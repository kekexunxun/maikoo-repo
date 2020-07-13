<?php
namespace app\index\controller;

use app\index\model\Admin;
use app\index\model\Catagory;
use app\index\model\Column;
use app\index\model\Column_goods;
use app\index\model\Goods as GoodsDb;
use app\index\model\Goods_comment;
use \think\Controller;
use \think\File;
use \think\Request;
use \think\Session;
use \think\Db;

class Goods extends Controller
{
    /**
     * @return 商品展示页面
     */
    public function goodslist()
    {
        $admin_id = Session::get('admin_id');
        // 查询mch_id的值
        $admin = new Admin;
        $mchId = $admin->where('admin_id', $admin_id)->value('mch_id');
        $this->assign('mch_id', $mchId);
        return $this->fetch();
    }

    /**
     * 获取商品信息
     * @param  Request $request
     * @return ary           商品信息
     */
    public function getGoodsData(Request $request)
    {
        // $goods     = new GoodsDb;
        // $goodsInfo = $goods->field('goods_id,goods_sn,goods_name,goods_img,market_price,shop_price,member_price,stock,sales_num,points,unit,status')->where('status', '<>', 4)->select();

        $field = 'goods_id,goods_sn,goods_name,goods_img,market_price,shop_price,member_price,stock,sales_num,points,unit,status';

        $admin_id = Session::get('admin_id');
        // 查询mch_id的值
        $mchId = Session::get('mch_id');
        if (!$mchId) {
            $mchId = null;
        }
        // dump($mchId);die;
        // 调用公共函数
        $goodsInfo = getGoods($field, $mchId, true, null);
        // dump($goodsInfo);die;
        // 查询审核与上下架的权限信息
        $menuId = Session::get('menuId');
        // 对字符串处理转为数组
        $ary = explode(',', $menuId);
        // dump($ary);die;
        // 数据不为0时
        if ($goodsInfo || count($goodsInfo) != 0) {
            // 构造返回数组
            foreach ($goodsInfo as $key => $value) {
                $goodsInfo[$key]['isCheck'] = 0;
                $goodsInfo[$key]['isUpdown'] = 0;
                foreach ($ary as $k => $v) {
                    if ($v == '31') {
                        $goodsInfo[$key]['isCheck'] = 1;
                    }
                    if ($v == '32') {
                        $goodsInfo[$key]['isUpdown'] = 1;
                    }
                }
            }
        }
        return objReturn(0, 'success', $goodsInfo);
    }

    /**
     * @return 商品编辑页面
     */
    public function goodsedit()
    {
        $request = Request::instance();
        $goodsId = intval($request->param('goods_id'));

        // 调用公共函数，通过商品Id获取商品详情
        $goodsInfo = getGoodsById($goodsId, false);
        if (!$goodsInfo) {
            $goodsInfo = null;
        }
  
        // 分类信息
        $catagory = new Catagory();
        // 获取顶级分类
        $parentCatId = Db::name('catagory')->where('cat_id', $goodsInfo['cat_id'])->value('parent_id');
        // 一级分类信息
        $catagoryOne = $catagory->field('cat_id, cname, parent_id')->where('parent_id', 0)->where('status', '<>', 2)->select();
        // 二级分类信息
        $catagoryTwo = $catagory->field('cat_id, cname, parent_id')->where('parent_id', $parentCatId)->where('status', '<>', 2)->select();
        $catagoryOne = collection($catagoryOne)->toArray();
        $catagoryTwo = collection($catagoryTwo)->toArray();
        $this->assign('catagoryOne', $catagoryOne);
        $this->assign('catagoryTwo', $catagoryTwo);
        $this->assign('parentCatId', $parentCatId);
        $this->assign('selfCatId', $goodsInfo['cat_id']);
        // 关键词
        $goodsInfo['keywords'] = explode(' ', $goodsInfo['keywords']);
        $this->assign('goodsInfo', $goodsInfo);
        return $this->fetch();
    }

    /**
     * 修改商品信息
     * @param  Request $request
     * @return ary           修改结果
     */
    public function editGoods(Request $request)
    {
        $where['goods_id'] = intval($request->param('goods_id'));
        $where['goods_name'] = htmlspecialchars($request->param('goods_name'));
        $where['unit'] = htmlspecialchars($request->param('unit'));
        $where['market_price'] = $request->param('market_price');
        $where['shop_price'] = $request->param('shop_price');
        $where['member_price'] = $request->param('member_price');
        $where['stock'] = intval($request->param('stock'));
        $where['sort'] = intval($request->param('sort'));
        $where['points'] = intval($request->param('points'));
        $where['cat_id'] = intval($request->param('cat_id'));
        $where['keywords'] = rtrim($request->param('goods_keyword'), ',');
        // dump($where['cat_id']);die;
        // 是否存在图片路径session
        if (Session::has('picsrc')) {
            $source = DEFAULT_STATIC_PATH . Session::get('picsrc');
            // 新的路径,取session值
            $word = DS . 'goods';
            $str = substr_replace(Session::get('picsrc'), $word, 3, 4);
            // 创建文件夹
            $str1 = substr($str, 0, 19);
            if (!is_dir(DEFAULT_STATIC_PATH . $str1)) {
                mkdir(DEFAULT_STATIC_PATH . $str1);
            }
            // 框架应用根目录/public/static/img/目录
            $destination = DEFAULT_STATIC_PATH . $str;
            // 拷贝文件到指定目录
            $res = copy($source, $destination);
            // 移动成功
            if ($res) {
                $str2 = DS . $str;
                $where['goods_img'] = $str2;
            } else {
                return objReturn(400, '上传失败,请重新上传图片！');
            }
            // 删除session信息
            Session::delete('picsrc');
        }
        if (!empty($request->param('goods_picsrc'))) {
            $source = $request->param('goods_picsrc');
            // 字符串分割为数组
            $temp = explode(',', $source);
            $srcAry = [];
            foreach ($temp as &$desc) {
                $te = explode(':', $desc);
                $srcAry[] = $te[0];
            }
            $src = '';
            // 遍历数组移动目录图片
            foreach ($srcAry as $key => $value) {
                // 新的路径
                $word = DS . 'goods';
                $strTemp = substr_replace($value, $word, 3, 4);
                // 创建文件夹
                $str3 = substr($strTemp, 0, 19);
                if (!is_dir(DEFAULT_STATIC_PATH . $str3)) {
                    mkdir(DEFAULT_STATIC_PATH . $str3);
                }
                // 框架应用根目录/public/static/img/目录
                $destination = DEFAULT_STATIC_PATH . $strTemp;
                $sou = DEFAULT_STATIC_PATH . $value;
                // 拷贝文件到指定目录
                $res = copy($sou, $destination);
                if ($res) {
                    $src .= DS . $strTemp . ',';
                } else {
                    return objReturn(400, '上传失败,请重新上传图片！');
                }
            }
            // 新文件路径
            foreach ($temp as $k => $v) {
                $v = DS . $v;
                $word = DS . 'goods';
                $picDesc = substr_replace($v, $word, 4, 4);
                $picDesc .= ',' . $picDesc;
            }
            $where['goods_desc'] = $picDesc;
        }
        // 获取管理员id
        $where['update_by'] = Session::get('admin_id');
        // 其他信息
        $where['update_at'] = time();
        $where['goods_sn'] = time();
        // 调用公共函数保存，参数ture为更新
        $update = saveData('goods', $where, true);
        if ($update) {
            return objReturn(0, '保存成功！');
        } else {
            return objReturn(400, '保存失败！');
        }
    }

