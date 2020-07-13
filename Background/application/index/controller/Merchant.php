<?php
namespace app\index\controller;

use \app\index\model\Admin;
use \app\index\model\Merchant as MerchantDb;
use \app\index\model\Merchant_apply;
use \app\index\model\Merchant_log;
use \think\Controller;
use \think\Db;
use \think\File;
use \think\Request;
use \think\Session;

class Merchant extends Controller
{
    /**
     * 商家入驻审核界面
     * @param    pass_reason 审核说明
     * @param    status 审核状态
     * @param    idx 商家入驻申请ID
     * @return   审核结果
     */
    public function admissionedit(Request $request)
    {
        $idx               = $request->param('idx');
        $merchantApplyDb   = new Merchant_apply;
        $merchantApplyData = $merchantApplyDb->where(['idx' => $idx])->find()->toArray();
        if ($merchantApplyData['status'] == 2) {
            $this->redirect('merchant/admissionlist');
        }
        if ($request->isPost()) {
            $merchantDb = new MerchantDb;
            //整理merchant_apply表数据
            $merchantApplyData['pass_reason'] = $request->param('pass_reason');
            $merchantApplyData['status']      = $request->param('status');
            $merchantApplyData['update_by']   = Session::get('admin_id');
            $merchantApplyData['update_at']   = time();
            if ($merchantApplyData['status'] == 2) {
                $merchantApplyData['pass_at'] = time();
                $merchantApplyData['pass_by'] = Session::get('admin_id');
                //整理merchant表数据
                $merchantData               = [];
                $merchantData['mch_id']     = $merchantApplyData['mch_id'];
                $merchantData['mch_name']   = $merchantApplyData['mch_name'];
                $merchantData['mch_cert']   = $merchantApplyData['mch_cert'];
                $merchantData['mch_logo']   = $merchantApplyData['mch_logo'];
                $merchantData['created_at'] = time();
                //开启事务
                Db::startTrans();
                $result1 = $merchantApplyDb->where(['idx' => $idx])->update($merchantApplyData);
                $result2 = $merchantDb->insert($merchantData);
                if ($result1 && $result2) {
                    Db::commit();
                    return objReturn(0, '操作成功!');
                } else {
                    Db::rollback();
                    return objReturn(400, '操作失败,请重试!');
                }
            }
            $result = $merchantApplyDb->where(['idx' => $idx])->update($merchantApplyData);
            if ($result) {
                return objReturn(0, '操作成功!');
            } else {
                return objReturn(400, '操作失败,请重试!');
            }
        } else {
            $image                           = explode(',', $merchantApplyData['mch_cert']);
            $merchantApplyData['image_one']  = empty($image[0]) ? '' : '/static' . $image[0];
            $merchantApplyData['image_two']  = empty($image[1]) ? '' : '/static' . $image[1];
            $merchantApplyData['mch_logo']   = '/static' . $merchantApplyData['mch_logo'];
            $merchantApplyData['created_at'] = date('Y-m-d H:i:s', $merchantApplyData['created_at']);
            $this->assign('data', $merchantApplyData);
            return $this->fetch();
        }
    }

    /**
     * admissionlist 入驻申请列表界面
     */
    public function admissionlist()
    {
        return $this->fetch();
    }

