<?php
namespace app\index\controller;

use \app\index\model\Merchant as MerchantDb;
use \app\index\model\Merchant_apply;
use \app\index\model\Merchant_log;
use \think\Controller;
use \think\Db;
use \think\File;
use \think\Request;

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
            // $merchantApplyData['update_by'] =
            $merchantApplyData['update_at'] = time();
            if ($merchantApplyData['status'] == 2) {
                $merchantApplyData['pass_at'] = time();
                //整理merchant表数据
                $merchantData               = [];
                $merchantData['mch_id']     = $merchantApplyData['mch_id'];
                $merchantData['mch_name']   = $merchantApplyData['mch_name'];
                $merchantData['mch_cert']   = $merchantApplyData['mch_cert'];
                $merchantData['created_at'] = time();
                $merchantData['status']    = 1;
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
            $merchantApplyData['image_one']  = empty($image[0]) ? '' : $image[0];
            $merchantApplyData['image_two']  = empty($image[1]) ? '' : $image[1];
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
        $merchantApplyData = $merchantApplyDb->select();
        foreach ($merchantApplyData as $k => $v) {
            if ($v['status'] == 0) {
                $merchantApplyData[$k]['status'] = '待审核';
            } else if ($v['status'] == 1) {
                $merchantApplyData[$k]['status'] = '未通过';
            } else {
                $merchantApplyData[$k]['status'] = '已通过';
            }
            $merchantApplyData[$k]['update_by']   = empty($v['update_by']) ? '--' : $v['update_by'];
            $merchantApplyData[$k]['pass_at']     = empty($v['pass_at']) ? '--' : date('Y-m-d H:i:s', $v['pass_at']);
            $merchantApplyData[$k]['pass_reason'] = empty($v['pass_reason']) ? '--' : $v['pass_reason'];
            $merchantApplyData[$k]['update_at']   = empty($v['update_at']) ? '--' : date('Y-m-d H:i:s', $v['update_at']);
            $merchantApplyData[$k]['created_at']  = date('Y-m-d H:i:s', $v['created_at']);
            $image                                = explode(',', $v['mch_cert']);
            $merchantApplyData[$k]['image_one']   = empty($image[0]) ? '' : $image[0];
            $merchantApplyData[$k]['image_two']   = empty($image[1]) ? '' : $image[1];
        }
        return json($merchantApplyData);
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
        $merchantData = $merchantDb->select();
        foreach ($merchantData as $k => $v) {
            $merchantData[$k]['created_at'] = date('Y-m-d H:i:s', $v['created_at']);
            $image                          = explode(',', $v['mch_cert']);
            $merchantData[$k]['image_one']  = empty($image[0]) ? '' : $image[0];
            $merchantData[$k]['image_two']  = empty($image[1]) ? '' : $image[1];
            if (!empty($v['pause_at'])) {
                $merchantData[$k]['pause_at'] = date('Y-m-d H:i:s', $v['pause_at']);
            }
        }
        return json($merchantData);
    }

    /**
     * merchantedit 商家资料编辑界面
     * @param  idx 商家ID
     * @return result 编辑结果
     */
    public function merchantedit(Request $request)
    {
        $merchantDb   = new MerchantDb;
        $idx          = $request->param('idx');
        $merchantData = $merchantDb->field('idx,mch_name,mch_cert')->where(['idx' => $idx])->find()->toArray();
        $image        = explode(',', $merchantData['mch_cert']);
        $image[0]     = empty($image[0]) ? '' : $image[0];
        $image[1]     = empty($image[1]) ? '' : $image[1];
        $this->assign('image', $image);
        $this->assign('data', $merchantData);
        return $this->fetch();
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
     * uploadimage 商家资料修改
     * @param  idx  商家ID
     * @param  mch_name 商家名称
     * @param  mch_cert 商家资质证书
     * @return result 修改结果
     */
    public function uploadimage(Request $request)
    {
        $idx              = $request->param('idx');
        $merchantDb       = new MerchantDb;
        $data['mch_name'] = $request->param('mch_name');
        $image_one        = $request->file('image_one');
        $image_two        = $request->file('image_two');

        //商家资质图片存储目录
        $dir = '.' . DS . 'static' . DS . 'img' . DS . 'merchant' . DS;
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        if ($image_one && $image_two) {
            $info = $image_one->move($dir);
            if ($info) {
                $saveName     = $info->getSaveName();
                $filePath_one = ltrim($dir . $saveName, '.');
            } else {
                return objReturn(400, $image_one->getError());
            }

            $info = $image_two->move($dir);
            if ($info) {
                $saveName     = $info->getSaveName();
                $filePath_two = ltrim($dir . $saveName, '.');
            } else {
                return objReturn(400, $image_two->getError());
            }
            $data['mch_cert'] = $filePath_one . ',' . $filePath_two;
            $result           = $merchantDb->where(['idx' => $idx])->update($data);
            if ($result) {
                return objReturn(0, '商家资料修改成功!');
            } else {
                return objReturn(400, '商家资料修改失败,请重试!');
            }
        }

        $result = $merchantDb->where(['idx' => $idx])->update($data);
        if ($result) {
            return objReturn(0, '商家名称修改成功,修改商家资质图片需要同时修改两张图片!');
        } else {
            return objReturn(400, '商家资料修改失败,修改商家资质图片需要同时修改两张图片!');
        }
    }
}