    /**
     * 商品审核
     *
     * @return void
     */
    public function checkGoods()
    {
        $goodsId = request()->param('id');
        $update = Db::name('goods')->where('goods_id', $goodsId)->update(['status' => 1, 'update_by' => Session::get('admin_id'), 'update_at' => time()]);
        if ($update) {
            return objReturn(0, '审核成功！');
        } else {
            return objReturn(400, '审核失败！');
        }
    }

    /**
     * 上架商品功能
     * @param  Request $request 参数
     * @return ary              结果
     */
    public function upGoods(Request $request)
    {
        $where['goods_id'] = $request->param('id');
        $where['status'] = 2;
        $where['update_at'] = time();
        $where['update_by'] = Session::get('admin_id');
        // 调用公共函数，参数true为更新
        $update = saveData('goods', $where, true);
        if ($update) {
            return objReturn(0, '上架成功！');
        } else {
            return objReturn(400, '上架失败！');
        }
    }

    /**
     * 下架商品功能
     * @param  Request $request 参数
     * @return ary              结果
     */
    public function downGoods(Request $request)
    {
        $where['goods_id'] = $request->param('id');
        $where['status'] = 3;
        $where['update_at'] = time();
        $where['update_by'] = Session::get('admin_id');
        // 调用公共函数，参数true为更新
        $update = saveData('goods', $where, true);
        if ($update) {
            return objReturn(0, '下架成功！');
        } else {
            return objReturn(400, '下架失败！');
        }
    }

    /**
     * 删除单个商品
     * @param  Request $request
     * @return ary           删除的结果
     */
    public function delSingleGoods(Request $request)
    {
        $where['goods_id'] = intval($request->param('goods_id'));
        $where['delete_at'] = time();
        $where['status'] = 4;
        // 调用公共函数，参数true为更新
        $update = saveData('goods', $where, true);
        if ($update) {
            return objReturn(0, '删除成功！');
        } else {
            return objReturn(400, '删除失败！');
        }
    }

    /**
     * 批量删除商品
     * @param  Request $request
     * @return ary          删除结果
     */
    public function delMultiGoods(Request $request)
    {
        $GoodsIds = intval($request->param('ids'));
        $GoodsIds = substr($GoodsIds, 0, strlen($GoodsIds) - 1);
        $idArr = explode("*", $GoodsIds);
        $deleteArr = array();
        $goods = new GoodsDb;
        for ($i = 0; $i < sizeof($idArr); $i++) {
            $arr = array();
            $arr['goods_id'] = $idArr[$i];
            $arr['delete_at'] = time();
            $arr['status'] = 4;
            $deleteArr[] = $arr;
        }
        $delete = $goods->saveAll($deleteArr);
        if ($delete) {
            return objReturn(0, '删除成功！');
        } else {
            return objReturn(400, '删除失败！');
        }
    }

    /**
     * 商品添加页面
     * @return 商品添加页面与分类数据
     */
    public function goodsadd()
    {
        $catagory = new Catagory;
        $catagoryData = $catagory->field('parent_id,cname,cat_id')->where('parent_id', 0)->where('status', '<>', 2)->select();
        $this->assign('catagoryData', $catagoryData);
        return $this->fetch();
    }

    /**
     * 上传单张图片
     * @param  Request $request
     * @return 图片路径
     */
    public function addPic(Request $request)
    {
        $file = request()->file('file');
        // 是否存在session
        if (Session::has('picsrc')) {
            // 删除session信息
            Session::delete('picsrc');
        }
        // ->validate(['size'=>52428800,'ext'=>'jpg,png,gif,jpeg'])
        // 移动到框架应用根目录/static/imgTemp/目录下
        $info = $file->move(DEFAULT_STATIC_PATH . 'imgTemp');
        if ($info) {
            $str = $info->getSaveName();
            $picsrc = 'imgTemp' . DS . $str;
            // 存路径名到session
            Session::set('picsrc', $picsrc);
            return objReturn(0, '上传成功！', $picsrc);
        }
        return objReturn(400, '上传失败！');
    }

