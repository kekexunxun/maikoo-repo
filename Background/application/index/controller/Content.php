<?php
namespace app\index\controller;

use \think\Controller;
use \think\File;
use \think\Request;
use \think\Session;
use app\index\model\Article;
use app\index\model\Banner;
use app\index\model\Catagory;
use app\index\model\Clause as ClauseDb;
use app\index\model\Feedback;
use app\index\model\Goods;
use app\index\model\Question as QuestionDb;
use app\index\model\User_profile;
use app\index\model\Search_kw;
use app\index\model\Admin;

class Content extends Controller
{
    /**
     * 轮播图展示
     * @return ary 返回值
     */
    public function bannerlist()
    {
        // 调用公共函数
        $bannerData = getBanner(0, true);
        $this->assign('bannerData', $bannerData);
        return $this->fetch();
    }

    /**
     * 添加banner图
     * @return html 页面
     */
    public function banneradd()
    {
        // 商品id
        $goods     = new Goods;
        $goodsData = $goods->field('goods_id,goods_name')->where('status', '<>', 4)->select();
        $this->assign('goodsData', $goodsData);
        // 文章id
        $article     = new Article;
        $articleData = $article->field('article_id,article_title')->where('status', '<>', 2)->select();
        $this->assign('articleData', $articleData);
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
            $str    = $info->getSaveName();
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
    public function addMultiPic(Request $request)
    {
        $file = request()->file('file');
        // 移动到框架应用根目录/public/static/imageTemp/ 目录下
        $info = $file->move(DEFAULT_STATIC_PATH . 'imgTemp');
        if ($info) {
            $str2 = $info->getSaveName();
            $src  = 'imgTemp' . DS . $str2;
            $getInfo = $info->getInfo();
           //获取图片的原名称
            $name = $getInfo['name'];
            $name = substr($name,0,-4);
            // 拼接图片顺序
            $picSrc = $src.':'.$name;
            return json($picSrc);
        }
    }

    /**
     * 添加banner图片
     * @param Request $request
     * @return ary 返回结果
     */
    public function addBanner(Request $request)
    {
        $navType         = intval($request->param('nav_type'));
        $where['sort']   = intval($request->param('banner_sort'));
        $where['status'] = intval($request->param('banner_active'));
        // 是否存在图片路径session
        if (Session::has('picsrc')) {
            $source = DEFAULT_STATIC_PATH . Session::get('picsrc');
            // 新的路径,取session值
            $word = DS . 'banner';
            $str  = substr_replace(Session::get('picsrc'), $word, 3, 4);
            // 创建文件夹
            $str1 = substr($str, 0, 20);
            if (!is_dir(DEFAULT_STATIC_PATH . $str1)) {
                mkdir(DEFAULT_STATIC_PATH . $str1);
            }
            // 框架应用根目录/public/static/img/目录
            $destination = DEFAULT_STATIC_PATH . $str;
            // 拷贝文件到指定目录
            $res = copy($source, $destination);
            // 移动成功
            if ($res) {
                $str2             = DS . $str;
                $where['img_src'] = $str2;
                // 删除session信息
                Session::delete('picsrc');
                $where['img_type']   = 0;
                $where['nav_type']   = $navType;
                $where['created_at'] = time();
                $where['update_by']  = Session::get('admin_id');
                // type为1时跳转到商品id
                if ($navType == 1) {
                    $where['nav_id'] = intval($request->param('select_goods'));
                }
                // type为2时跳转到文章id
                if ($navType == 2) {
                    $where['nav_id'] = intval($request->param('article_id'));
                }
                // 调用公共函数保存，参数false为新增
                $insert = saveData('banner', $where, false);
                if ($insert) {
                    return objReturn(0, '保存成功！');
                } else {
                    return objReturn(400, '保存失败！');
                }
            } else {
                return objReturn(400, '上传失败,请重新上传图片！');
            }
        }
        return objReturn(400, '保存失败,请上传图片！');
    }

    /**
     * 获取当前banner id
     * @return json banner 启用结果
     */
    public function bannerStart(Request $request)
    {
        $where['img_id']    = intval($request->param('id'));
        $where['status']    = 1;
        $where['update_by'] = Session::get('admin_id');
        // 调用公共函数，参数true为更新
        $update = saveData('banner', $where, true);
        if ($update) {
            return objReturn(0, '启用成功！');
        } else {
            return objReturn(400, '启用失败！');
        }
    }

    /**
     * 获取当前banner id
     * @return json banner 停用结果
     */
    public function bannerStop(Request $request)
    {
        $where['img_id']    = intval($request->param('id'));
        $where['status']    = 0;
        $where['update_by'] = Session::get('admin_id');
        $where['pause_at']  = time();
        // 调用公共函数，参数true为更新
        $update = saveData('banner', $where, true);
        if ($update) {
            return objReturn(0, '停用成功！');
        } else {
            return objReturn(400, '停用失败！');
        }
    }

    /**
     * 获取当前banner id
     * @return json 删除banner结果
     */
    public function bannerDel(Request $request)
    {
        $where['img_id']    = intval($request->param('id'));
        $where['status']    = 3;
        $where['update_by'] = Session::get('admin_id');
        // 调用公共函数，参数true为更新
        $update = saveData('banner', $where, true);
        if ($update) {
            return objReturn(0, '删除成功！');
        } else {
            return objReturn(400, '删除失败！');
        }
    }

    /**
     * 广告栏页面
     * @return html
     */
    public function adlist()
    {
        return $this->fetch();
    }

    /**
     * 获取父级分类信息
     * @return    ary        分类数组
     */
    public function catZtree(Request $request)
    {
        $catagory = new Catagory;
        $res      = $catagory->field('cat_id,parent_id,cname')->where('parent_id', 0)->where('status', 1)->order('sort desc')->select();
        if ($res) {
            // 构造返回数组
            foreach ($res as $key => $value) {
                $temp['id']    = $value['cat_id'];
                $temp['pId']   = $value['parent_id'];
                $temp['name']  = $value['cname'];
                $temp['open']  = 'true';
                $catagoryArr[] = $temp;
            }
            return objReturn(0, '数据获取成功！', $catagoryArr);
        } else {
            return objReturn(400, '数据获取失败！');
        }
    }

    /**
     * 广告栏信息
     * @param  Request $reques  参数
     * @return ary              返回值
     */
    public function getAdData(Request $request)
    {
        // 调用公共函数 1为ad
        $adData = getBanner(1, true);
        // dump($adData);die;
        return objReturn(0, '数据获取成功！', $adData);
    }

    /**
     * 根据一级分类选广告栏信息
     * @param  Request $request 参数
     * @return ary              返回值
     */
    public function selectAd(Request $request)
    {
        $cat_id = intval($request->param('cat_id'));
        $banner = new Banner;
        $res    = $banner->field('img_id, img_src, nav_type, nav_id, location, sort, status')->where('location', $cat_id)->where('img_type', 1)->where('status', '<>', 3)->select();
        if ($res) {
            foreach ($res as &$info) {
                $info['img_src'] = config('static_path') . $info['img_src'];
            }
            $res = count($res) == 1 ? $res[0] : $res;
        }else{
            $res = 401;
        }
        return objReturn(0, '数据获取成功！', $res);
    }

    /**
     * 广告栏添加
     * @return html 页面
     */
    public function adadd()
    {
        // 商品id
        $goods     = new Goods;
        $goodsData = $goods->field('goods_id,goods_name')->where('status', '<>', 4)->select();
        $this->assign('goodsData', $goodsData);
        // 文章id
        $article     = new Article;
        $articleData = $article->field('article_id,article_title')->where('status', '<>', 2)->select();
        $this->assign('articleData', $articleData);
        // 分类id
        $catagory = new Catagory;
        $catData  = $catagory->field('cat_id,parent_id,cname')->where('parent_id', 0)->where('status', 1)->order('sort desc')->select();
        $this->assign('catData', $catData);
        return $this->fetch();
    }

    /**
     * 添加广告栏
     * @return ary 添加结果
     */
    public function addAd(Request $request)
    {
        // $goodsId = intval($request->param('goods_id'));
        $navType             = intval($request->param('nav_type'));
        $where['status']     = intval($request->param('ad_active'));
        $where['img_type']   = 1;
        $where['created_at'] = time();
        $where['update_by']  = Session::get('admin_id');
        // 类型
        if ($navType == 1) {
            $where['nav_id']   = intval($request->param('goods_id'));
            $where['nav_type'] = $navType;
        }
        if ($navType == 2) {
            $where['nav_id']   = intval($request->param('article_id'));
            $where['nav_type'] = $navType;
        }
        if ($navType == 3) {
            $where['location'] = intval($request->param('cat_id'));
            $where['nav_type'] = $navType;
        }
        // 是否存在图片路径session
        if (Session::has('picsrc')) {
            $source = DEFAULT_STATIC_PATH . Session::get('picsrc');
            // 新的路径,取session值
            $word = DS . 'ad';
            $str  = substr_replace(Session::get('picsrc'), $word, 3, 4);
            // 创建文件夹
            $str1 = substr($str, 0, 16);
            if (!is_dir(DEFAULT_STATIC_PATH . $str1)) {
                mkdir(DEFAULT_STATIC_PATH . $str1);
            }
            // 框架应用根目录/public/static/img/目录
            $destination = DEFAULT_STATIC_PATH . $str;
            // 拷贝文件到指定目录
            $res = copy($source, $destination);
            // 移动成功
            if ($res) {
                $str2             = DS . $str;
                $where['img_src'] = $str2;
                // 删除session信息
                Session::delete('picsrc');
                // 调用公共函数保存，参数false为新增
                $insert = saveData('banner', $where, false);
                if ($insert) {
                    return objReturn(0, '保存成功！');
                } else {
                    return objReturn(400, '保存失败！');
                }
            } else {
                return objReturn(400, '上传失败,请重新上传图片！');
            }
        } else {
            return objReturn(400, '请重新上传图片！');
        }
    }

    /**
     * 获取当前ad id
     * @return json add 启用结果
     */
    public function adStart(Request $request)
    {
        $where['img_id']    = intval($request->param('id'));
        $where['status']    = 1;
        $where['update_by'] = Session::get('admin_id');
        // 调用公共函数，参数true为更新
        $update = saveData('banner', $where, true);
        if ($update) {
            return objReturn(0, '启用成功！');
        } else {
            return objReturn(400, '启用失败！');
        }
    }

    /**
     * 获取当前ad id
     * @return json ad 停用结果
     */
    public function adStop(Request $request)
    {
        $where['img_id']    = intval($request->param('id'));
        $where['status']    = 0;
        $where['update_by'] = Session::get('admin_id');
        $where['pause_at']  = time();
        // 调用公共函数，参数true为更新
        $update = saveData('banner', $where, true);
        if ($update) {
            return objReturn(0, '停用成功！');
        } else {
            return objReturn(400, '停用失败！');
        }
    }

    /**
     * 获取当前ad id
     * @return json 删除ad结果
     */
    public function adDel(Request $request)
    {
        $where['img_id']    = intval($request->param('id'));
        $where['status']    = 3;
        $where['update_by'] = Session::get('admin_id');
        // 调用公共函数，参数true为更新
        $update = saveData('banner', $where, true);
        if ($update) {
            return objReturn(0, '删除成功！');
        } else {
            return objReturn(400, '删除失败！');
        }
    }

    /**
     * 文章界面
     * @return ary 文章列表页面
     */
    public function article()
    {
        $article     = new Article;
        $articleData = $article->field('article_id,article_title,article_desc,article_brief,status')->where('status', '<>', 2)->select();
        $this->assign('articleData', $articleData);
        return $this->fetch();
    }

    /**
     * 添加文章列表页面
     * @return ary
     */
    public function articleadd()
    {
        return $this->fetch();
    }

    /**
     * 添加文章功能
     * @param Request $request
     * @return ary   返回值
     */
    public function addArticle(Request $request)
    {
        $where['article_title'] = htmlspecialchars($request->param('article_name'));
        $where['article_brief'] = htmlspecialchars($request->param('article_content'));
        $where['status']        = intval($request->param('article_active'));
        $where['created_at']    = time();
        if (!empty($request->param('article_picsrc'))) {
            $source = $request->param('article_picsrc');
            // 字符串分割为数组
            $temp = explode(',', $source);
            $srcArr = [];
            foreach ($temp as &$desc) {
                $te = explode(':', $desc);
                $srcArr[] = $te[0];
            }
            $src    = '';
            // 遍历数组移动目录图片
            foreach ($srcArr as $key => $value) {
                // 新的路径
                $word    = DS . 'article';
                $strTemp = substr_replace($value, $word, 3, 4);
                // dump($strTemp);die;
                // 创建文件夹
                $str3 = substr($strTemp, 0, 21);
                if (!is_dir(DEFAULT_STATIC_PATH . $str3)) {
                    mkdir(DEFAULT_STATIC_PATH . $str3);
                }
                // 框架应用根目录/public/static/img/目录
                $destination = DEFAULT_STATIC_PATH . $strTemp;
                $sou         = DEFAULT_STATIC_PATH . $value;
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
                $v = DS.$v;
                $word = DS.'goods';
                $picDesc = substr_replace($v,$word,4,4);
                $picDesc .= ','.$picDesc;
            }
            // dump($picDesc);die;
            $where['article_desc'] = $picDesc;
            // 调用公共函数保存，参数false为新增
            $insert = saveData('article', $where, false);
            if ($insert) {
                return objReturn(0, '保存成功！');
            } else {
                return objReturn(400, '保存失败！');
            }
        } else {
            return objReturn(400, '上传失败,请重新上传图片！');
        }
    }

    /**
     * 获取当前article id
     * @return json article 启用结果
     */
    public function articleStart(Request $request)
    {
        $where['article_id'] = intval($request->param('id'));
        $where['status']     = 1;
        // 调用公共函数，参数true为更新
        $update = saveData('article', $where, true);
        if ($update) {
            return objReturn(0, '启用成功！');
        } else {
            return objReturn(400, '启用失败！');
        }
    }

    /**
     * 获取当前article id
     * @return json article 停用结果
     */
    public function articleStop(Request $request)
    {
        $where['article_id'] = intval($request->param('id'));
        $where['status']     = 0;
        // 调用公共函数，参数true为更新
        $update = saveData('article', $where, true);
        if ($update) {
            return objReturn(0, '停用成功！');
        } else {
            return objReturn(400, '停用失败！');
        }
    }

    /**
     * 获取当前article id
     * @return json 删除article结果
     */
    public function articleDel(Request $request)
    {
        $where['article_id'] = intval($request->param('id'));
        $where['status']     = 2;
        // 调用公共函数，参数true为更新
        $update = saveData('article', $where, true);
        if ($update) {
            return objReturn(0, '删除成功！');
        } else {
            return objReturn(400, '删除失败！');
        }
    }

    /**
     * @return 用户协议页面
     */
    public function clauselist()
    {
        $clause = new ClauseDb;
        $info   = $clause->where('idx', 1)->find();
        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 修改用户协议
     * @return json 修改结果
     */
    public function updateClause(Request $request)
    {
        $clause            = new ClauseDb;
        $content['idx']    = 1;
        $content['clause'] = htmlspecialchars($request->param('content'));
        $update            = $clause->update($content);
        if ($update) {
            return objReturn(0, '修改成功');
        } else {
            return objReturn(400, '修改失败');
        }
    }

// ***********************
    /**
     * 关键词列表界面
     */
    public function searchlist()
    {
        $article      = new Article;
        $catagory     = new Catagory;
        $goods        = new Goods;
        $searchKwData = getSearchValue();
        //收集关联id
        $goods_ids    = [];
        $article_ids  = [];
        $catagory_ids = [];
        foreach ($searchKwData as $k => $v) {
            if ($v['nav_type'] == 0 || $v['nav_type'] == 3) {
                $searchKwData[$k]['nav_id'] = '--';
                continue;
            }
            if ($v['nav_type'] == 1) {
                $goods_ids[] = $v['nav_id'];
                continue;
            }
            if ($v['nav_type'] == 2) {
                $article_ids[] = $v['nav_id'];
                continue;
            }
            if ($v['nav_type'] == 4) {
                $catagory_ids[] = $v['nav_id'];
            }
        }
        $catagory_ids = array_unique($catagory_ids);
        $article_ids  = array_unique($article_ids);
        $goods_ids    = array_unique($goods_ids);
        //获取对应的名字
        $goods_data    = $goods->field('goods_id,goods_name')->where(['goods_id' => ['in', $goods_ids]])->select();
        $article_data  = $article->field('article_id,article_title')->where(['article_id' => ['in', $article_ids]])->select();
        $catagory_data = $catagory->field('cat_id,cname')->where(['cat_id' => ['in', $catagory_ids]])->select();
        foreach ($searchKwData as $k => $v) {
            if ($v['nav_type'] == 1) {
                foreach ($goods_data as $kk => $vv) {
                    if ($v['nav_id'] == $vv['goods_id']) {
                        $searchKwData[$k]['nav_id'] = $vv['goods_name'];
                        break;
                    }
                }
                continue;
            }
            if ($v['nav_type'] == 2) {
                foreach ($article_data as $kk => $vv) {
                    if ($v['nav_id'] == $vv['article_id']) {
                        $searchKwData[$k]['nav_id'] = $vv['article_title'];
                        break;
                    }
                }
                continue;
            }
            if ($v['nav_type'] == 4) {
                foreach ($catagory_data as $kk => $vv) {
                    if ($v['nav_id'] == $vv['cat_id']) {
                        $searchKwData[$k]['nav_id'] = $vv['cname'];
                        break;
                    }
                }
                continue;
            }
        }
        $this->assign('data', $searchKwData);
        return $this->fetch();
    }

    /**
     * 添加关键词界面
     * @param
     * @return  result 添加结果
     */
    public function searchadd(Request $request)
    {
        if ($request->isPost()) {
            $searchKwDb         = new Search_kw;
            $data               = $request->post();
            $data['nav_id']     = empty($data['nav_id']) ? 0 : $data['nav_id'];
            $data['created_at'] = time();
            $result             = $searchKwDb->insert($data);
            if ($result) {
                return objReturn(0, '添加关键词成功!');
            } else {
                return objReturn(400, '添加关键词失败!');
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 删除关键词
     * @param   idx  关键词ID
     * @return  result 删除结果
     */
    public function searchDelete(Request $request)
    {
        $idx            = $request->param('idx');
        $searchKwDb     = new Search_kw;
        $data['status'] = 2;
        $result         = $searchKwDb->where(['idx' => $idx])->update($data);
        if ($result) {
            return objReturn(0, '关键词删除成功!');
        } else {
            return objReturn(400, '关键词删除失败!');
        }
    }

    /**
     * 改变关键词状态
     * @param   idx  关键词ID
     * @param   status 关键词当前状态
     * @return  result 操作结果
     */
    public function searchaChange(Request $request)
    {
        $searchKwDb = new Search_kw;
        $idx        = $request->param('idx');
        $status     = $request->param('status');
        $msg        = '';
        $error      = '';
        if ($status == 0) {
            $data['status'] = 1;
            $msg            = '启用成功!';
            $error          = '启用失败!';
        } else {
            $data['status'] = 0;
            $msg            = '关闭成功!';
            $error          = '关闭失败!';
        }
        $result = $searchKwDb->where(['idx' => $idx])->update($data);
        if ($result) {
            return objReturn(0, $msg);
        } else {
            return objReturn(400, $error);
        }
    }

    /**
     * 关键词编辑
     * @param   idx  关键词ID
     * @return  result 编辑结果
     */
    public function searchedit(Request $request)
    {
        $idx        = $request->param('idx');
        $searchKwDb = new Search_kw;
        if ($request->post()) {
            $data              = $request->post();
            $data['nav_id']    = empty($data['nav_id']) ? 0 : $data['nav_id'];
            $data['update_at'] = time();
            $data['update_by'] = Session::get('admin_id');
            $result            = $searchKwDb->where(['idx' => $idx])->update($data);
            if ($result) {
                return objReturn(0, '修改关键词成功!');
            } else {
                return objReturn(400, '修改关键词失败!');
            }

        } else {
            $searchKwData = $searchKwDb->where(['idx' => $idx])->find();
            $this->assign('data', $searchKwData);
            return $this->fetch();
        }
    }

    /**
     * 获取商品数据或文章数据
     * @param   type  判断获取文章还是商品
     * @return  array 返回商品数据或文章数据
     */
    public function getSelect(Request $request)
    {
        $type = $request->param('type');
        if ($type == 'goods') {
            $goods     = new Goods;
            $goodsInfo = $goods->field('goods_id,goods_name')->where('status', '<>', 4)->select();
            return json($goodsInfo);
        } else {
            $article     = new Article;
            $articleData = $article->field('article_id,article_title')->where('status', '<>', 2)->select();
            return json($articleData);
        }
    }

    /**
     * 用户反馈列表界面
     * @return array 用户反馈列表数据
     */
    public function feedbacklist()
    {
        $feedbackData = getFeedback();
        //收集用户id与管理员id
        $user_ids  = [];
        $admin_ids = [];
        foreach ($feedbackData as $k => $v) {
            $user_ids[]  = $v['uid'];
            $admin_ids[] = $v['reply_by'];
        }
        $user_ids  = array_unique($user_ids);
        $admin_ids = array_unique($admin_ids);
        //获取对应的用户名
        $user_profile = new User_profile;
        $user_data    = $user_profile->field('uid,nickName')->where(['uid' => ['in', $user_ids]])->select();
        foreach ($feedbackData as $k => $v) {
            foreach ($user_data as $kk => $vv) {
                if ($v['uid'] == $vv['uid']) {
                    $feedbackData[$k]['uid'] = $vv['nickName'];
                    break;
                }
            }
        }
        //获取对应的管理员名字
        $admin      = new Admin;
        $admin_data = $admin->field('admin_id,username')->where(['admin_id' => ['in', $admin_ids]])->select();
        foreach ($feedbackData as $k => $v) {
            foreach ($admin_data as $kk => $vv) {
                if ($v['reply_by'] == $vv['admin_id']) {
                    $feedbackData[$k]['reply_by'] = $vv['username'];
                    break;
                }
            }
        }
        //模块渲染
        $this->assign('data', $feedbackData);
        return $this->fetch();
    }

    /**
     * 用户反馈回复界面
     * @param  idx    用户反馈记录ID
     * @param  reply  回复内容
     * @return result 回复结果
     */
    public function feedbackReply(Request $request)
    {
        $feedbackDb = new Feedback;
        $idx        = $request->param('idx');
        if ($request->isPost()) {
            $data['reply'] = $request->param('reply');
            $status        = $request->param('status');
            if ($status != 0) {
                return objReturn(0, '此反馈已处理!');
            }
            $data['reply_at'] = time();
            $data['reply_by'] = Session::get('admin_id');
            $data['status']   = 1;
            $result           = $feedbackDb->where(['idx' => $idx])->update($data);
            if ($result) {
                return objReturn(0, '回复成功!');
            } else {
                return objReturn(400, '回复失败!');
            }
        } else {
            $feedbackData = $feedbackDb->where(['idx' => $idx])->find();
            $this->assign('data', $feedbackData);
            return $this->fetch();
        }
    }

    /**
     * 问答列表界面
     * @return array
     */
    public function questionlist()
    {
        $questionData = getSysQA();
        $this->assign('data', $questionData);
        return $this->fetch();
    }

    /**
     * 问答添加界面
     * @param  question 问题
     * @param  answer   回答
     * @return result 添加结果
     */
    public function questionadd(Request $request)
    {
        if ($request->isPost()) {
            $data               = $request->post();
            $data['created_at'] = time();
            $data['created_by'] = Session::get('admin_id');
            $questionDb         = new QuestionDb;
            $result             = $questionDb->insert($data);
            if ($result) {
                return objReturn(0, '添加问答成功!');
            } else {
                return objReturn(400, '添加问答失败!');
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 问答编辑界面
     * @param  question_id  问答ID
     * @param  question 问题
     * @param  answer   回答
     * @return result 编辑结果
     */
    public function questionedit(Request $request)
    {
        $questionDb  = new QuestionDb;
        $question_id = $request->param('question_id');
        if ($request->isPost()) {
            $data['question']  = $request->param('question');
            $data['answer']    = $request->param('answer');
            $data['update_at'] = time();
            $data['update_by'] = Session::get('admin_id');
            $result            = $questionDb->where(['question_id' => $question_id])->update($data);
            if ($result) {
                return objReturn(0, '编辑问答成功!');
            } else {
                return objReturn(400, '编辑问答失败!');
            }
        } else {
            $questionData = $questionDb->where(['question_id' => $question_id])->find();
            $this->assign('data', $questionData);
            return $this->fetch();
        }
    }

    /**
     * 问答的删除
     * @param  question_id  问答ID
     * @return result 删除结果
     */
    public function questiondelete(Request $request)
    {
        $questionDb     = new QuestionDb;
        $question_id    = $request->param('question_id');
        $data['status'] = 2;
        $result         = $questionDb->where(['question_id' => $question_id])->update($data);
        if ($result) {
            return objReturn(0, '删除问答成功!');
        } else {
            return objReturn(400, '删除问答失败!');
        }
    }

    /**
     * 改变问答的状态
     * @param  question_id  问答ID
     * @return result 改变状态结果
     */
    public function questionChange(Request $request)
    {
        $questionDb  = new QuestionDb;
        $question_id = $request->param('question_id');
        $status      = $request->param('status');
        $msg         = '';
        $error       = '';
        if ($status == 0) {
            $data['status'] = 1;
            $msg            = '启用成功!';
            $error          = '启用失败!';
        } else {
            $data['status'] = 0;
            $msg            = '关闭成功!';
            $error          = '关闭失败!';
        }
        $result = $questionDb->where(['question_id' => $question_id])->update($data);
        if ($result) {
            return objReturn(0, $msg);
        } else {
            return objReturn(400, $error);
        }
    }

}