    /**
     * admissionDetail 入驻申请列表数据
     * @return   array  申请列表数据
     */
    public function admissionDetail()
    {
        $merchantApplyDb   = new Merchant_apply;
        $merchantApplyData = $merchantApplyDb->field('idx,mch_name,mch_cert,created_at,status,update_by,pass_at,pass_reason,mch_logo')->select();
        $markAdmin         = [];
        foreach ($merchantApplyData as $k => $v) {
            if ($v['status'] == 0) {
                $merchantApplyData[$k]['status'] = '待审核';
            } else if ($v['status'] == 1) {
                $merchantApplyData[$k]['status'] = '未通过';
            } else {
                $merchantApplyData[$k]['status'] = '已通过';
            }
            //记录管理员ID
            if (empty($v['update_by'])) {
                $merchantApplyData[$k]['update_by'] = '--';
            } else {
                $markAdmin[] = $v['update_by'];
            }
            //时间相关处理
            $merchantApplyData[$k]['pass_at']     = empty($v['pass_at']) ? '--' : date('Y-m-d H:i:s', $v['pass_at']);
            $merchantApplyData[$k]['pass_reason'] = empty($v['pass_reason']) ? '--' : $v['pass_reason'];
            $merchantApplyData[$k]['update_at']   = empty($v['update_at']) ? '--' : date('Y-m-d H:i:s', $v['update_at']);
            $merchantApplyData[$k]['created_at']  = date('Y-m-d H:i:s', $v['created_at']);
            //图片处理
            $merchantApplyData[$k]['mch_logo']  = '/static' . $v['mch_logo'];
            $image                              = explode(',', $v['mch_cert']);
            $merchantApplyData[$k]['image_one'] = empty($image[0]) ? '' : '/static' . $image[0];
            $merchantApplyData[$k]['image_two'] = empty($image[1]) ? '' : '/static' . $image[1];
        }
        //获取管理员名字
        if (!empty($markAdmin)) {
            $markAdmin    = array_unique($markAdmin);
            $markAdminStr = implode(',', $markAdmin);
            $adminDb      = new Admin;
            $adminData    = $adminDb->field('admin_id,username')->where(['admin_id' => ['in', $markAdminStr]])->select();
            foreach ($merchantApplyData as $k => $v) {
                foreach ($adminData as $kk => $vv) {
                    if ($v['update_by'] == $vv['admin_id']) {
                        $merchantApplyData[$k]['update_by'] = $vv['username'];
                        break;
                    }
                }
            }
        }
        return json($merchantApplyData);
    }

    /**
     * 城市联动
     * @return  array
     */
    public function locationList()
    {
        $data       = Db('area')->field('code,name')->where('level != 4')->select();
        $handleData = [];
        foreach ($data as $k => $v) {
            $handleData[$v['code']] = $v['name'];
        }
        return json($handleData);
    }

    /**
     * merchantlist 商家列表界面
     */
    public function merchantlist()
    {
        return $this->fetch();
    }

    /**
     * merchantDetail 获取商家列表数据
     * @return   array  商家列表数据
     */
    public function merchantDetail()
    {
        $merchantDb   = new MerchantDb;
        $merchantData = $merchantDb->field('idx,mch_name,created_at,mch_cert,pause_at,mch_name,location,status,pause_reason,mch_logo,address,address_code')->where(' status != 3 ')->select();
        //记录区域code
        $allLocation = [];
        foreach ($merchantData as $k => $v) {
            $merchantData[$k]['address_code'] = explode('_', $v['address_code']);
            foreach ($merchantData[$k]['address_code'] as $ka => $va) {
                $allLocation[] = $va;
            }
            $merchantData[$k]['mch_logo']   = '/static' . $merchantData[$k]['mch_logo'];
            $merchantData[$k]['created_at'] = date('Y-m-d H:i:s', $v['created_at']);
            $image                          = explode(',', $v['mch_cert']);
            $merchantData[$k]['image_one']  = empty($image[0]) ? '' : $image[0];
            $merchantData[$k]['image_two']  = empty($image[1]) ? '' : $image[1];
            if (!empty($v['pause_at'])) {
                $merchantData[$k]['pause_at'] = date('Y-m-d H:i:s', $v['pause_at']);
            }
        }
        //区域code获取区域名称
        $specialCity = ['北京', '上海', '重庆', '天津'];
        $allLocation = array_filter($allLocation);
        $allLocation = array_unique($allLocation);
        $place_name  = Db('area')->where(['code' => ['in', $allLocation]])->field('code,name')->select();
        foreach ($merchantData as $k => $v) {
            $merchantData[$k]['place_name'] = '';
            if ($v['address_code']) {
                foreach ($v['address_code'] as $ak => $av) {
                    foreach ($place_name as $pk => $pv) {
                        if ($av == $pv['code'] && !in_array($pv['name'], $specialCity)) {
                            $merchantData[$k]['place_name'] .= $pv['name'];
                        }
                    }
                }
            }
            $merchantData[$k]['place_name'] = $merchantData[$k]['place_name'] . $merchantData[$k]['address'];
        }
        return json($merchantData);
    }