    /**
     * webuploader上传多图 不超过15张
     * @param Request $request
     * @return 图片路径
     */
    public function addGoodsPic(Request $request)
    {
        $file = request()->file('file');
        // 移动到框架应用根目录/public/static/imageTemp/ 目录下
        $info = $file->move(DEFAULT_STATIC_PATH . 'imgTemp');
        if ($info) {
            $str2 = $info->getSaveName();
            $src = 'imgTemp' . DS . $str2;
            $getInfo = $info->getInfo();
           //获取图片的原名称
            $name = $getInfo['name'];
            $name = substr($name, 0, -4);
            // 拼接图片顺序
            $picSrc = $src . ':' . $name;
            return json($picSrc);
            // 判断文件名是否数字
            // if(is_numeric($name)){ 
                // return json($picSrc);
            // }else{
               //  return 401;             
            // }
        }
    }

    /**
     * 添加商品信息功能
     * @param Request $request
     * @return ary  添加结果
     */
    public function addGoods(Request $request)
    {
        $where['goods_name'] = htmlspecialchars($request->param('goods_name'));
        $where['unit'] = htmlspecialchars($request->param('unit'));
        $where['market_price'] = $request->param('market_price');
        $where['shop_price'] = $request->param('shop_price');
        $where['member_price'] = $request->param('member_price');
        $where['stock'] = intval($request->param('stock'));
        $where['sort'] = intval($request->param('sort'));
        $where['points'] = intval($request->param('points'));
        $where['cat_id'] = intval($request->param('cat_id'));
        $where['keywords'] = rtrim($request->param('goods_keyword'), ',');
        // 是否存在图片路径session
        if (Session::has('picsrc')) {
            $source = DEFAULT_STATIC_PATH . Session::get('picsrc');
            // 新的路径,取session值
            $word = DS . 'goods';
            $str = substr_replace(Session::get('picsrc'), $word, 3, 4);
            // 创建文件夹
            $str1 = substr($str, 0, 19);
            if (!is_dir(DEFAULT_STATIC_PATH . $str1)) {
                mkdir(DEFAULT_STATIC_PATH . $str1);
            }
            // 框架应用根目录/public/static/img/目录
            $destination = DEFAULT_STATIC_PATH . $str;
            // 拷贝文件到指定目录
            $res = copy($source, $destination);
            // 移动成功
            if ($res) {
                $str2 = DS . $str;
                $where['goods_img'] = $str2;
            } else {
                return objReturn(400, '上传失败,请重新上传图片！');
            }
            // 不找过15张图
            if (!empty($request->param('goods_picsrc'))) {
                $source = $request->param('goods_picsrc');
                // 字符串分割为数组
                $temp = explode(',', $source);
                $srcAry = [];
                foreach ($temp as &$desc) {
                    $te = explode(':', $desc);
                    $srcAry[] = $te[0];
                }
                $src = '';
                // 遍历数组移动目录图片
                foreach ($srcAry as $key => $value) {
                    // 新的路径
                    $word = DS . 'goods';
                    $strTemp = substr_replace($value, $word, 3, 4);
                    // 创建文件夹
                    $str3 = substr($strTemp, 0, 19);
                    if (!is_dir(DEFAULT_STATIC_PATH . $str3)) {
                        mkdir(DEFAULT_STATIC_PATH . $str3);
                    }
                    // 框架应用根目录/public/static/img/目录
                    $destination = DEFAULT_STATIC_PATH . $strTemp;
                    $sou = DEFAULT_STATIC_PATH . $value;
                    // 拷贝文件到指定目录
                    $res = copy($sou, $destination);
                    if ($res) {
                        $src .= DS . $strTemp . ',';
                    } else {
                        return objReturn(400, '上传失败,请重新上传图片！');
                    }
                }
                // 新文件路径
                foreach ($temp as $k => $v) {
                    $v = DS . $v;
                    $word = DS . 'goods';
                    $picDesc = substr_replace($v, $word, 4, 4);
                    $picDesc .= ',' . $picDesc;
                }
                $where['goods_desc'] = $picDesc;
                $where['is_new'] = 1;
                $where['status'] = 0;
                $where['is_hot'] = 0;
                $where['created_at'] = time();
                $where['goods_sn'] = time();
                // 调用公共函数保存，参数false为新增
                $insert = saveData('goods', $where, false);
                if ($insert) {
                    return objReturn(0, '保存成功！');
                        // 删除session信息
                    Session::delete('picsrc');
                } else {
                    return objReturn(400, '保存失败！');
                }
            } else {
                return objReturn(400, '上传失败,请重新上传图片！');
            }
        } else {
            return objReturn(400, '上传失败,请重新上传图片！');
        }
    }

    /**
     * 根据一级分类id找二级分类id
     * @param  Request $request
     * @return ary     返回查找的结果
     */
    public function catagoryData(Request $request)
    {
        $catId = intval($request->param('catagory_id'));
        $catagory = new Catagory;
        $res = $catagory->field('cat_id,cname')->where('parent_id', $catId)->where('status', '<>', 2)->select();
        if ($res) {
            return objReturn(0, '获取成功', $res);
        } else {
            return objReturn(400, '无二级分类', $res);
        }
    }

