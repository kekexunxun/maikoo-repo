<?php
namespace app\index\controller;

use \app\index\model\Coupon;
use \think\Controller;
use \think\Request;

class Promotion extends Controller
{
    /**
     * 修改优惠券使用状态
     * @param    coupon_id 优惠券ID
     * @param    status  优惠券当前状态
     * @return   result 修改结果
     */
    public function couponChange(Request $request)
    {
        $couponDb       = new Coupon;
        $coupon_id      = $request->param('coupon_id');
        $data['status'] = $request->param('status');
        if (empty($data['status'])) {
            return objReturn(400, '此优惠券已停止使用!');
        }
        if ($data['status'] == 3) {
            $data['pause_at'] = time();
        }
        $result = $couponDb->where(['coupon_id' => $coupon_id])->update($data);
        if ($result) {
            return objReturn(0, '优惠券使用状态修改成功!');
        }
        return objReturn(400, '优惠券使用状态修改失败!');
    }

    /**
     * couponadd 添加优惠券
     * @param    coupon_name 优惠券名称
     * @param    money   优惠券金额
     * @param    send_start_at  开始时间
     * @param    send_end_at  过期时间
     * @param    condition  满金额可用
     * @param    total_num  发放总数
     * @return   result 添加结果
     */
    public function couponadd(Request $request)
    {
        if ($request->isPost()) {
            $data                  = $request->post();
            $data['coupon_sn']     = time();
            $data['created_at']    = time();
            $data['send_start_at'] = strtotime($data['send_start_at']);
            $data['send_end_at']   = strtotime($data['send_end_at']);
            $couponDb              = new Coupon;
            $result                = $couponDb->insert($data);
            if ($result) {
                return objReturn(0, '添加优惠券成功!');
            } else {
                return objReturn(400, '添加优惠券成功!');
            }
        } else {
            $nowDay = date('Y-m-d H:i:s', time());
            $this->assign('nowDay', $nowDay);
            return $this->fetch();
        }
    }

    /**
     * couponlist 优惠券列表界面
     */
    public function couponlist()
    {
        return $this->fetch();
    }

    /**
     * couponDetail 获取优惠卷列表数据
     * @return   array 优惠卷列表数据
     */
    public function couponDetail()
    {
        $couponData = getCoupon();
        return json($couponData);
    }
}