    /**
     * merchantChange 修改商家营业状态
     * @param    idx  商家ID
     * @return   result  操作结果
     */
    public function merchantChange(Request $request)
    {
        $idx              = $request->param('idx');
        $data['status']   = $request->param('status');
        $data['pause_at'] = time();
        $merchantDb       = new MerchantDb;
        if ($data['status'] == 1) {
            $data['pause_at']     = '';
            $data['pause_reason'] = '';
            $result               = $merchantDb->where(['idx' => $idx])->update($data);
            if ($result) {
                return objReturn(0, '操作成功!');
            } else {
                return objReturn(400, '操作失败,请重试!');
            }
        } else {
            $data['pause_reason'] = $request->param('pause_reason');
            if (empty($data['pause_reason'])) {
                return objReturn(400, '请填写操作原因!');
            }
            $result = $merchantDb->where(['idx' => $idx])->update($data);
            if (!$result) {
                return objReturn(400, '操作失败,请重试!');
            }
            $data['mch_id'] = $request->param('mch_id');
            $data['reason'] = $data['pause_reason'];
            unset($data['pause_reason']);
            unset($data['status']);
            $data['created_at'] = $data['pause_at'];
            $merchantLogDb      = new Merchant_log;
            $merchantLogDb->insert($data);
            return objReturn(0, '操作成功!');
        }
        return objReturn(400, '操作失败,请重试!');
    }

    /**
     * merchantedit 商家资料编辑界面
     * @param  idx 商家ID
     * @return result 编辑结果
     */
    public function merchantedit(Request $request)
    {
        $merchantDb               = new MerchantDb;
        $idx                      = $request->param('idx');
        $merchantData             = $merchantDb->field('idx,mch_name,mch_cert,location,mch_logo,address,address_code')->where(['idx' => $idx])->find()->toArray();
        $merchantData['mch_logo'] = '/static' . $merchantData['mch_logo'];
        //城市code获取对应城市名
        $address_code = explode('_', $merchantData['address_code']);
        $place_name   = Db('area')->where(['code' => ['in', $address_code]])->field('name')->select();
        //资质图片处理
        $image    = explode(',', $merchantData['mch_cert']);
        $image[0] = empty($image[0]) ? '' : '/static' . $image[0];
        $image[1] = empty($image[1]) ? '' : '/static' . $image[1];
        //模板渲染
        $this->assign('place_name', $place_name);
        $this->assign('image', $image);
        $this->assign('data', $merchantData);
        return $this->fetch();
    }

    /**
     * 添加商家界面
     */
    public function addmerchant()
    {
        return $this->fetch();
    }

    /**
     * 添加商家
     * @return Json $result 添加结果
     */
    public function merchantAdd(Request $request)
    {
        $data     = $request->except(['image_one', 'image_two', 'mch_logo', 'province', 'city', 'area'], 'post');
        $img_one  = $request->file('image_one');
        $img_two  = $request->file('image_two');
        $mch_logo = $request->file('mch_logo');
        //接收具体位置
        $province             = $request->param('province');
        $city                 = empty($request->param('city')) ? '_' : '_' . $request->param('city') . '_';
        $area                 = $request->param('area');
        $data['address_code'] = $province . $city . $area;
        //商家资质图片存储目录
        $data['mch_cert'] = '';
        $dir              = '.' . DS . 'static' . DS . 'img' . DS . 'merchant' . DS;
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        //上传商家LOGO
        $info = $mch_logo->move($dir);
        if ($info) {
            $saveName         = $info->getSaveName();
            $data['mch_logo'] = ltrim($dir . $saveName, './');
            $data['mch_logo'] = ltrim($data['mch_logo'], 'static');
        } else {
            return objReturn(400, $img_one->getError());
        }
        //第一张图上传
        if ($img_one) {
            $info = $img_one->move($dir);
            if ($info) {
                $saveName = $info->getSaveName();
                $strTemp  = ltrim($dir . $saveName, './');
                $data['mch_cert'] .= ltrim($strTemp, 'static');
            } else {
                return objReturn(400, $img_one->getError());
            }
        }
        //第二张图上传
        if ($img_two) {
            $info = $img_two->move($dir);
            if ($info) {
                $saveName = $info->getSaveName();
                $strTemp  = ltrim($dir . $saveName, './');
                $strTemp  = ltrim($strTemp, 'static');
                if ($data['mch_cert']) {
                    $data['mch_cert'] = $data['mch_cert'] . ',' . $strTemp;
                } else {
                    $data['mch_cert'] = $strTemp;
                }
            } else {
                return objReturn(400, $img_two->getError());
            }
        }
        if (empty($data['mch_cert'])) {
            return objReturn(400, '请至少上传一张商家资质证明图片');
        }
        $data['created_at'] = time();
        $data['mch_id']     = generateSn();
        //插入数据库
        $merchantDb = new MerchantDb;
        $result     = $merchantDb->insert($data);
        //反馈
        if ($result) {
            return objReturn(0, '添加商家成功!');
        } else {
            return objReturn(400, '添加商家失败!');
        }
    }