    /**
     * 根据分类id选对应商品
     * @param  Request $request
     * @return ary           返回值
     */
    public function selectGoods(Request $request)
    {
        $cat_id = intval($request->param('cat_id'));
        $goods = new GoodsDb;
        $goodsList = $goods->field('goods_id,goods_sn,goods_name,goods_img,market_price,shop_price,member_price,stock,sales_num,status,points,unit')->where('cat_id', $cat_id)->where('status', '<>', 4)->select();
        if (!$goodsList || count($goodsList) == 0) {
            return objReturn(0, '获取成功', 401);
        }
        foreach ($goodsList as &$good) {
            if (isset($good['goods_img'])) {
                $good['goods_img'] = "https//xnps.up.maikoo.cn/static" . $good['goods_img'];
            }

        }
        $goodsList = collection($goodsList)->toArray();

        $admin_id = Session::get('admin_id');
        // 查询mch_id的值
        $admin = new Admin;
        $mchId = $admin->where('admin_id', $admin_id)->value('mch_id');
        if (!$mchId) {
            $mchId = null;
        }
        // 查询审核与上下架的权限信息
        $menuId = Session::get('menuId');
        // 对字符串处理转为数组
        $ary = explode(',', $menuId);
        // dump($ary);die;
        // 数据不为0时
        if ($goodsList || count($goodsList) != 0) {
            // 构造返回数组
            foreach ($goodsList as $key => $value) {
                $goodsList[$key]['isCheck'] = 0;
                $goodsList[$key]['isUpdown'] = 0;
                foreach ($ary as $k => $v) {
                    if ($v == '31') {
                        $goodsList[$key]['isCheck'] = 1;
                    }
                    if ($v == '32') {
                        $goodsList[$key]['isUpdown'] = 1;
                    }
                }
            }
        }
        // dump($goodsInfo);die;
        if (empty($goodsList)) {
            return objReturn(0, '获取成功', 401);
        } else {
            return objReturn(0, '获取成功', $goodsList);
        }
    }

    /**
     * 分类界面
     * @return ary 分类数据
     */
    public function catagory()
    {
        $catagory = new Catagory;
        $catagoryData = $catagory->field('cat_id,cname')->where('parent_id', 0)->where('status', 1)->select();
        $this->assign('catagoryData', $catagoryData);
        return $this->fetch();
    }

    /**
     * catagoryZtree 获取分类数据
     * @DateTime 2018-08-02
     * @version  V1.0.0
     * @return   ary     返回ztree数组
     */
    public function catagoryZtree()
    {
        $catagory = new Catagory;
        $res = $catagory->field('cat_id,parent_id,cname')->where('status', '<>', 2)->order('sort desc')->select();
        if ($res) {
            // 构造返回数组
            foreach ($res as $key => $value) {
                $temp['id'] = $value['cat_id'];
                $temp['pId'] = $value['parent_id'];
                $temp['name'] = $value['cname'];
                $temp['open'] = 'true';
                $catagoryArr[] = $temp;
            }
            return objReturn(0, '数据获取成功！', $catagoryArr);
        }
        return objReturn(400, '数据获取失败！');
    }

    /**
     *  选择节点信息
     * @param    Request  $request 参数
     * @return   ary               返回值
     */
    public function selectCatagory(Request $request)
    {
        $cat_id = intval($request->param('cat_id'));
        $catagory = new Catagory;
        $data = $catagory->field('parent_id,cname,status,sort,img')->where('cat_id', $cat_id)->where('status', '<>', 2)->find();
        if ($data) {
            return objReturn(0, 'success', $data);
        } else {
            return objReturn(400, 'failed', $data);
        }
    }

    /**
     * addCatagory 添加商品分类
     * @param    Request  $request 参数
     * @return   ary               返回值
     */
    public function addCatagory(Request $request)
    {
        $parent_id = intval($request->param('catagory_parent'));
        $cat_id = intval($request->param('catagory_cat_id'));
        $add['cname'] = htmlspecialchars($request->param('catagory_name'));
        $add['sort'] = intval($request->param('catagory_orderby'));
        $add['status'] = intval($request->param('catagory_active'));
        $add['created_at'] = time();
        // 添加父级分类
        if ($parent_id == 0) {
            $add['parent_id'] = 0;
            // 调用公共函数保存，参数false为新增
            $insert = saveData('cat', $add, false);
            if ($insert) {
                return objReturn(0, '新增成功');
            } else {
                return objReturn(400, '新增失败');
            }
        }
        // 添加子级
        if ($parent_id == 1) {
            $add['parent_id'] = $cat_id;
            // 是否存在session
            if (Session::has('picsrc')) {
                // 取session值
                $source = DEFAULT_STATIC_PATH . Session::get('picsrc');
                // 新的路径,取session值
                $word = DS . 'cat';
                $str = substr_replace(Session::get('picsrc'), $word, 3, 4);
                // 创建文件夹
                $str1 = substr($str, 0, 17);
                if (!is_dir(DEFAULT_STATIC_PATH . $str1)) {
                    mkdir(DEFAULT_STATIC_PATH . $str1);
                }
                // 框架应用根目录/public/static/img/目录
                $destination = DEFAULT_STATIC_PATH . $str;
                // 拷贝文件到指定目录
                $res = copy($source, $destination);
                // 移动成功
                if ($res) {
                    $str2 = DS . $str;
                    $add['img'] = $str2;
                    // 删除session信息
                    // Session::delete('picsrc');
                    // 调用公共函数保存，参数false为新增
                    $insert = saveData('cat', $add, false);
                    if ($insert) {
                        return objReturn(0, '新增成功');
                    } else {
                        return objReturn(400, '新增失败');
                    }
                } else {
                    return objReturn(400, '上传失败,请重新上传图片！');
                }
            }
            return objReturn(400, '新增失败，请上传图片！');
        }
    }

    /**
     * 删除一级分类
     *
     * @return void
     */
    public function delParentCat()
    {
        $catId = request()->param('cat_id');
        $update = Db::name('catagory')->whereOr('cat_id', $catId)->whereOr('parent_id', $catId)->update(['status' => 2, 'update_at' => time(), 'update_by' => Session::get('admin_id')]);
        if ($update) {
            return objReturn(0, '删除成功');
        }
        return objReturn(400, '删除失败', $update);
    }

    /**
     * 修改节点信息
     * @param    Request    $request 参数
     * @return   ary              返回值
     */
    public function editCatagory(Request $request)
    {
        $parentId = intval($request->param('catagory_parent_id'));
        // 修改子分类
        if ($parentId != 10001 && $parentId != 0) {
            // 是否存在session
            if (Session::has('picsrc')) {
                // 取session值
                $source = DEFAULT_STATIC_PATH . Session::get('picsrc');
                // 新的路径,取session值
                $word = DS . 'cat';
                $str = substr_replace(Session::get('picsrc'), $word, 3, 4);
                // 创建文件夹
                $str1 = substr($str, 0, 17);
                if (!is_dir(DEFAULT_STATIC_PATH . $str1)) {
                    mkdir(DEFAULT_STATIC_PATH . $str1);
                }
                // 框架应用根目录/public/static/img/目录
                $destination = DEFAULT_STATIC_PATH . $str;
                // 拷贝文件到指定目录
                $res = copy($source, $destination);
                // 移动成功
                if ($res) {
                    $str2 = DS . $str;
                    $update['img'] = $str2;
                    // 删除session信息
                    Session::delete('picsrc');
                } else {
                    return objReturn(400, '上传失败,请重新上传图片！');
                }
            }
            $update['cat_id'] = intval($request->param('catagory_cat_id'));
            $update['cname'] = htmlspecialchars($request->param('catagory_cat_name'));
            $update['sort'] = intval($request->param('catagory_cat_orderby'));
            $update['status'] = intval($request->param('catagory_cat_active'));
            $update['update_at'] = time();
            $update['update_by'] = Session::get('admin_id');
            // 调用公共函数，参数true为更新
            $res = saveData('cat', $update, true);
            if ($res) {
                return objReturn(0, '修改成功！');
            } else {
                return objReturn(400, '修改失败！');
            }
        }
        // 修改父级分类
        if ($parentId == 10001) {
            $update['cat_id'] = intval($request->param('catagory_cat_id'));
            $update['cname'] = htmlspecialchars($request->param('catagory_parent_name'));
            $update['sort'] = intval($request->param('catagory_parent_orderby'));
            $update['status'] = intval($request->param('catagory_parent_active'));
            $update['update_at'] = time();
            $update['update_by'] = Session::get('admin_id');
            // 调用公共函数，参数true为更新
            $res = saveData('cat', $update, true);
            if ($res) {
                return objReturn(0, '修改成功！');
            } else {
                return objReturn(400, '修改失败！');
            }
        }
    }

    /**
     * 删除父节点与对应的子节点
     * @param    Request    $request
     * @return   ary              返回值
     */
    public function delFather(Request $request)
    {
        // 删除子节点
        $parent_id = intval($request->param('catagory_cat_id'));
        $catagory = new Catagory;
        $dell['status'] = 2;
        $dell['delete_at'] = time();
        $res = $catagory->where('parent_id', $parent_id)->update($dell);
        // 删除父节点
        $del['cat_id'] = intval($request->param('catagory_cat_id'));
        $del['status'] = 2;
        $del['delete_at'] = time();
        // 调用公共函数，参数true为更新
        $delete = saveData('cat', $del, true);
        if ($delete) {
            return objReturn(0, '删除成功！');
        } else {
            return objReturn(400, '删除失败！');
        }
    }

    /**
     * 只删除子节点
     * @param    Request    $request
     * @return   ary     返回值
     */
    public function delCat(Request $request)
    {
        $del['cat_id'] = intval($request->param('catagory_cat_id'));
        $del['status'] = 2;
        $del['delete_at'] = time();
        // 调用公共函数，参数true为更新
        $delete = saveData('cat', $del, true);
        if ($delete) {
            return objReturn(0, '删除成功！');
        } else {
            return objReturn(400, '删除失败！');
        }
    }

    /**
     * 上传excel文件
     * @param  Request $request 参数
     * @return ary           返回信息
     */
    public function uploadExcel(Request $request)
    {
        $file = request()->file('file');
        // 是否存在session
        if (Session::has('excelPath')) {
            // 删除session信息
            Session::delete('excelPath');
        }
        // 移动到框架应用根目录/static/excel/目录下
        $path = 'excel' . DS . 'import' . DS;
        $info = $file->move(DEFAULT_STATIC_PATH . $path);
        if ($info) {
            $str = $info->getSaveName();
            $src = $path . $str;
            // 存路径名到session
            Session::set('excelPath', $src);
            return objReturn(0, '上传成功！', $src);
        }
        return objReturn(400, '上传失败！');
    }

    /**
     * 导入excel文件 调用Excel.php的getExcelData函数
     * @param  Request $request 参数
     * @return ary           导入结果
     */
    public function importExcel(Request $request)
    {
        // 判断是否上传了excel文件
        if (Session::has('excelPath')) {
            // 获取excel文件路径
            $path = Session::get('excelPath');
            $filename = DEFAULT_STATIC_PATH . $path;
            // 文件格式
            $exts = 'xlsx';
            $excel = new Excel;
            $res = $excel->getExcelData($filename, $exts);
            return $res;
        } else {
            return objReturn(400, '导入失败！');
        }
    }

    /**
     * 下载excel模板
     * @param  Request $request 参数
     * @return ary           下载的结果
     */
    public function downTemplate(Request $request)
    {
        // 调用Excel控制器的template方法
        $excel = new Excel;
        $res = $excel->template();
        if ($res) {
            return objReturn(0, '点击下载模板', $res);
            // header('Content-Type: application/vnd.ms-excel');
            // header('Cache-Control: max-age=0');
            // Header("Accept-Ranges:bytes");
            // return $res;
        } else {
            return objReturn(400, '下载模板失败！');
        }
    }
// ******************
    /**
     * 专栏列表
     */
    public function columnlist()
    {
        $columnData = getColumn();
        $this->assign('data', $columnData);
        return $this->fetch();
    }