    /**
     * uploadimage 商家资料修改
     * @param  idx  商家ID
     * @param  mch_name 商家名称
     * @param  mch_cert 商家资质证书
     * @return result 修改结果
     */
    public function uploadImage(Request $request)
    {
        $idx              = $request->param('idx');
        $merchantDb       = new MerchantDb;
        $data['mch_name'] = $request->param('mch_name');
        $data['location'] = $request->param('location');
        $data['address']  = $request->param('address');
        $image_one        = $request->file('image_one');
        $image_two        = $request->file('image_two');
        $mch_logo         = $request->file('mch_logo');
        //接收具体位置
        $province             = $request->param('province');
        $city                 = empty($request->param('city')) ? '_' : '_' . $request->param('city') . '_';
        $area                 = $request->param('area');
        $data['address_code'] = $province . $city . $area;
        //商家资质图片存储目录
        $dir = '.' . DS . 'static' . DS . 'img' . DS . 'merchant' . DS;
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        //上传商家logo
        if ($mch_logo) {
            $info = $mch_logo->move($dir);
            if ($info) {
                $saveName         = $info->getSaveName();
                $data['mch_logo'] = ltrim($dir . $saveName, './');
                $data['mch_logo'] = ltrim($data['mch_logo'], 'static');
            } else {
                return objReturn(400, $mch_logo->getError());
            }
        }
        //第一张图片上传
        $data['mch_cert'] = '';
        if (!$image_one) {
            $old_image_one = ltrim($request->param('old_image_one'), '/');
            $data['mch_cert'] .= ltrim($old_image_one, 'static');
        } else {
            $info = $image_one->move($dir);
            if ($info) {
                $saveName = $info->getSaveName();
                $strTemp  = ltrim($dir . $saveName, './');
                $data['mch_cert'] .= ltrim($strTemp, 'static');
            } else {
                return objReturn(400, $image_one->getError());
            }
        }
        //第二张图片上传
        $image_two_str = '';
        if (!$image_two) {
            $old_image_two = ltrim($request->param('old_image_two'), '/');
            $image_two_str = ltrim($old_image_two, 'static');
        } else {
            $info = $image_two->move($dir);
            if ($info) {
                $saveName      = $info->getSaveName();
                $strTemp       = ltrim($dir . $saveName, './');
                $image_two_str = ltrim($strTemp, 'static');
            } else {
                return objReturn(400, $image_two->getError());
            }
        }
        if ($data['mch_cert']) {
            $data['mch_cert'] = $data['mch_cert'] . ',' . $image_two_str;
        } else {
            $data['mch_cert'] = $image_two_str;
        }
        $data['mch_cert'] = trim($data['mch_cert'], ',');
        //保存数据库
        $data['update_at'] = time();
        $result            = $merchantDb->where(['idx' => $idx])->update($data);
        //反馈
        if ($result) {
            return objReturn(0, '商家资料保存成功!');
        } else {
            return objReturn(400, '商家资料保存失败,请重试!');
        }
    }

    /**
     * 商家删除
     * @param  int $idx  IDX
     * @return Json $result 删除结果
     */
    public function merchantDelete(Request $request)
    {
        $idx        = $request->param('idx');
        $merchantDb = new MerchantDb;
        $result     = $merchantDb->where(['idx' => $idx])->update(['status' => 3]);
        //反馈
        if ($result) {
            return objReturn(0, '删除商家成功!');
        } else {
            return objReturn(400, '删除商家失败!');
        }
    }

    /**
     * 库存管理
     * @param  int $mch_id  商家ID
     * @return array $data  库存数据
     */
    public function stock(Request $request)
    {
        $mch_id   = $request->param('mch_id');
        $mch_name = $request->param('mch_name');
        $field    = 'goods_name,goods_img';
        $goods    = getGoods($field, $mch_id);
        $this->assign('mch_name', $mch_name);
        $this->assign('data', $goods);
        return $this->fetch();
    }

    public function location(Request $request)
    {
        $location = $request->param('xy');
        $temp     = explode(',', $location);
        $temp[0]  = $temp[0] + 0.005404;
        $temp[1]  = $temp[1] + 0.006127;
        $location = $temp[0] . ',' . $temp[1];
        $this->assign('location', $location);
        return $this->fetch();
    }
}