    /**
     * 选择ztree的goods
     *
     * @return void
     */
    public function columnGoodsSelect()
    {
        $goodsIds = request()->param('goodsid');
        // 去除逗号
        $goodsIds = rtrim($goodsIds, ',');

        // goodsid 是大于 1000000的
        // 重构goodsid
        $goodsIdsArr = explode(',', $goodsIds);
        $goodsArr = [];
        foreach ($goodsIdsArr as $k => $v) {
            if ($v > 1000000) {
                $goodsArr[] = $v - 1000000;
            }
        }
        if (count($goodsArr) == 0) {
            return objReturn(400, '请至少选择一件商品');
        }
        // 是否存在session
        if (Session::has('goodsIds')) {
            // 删除session信息
            Session::delete('goodsIds');
        }
        // 存menu_id到session
        Session::set('goodsIds', $goodsArr);
        if (Session::has('goodsIds')) {
            return objReturn(0, '商品信息保存成功！');
        } else {
            return objReturn(400, '商品信息保存失败！');
        }
    }

    /**
     * 添加专栏
     * @param   column_sort  专栏顺序
     * @param   column_name  专栏名称
     * @param   column_color 专栏主题颜色
     * @param   column_img   专栏封面图片
     * @param   column_goods 专栏商品
     * @return  result 添加结果
     */
    public function columnadd(Request $request)
    {
        if ($request->post()) {
            $post = $request->post();
            $data['column_color'] = $post['column_color'];
            $data['sort'] = $post['sort'];
            $data['column_name'] = $post['column_name'];
            $data['status'] = $post['status'];
            $file = $request->file('column_img');
            //判断专栏商品是否为空
            if (!Session::has('goodsIds')) {
                return objReturn(400, '请选择商品!');
            }
            $goods = Session::get('goodsIds');
            // dump($goods);die;
            //专栏封面图片存储目录
            $dir = '.' . DS . 'static' . DS . 'img' . DS . 'column' . DS;
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $info = $file->move($dir);
            if ($info) {
                $saveName = $info->getSaveName();
                $data['column_img'] = '/img/column/' . $saveName;
            } else {
                return objReturn(400, $file->getError());
            }
            $data['created_at'] = time();
            $data['created_by'] = Session::get('admin_id');
            $columnDb = new Column;
            $column_id = $columnDb->insertGetId($data);
            if (!$column_id) {
                return objReturn(400, '请求错误,请重试!');
            }
            $column_goods_data = [];
            foreach ($goods as $k => $v) {
                $column_goods_data[] = ['column_id' => $column_id, 'goods_id' => $v, 'created_at' => $data['created_at'], 'created_by' => Session::get('admin_id'), 'status' => 1];
            }
            $column_goodsDb = new Column_goods;
            $result = $column_goodsDb->insertAll($column_goods_data);
            if ($result) {
                Session::delete('goodsIds');
                return objReturn(0, '添加专栏成功!');
            } else {
                return objReturn(400, '添加专栏失败!');
            }
        } else {
            // $goodsDb   = new Goods;
            // $goodsData = $goodsDb->field()->where(['status' => 2])->select();
            $field = "goods_id,goods_name,cat_id";
            // 调用公共函数
            $goodsData = getGoods($field, null, true);
            // dump($goodsData);die;
            // 构造ztree数据
            $data = [];
            foreach ($goodsData as $key => $value) {
                $ary = array(
                    'id' => $value['goods_id'],
                    'pId' => $value['cat_id'],
                    'name' => $value['goods_name'],
                    'open' => "true",
                    'checked' => "false",
                );
                $data[] = $ary;
            }
            // dump($data);die;
            // $this->assign('data', $data);
            return $this->fetch();
        }
    }

    /**
     * 分类对应的商品信息
     * @param  Request $request 参数
     * @return ary              返回ztree数组
     */
    public function goodsData(Request $request)
    {
        $columnID = request()->param('columnid');
        if ($columnID) {
            $columnGoodsIds = Db::name('column_goods')->where('column_id', $columnID)->field('goods_id')->select();
            $columnGoodsIds = $columnGoodsIds ? collection($columnGoodsIds)->toArray() : [];
            // 构造columnGoodsArr
            $columnGoodsArr = [];
            if (count($columnGoodsIds) > 0) {
                foreach ($columnGoodsIds as $k => $v) {
                    $columnGoodsArr[] = $v['goods_id'];
                }
            }
        }

        $field = "goods_id,goods_name,cat_id";
        // 调用公共函数 获取商品信息
        $goodsData = getGoods($field, null, true);
        // dump($goodsData);die;
        // 构造ztree数据
        $catagory = new Catagory;
        $catList = $catagory->field('cat_id,parent_id,cname')->where('status', 1)->order('sort desc')->select();
        if ($catList) {
            $catList = collection($catList)->toArray();
            $temp = [];
            // 构造返回数组
            foreach ($catList as $key => $value) {
                if ($value['parent_id'] == 0) {
                    $catFirst['id'] = $value['cat_id'];
                    $catFirst['pId'] = 0;
                    $catFirst['name'] = $value['cname'];
                    $catFirst['open'] = true;
                    $temp[] = $catFirst;
                    foreach ($catList as $ke => $val) {
                        // 相等时对应的pid 等于上一级的pid
                        if ($val['parent_id'] != 0 && $val['parent_id'] == $value['cat_id']) {
                            $catSecond['id'] = $val['cat_id'];
                            $catSecond['pId'] = $value['cat_id'];
                            $catSecond['name'] = $val['cname'];
                            $catSecond['open'] = 'true';
                            $temp[] = $catSecond;
                            // 商品的信息
                            foreach ($goodsData as $k => $v) {
                                if ($val['cat_id'] == $v['cat_id']) {
                                    $goods['id'] = $v['goods_id'] + 1000000;
                                    $goods['name'] = $v['goods_name'];
                                    $goods['pId'] = $v['cat_id'];
                                    // 如果当前有columnid 并且columnid 中包含有该商品 那么其checked 为true
                                    if ($columnID && count($columnGoodsArr) > 0 && in_array($v['goods_id'], $columnGoodsArr)) {
                                        $goods['checked'] = true;
                                    } else {
                                        $goods['checked'] = false;
                                    }
                                    $temp[] = $goods;
                                }
                            }
                        }
                    }
                }
            }
        }
        return objReturn(0, '数据获取成功！', $temp);
    }

    /**
     * 选择分类对应的商品信息
     * @param  Request $request 参数
     * @return ary           返回信息
     */
    public function selectCatGoods(Request $request)
    {
        $goodsIds = $request->param('goodsIds');
        // 去除最后的逗号
        $goodsIds = rtrim($goodsIds, ',');
        // 是否存在session
        if (Session::has('goodsIds')) {
            // 删除session信息
            Session::delete('goodsIds');
        }
        // 存goodsIds到session
        Session::set('goodsIds', $goodsIds);
        if (Session::has('goodsIds')) {
            return objReturn(0, '商品信息保存成功！');
        } else {
            return objReturn(400, '商品信息保存失败！');
        }
    }

    /**
     * 原先分类对应的商品信息
     * @param  Request $request 参数
     * @return ary              返回ztree数组
     */
    public function preCatGoods(Request $request)
    {
        $columnId = intval($request->param('columnId'));
        $field = "goods_id,goods_name,cat_id";
        // 调用公共函数 获取商品信息
        $goodsData = getGoods($field, null, true);
        // dump($goodsData);die;
        // 构造ztree数据
        $catagory = new Catagory;
        $catList = $catagory->field('cat_id,parent_id,cname')->where('status', 1)->order('sort desc')->select();
        if (!$catList || count($catList) == 0) {
            return objReturn(400, 'No Cat');
        }

        $catList = collection($catList)->toArray();
        // 专栏的商品信息
        $columnGoods = new Column_goods;
        $goodsList = $columnGoods->field('goods_id')->where('status', 1)->where('column_id', $columnId)->select();
        if (!$goodsList || count($goodsList) == 0) {
            return objReturn(400, 'No CatGoods');
        }

        $goodsList = collection($goodsList)->toArray();
        $goodsIds = [];
        foreach ($goodsList as $k => $v) {
            $goodsIds[$k] = $v['goods_id'];
        }
        // dump($goodsIds);
        // die;
        if ($catList) {
            $temp = [];
            // 构造返回数组
            foreach ($catList as $key => $value) {
                if ($value['parent_id'] == 0) {
                    $catFirst['id'] = $value['cat_id'];
                    $catFirst['pId'] = 0;
                    $catFirst['name'] = $value['cname'];
                    $catFirst['open'] = true;
                    $temp[] = $catFirst;
                    foreach ($catList as $ke => $val) {
                        // 相等时对应的pid 等于上一级的pid
                        if ($val['parent_id'] != 0 && $val['parent_id'] == $value['cat_id']) {
                            $catSecond['id'] = $val['cat_id'];
                            $catSecond['pId'] = $value['cat_id'];
                            $catSecond['name'] = $val['cname'];
                            $catSecond['open'] = 'true';
                            $temp[] = $catSecond;
                            // 商品的信息
                            foreach ($goodsData as $k => $v) {
                                if ($val['cat_id'] == $v['cat_id']) {
                                    $goods['id'] = $v['goods_id'] + 1000000;
                                    $goods['name'] = $v['goods_name'];
                                    $goods['pId'] = $v['cat_id'];
                                    // dump($v['goods_id']);
                                    if ($goodsIds && in_array($v['goods_id'], $goodsIds)) {
                                        $goods['checked'] = true;
                                    } else {
                                        $goods['checked'] = false;
                                    }
                                    $temp[] = $goods;
                                }
                            }
                        }
                    }
                }
            }
        }
        return objReturn(0, '数据获取成功！', $temp);
    }

    /**
     * 编辑专栏
     * @param   column_sort  专栏顺序
     * @param   column_name  专栏名称
     * @param   column_color 专栏主题颜色
     * @param   column_img   专栏封面图片
     * @param   column_goods 专栏商品
     * @return  result 编辑结果
     */
    public function columnedit(Request $request)
    {
        $column_id = $request->param('column_id');
        if ($request->isPost()) {
            $data = $request->except(['column_img', 'column_id', 'old_column_img', 'goods'], 'post');
            $post = $request->post();
            // dump($post);die;
            //图片上传
            $file = $request->file('column_img');
            if (!$file) {
                $oldImg = $post['old_column_img'];
                if (empty($oldImg)) {
                    return objReturn(400, '请选择专栏封面图片');
                }
                $dir = pathinfo(pathinfo($oldImg, PATHINFO_DIRNAME), PATHINFO_BASENAME);
                $data['column_img'] = '/img/column/' . $dir . '/' . pathinfo($oldImg, PATHINFO_BASENAME);
            } else {
                //专栏封面图片存储目录
                $dir = '.' . DS . 'static' . DS . 'img' . DS . 'column' . DS;
                $info = $file->move($dir);
                if ($info) {
                    $saveName = $info->getSaveName();
                    $data['column_img'] = '/img/column/' . $saveName;
                } else {
                    return objReturn(400, $file->getError());
                }
            }
            $data['update_at'] = time();
            $data['update_by'] = Session::get('admin_id');
            //更新column表
            $columnDb = new Column;
            $result = $columnDb->where(['column_id' => $column_id])->update($data);
            if (!$result) {
                return objReturn(400, '请求错误,请重试!');
            }
            // 是否更改商品信息
            if (Session::has('goodsIds')) {
                //更新column_goods表
                $columnGoodsDb = new Column_goods;
                $columnGoodsDb->where(['column_id' => $column_id])->delete();
                $goods = Session::get('goodsIds');
                // dump($goods);die;
                $column_goods_data = [];
                foreach ($goods as $k => $v) {
                    $column_goods_data[] = ['column_id' => $column_id, 'goods_id' => $v, 'update_at' => $data['update_at'], 'update_by' => Session::get('admin_id')];
                }
                $result = $columnGoodsDb->insertAll($column_goods_data);
                if ($result) {
                    return objReturn(0, '修改专栏成功!');
                } else {
                    return objReturn(400, '修改专栏失败!');
                }
            }
        } else {
            $columnData = getColumnById($column_id);
            $goods_ids = [];
            foreach ($columnData['goods'] as $k => $v) {
                $goods_ids[] = $v['goods_id'];
            }
            $goodsDb = new GoodsDb;
            $goodsData = $goodsDb->where(['status' => 2])->select();
            $this->assign('goodsData', $goodsData);
            $this->assign('goods_ids', $goods_ids);
            $this->assign('data', $columnData);
            $this->assign('column_id', $column_id);
            return $this->fetch();
        }
    }

    /**
     * 删除专栏
     * @param   column_id 专栏ID
     * @return  result    删除结果
     */
    public function columnDelete(Request $request)
    {
        $column_id = $request->param('column_id');
        $columnDb = new Column;
        $columnGoodsDb = new Column_goods;
        //删除column_goods表专栏商品
        $columnGoodsDb->where(['column_id' => $column_id])->delete();
        //删除column表专栏
        $result = $columnDb->where(['column_id' => $column_id])->update(['status' => 3]);
        if ($result) {
            return objReturn(0, '专栏删除成功!');
        } else {
            return objReturn(400, '专栏删除失败!');
        }
    }

    /**
     * 专栏商品列表
     * @param   column_id 专栏ID
     */
    public function columngoodslist(Request $request)
    {
        $column_id = $request->param('column_id');
        $columnData = getColumnById($column_id);
        $this->assign('column_id', $column_id);
        $this->assign('data', $columnData['goods']);
        return $this->fetch();
    }

    /**
     * 设置专栏商品顺序
     * @param   idx    专栏商品ID
     * @param   sort   顺序
     * @return  result 修改顺序结果
     */
    public function columnGoodsSetSort(Request $request)
    {
        $idx = $request->param('idx');
        $sort = $request->param('sort');
        $columnGoodsDb = new Column_goods;
        $result = $columnGoodsDb->where(['idx' => $idx])->update(['sort' => $sort]);
        if ($result) {
            return objReturn(0, '已修改顺序');
        } else {
            return objReturn(400, '修改顺序失败');
        }
    }

    /**
     * 改变专栏商品状态
     * @param   idx    专栏商品ID
     * @param   status 专栏商品当前状态
     * @return  result 操作结果
     */
    public function columnGoodsChange(Request $request)
    {
        $columnGoodsDb = new Column_goods;
        $idx = $request->param('idx');
        $status = $request->param('status');
        $msg = '';
        $error = '';
        if ($status == 0) {
            $data['status'] = 1;
            $msg = '启用展示成功!';
            $error = '启用展示失败!';
        } else {
            $data['status'] = 0;
            $msg = '关闭展示成功!';
            $error = '关闭展示失败!';
        }
        $result = $columnGoodsDb->where(['idx' => $idx])->update($data);
        if ($result) {
            return objReturn(0, $msg);
        } else {
            return objReturn(400, $error);
        }
    }

    /**
     * 删除专栏商品
     * @param   idx    专栏商品ID
     * @return  result 操作结果
     */
    public function columnGoodsDelete(Request $request)
    {
        $idx = $request->param('idx');
        $columnGoodsDb = new column_goods;
        $data['status'] = 2;
        $result = $columnGoodsDb->where(['idx' => $idx])->update($data);
        if ($result) {
            return objReturn(0, '专栏商品删除成功!');
        } else {
            return objReturn(400, '专栏商品删除失败!');
        }
    }

    /**
     * 改变专栏首页置顶状态
     * @param   column_id    专栏ID
     * @param   is_top       置顶状态
     * @return  result       操作结果
     */
    public function columnTop(Request $request)
    {
        $columnDb = new Column;
        $column_id = $request->param('column_id');
        $is_top = $request->param('is_top');
        $msg = '';
        $error = '';
        if ($is_top == 0) {
            $data['is_top'] = 1;
            $msg = '启用首页置顶成功!';
            $error = '启用首页置顶失败!';
        } else {
            $data['is_top'] = 0;
            $msg = '关闭首页置顶成功!';
            $error = '关闭首页置顶失败!';
        }
        $result = $columnDb->where(['is_top' => 1])->update(['is_top' => 0]);
        if ($data['is_top'] == 1) {
            $result = $columnDb->where(['column_id' => $column_id])->update($data);
        }
        if ($result) {
            return objReturn(0, $msg);
        } else {
            return objReturn(400, $error);
        }
    }

    /**
     * 改变专栏展示状态
     * @param   column_id    专栏ID
     * @param   status       展示状态
     * @return  result       操作结果
     */
    public function columnStatusChange(Request $request)
    {
        $column_id = $request->param('column_id');
        $data['status'] = $request->param('status');
        $columnDb = new Column;
        $result = $columnDb->where(['column_id' => $column_id])->update($data);
        if ($result) {
            return objReturn(0, '修改专栏展示状态成功!');
        } else {
            return objReturn(400, '修改专栏展示状态失败!');
        }
    }

    public function comment()
    {
        $goods_comment = new Goods_comment;
        // 连表查询
        $commentData = $goods_comment->alias('c')->join('goods g', 'c.goods_id = g.goods_id', 'LEFT')->join('user_profile u', 'c.created_by = u.uid', 'LEFT')->field('c.comment_id,c.order_sn,c.comment,c.satisfaction,c.created_at,g.goods_name,g.goods_img,u.nickName,u.avatar_url')->select();
        if ($commentData && count($commentData) != 0) {
            $commentData = collection($commentData)->toArray();
        }
        // dump($commentData);die;
        $this->assign('commentData', $commentData);
        return $this->fetch();
    }
}
