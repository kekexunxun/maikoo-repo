<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;

class Order extends Controller
{
    /**
     * 用户下单
     *
     * @return void
     */
    public function makeOrder()
    {
        // 请求方法校验
        if (request()->isGet()) return objReturn(400, 'Invaild Method');
        // 下单时间校验

        $timeStamp = request()->param('timestamp');
        if (time() - $timeStamp > 10) return objReturn(400, 'Overtime');
        // 参数处理
        $orderData['coupon_id'] = intval(request()->param('couponId'));
        $orderData['uid'] = intval(request()->param('uid'));
        $orderData['total_fee'] = request()->param('totalFee');
        $orderData['coupon_fee'] = request()->param('couponFee');
        $orderData['logi_fee'] = request()->param('logiFee');
        $orderData['phone'] = request()->param('phone');
        $orderData['address'] = htmlspecialchars_decode(request()->param('address'));
        $orderData['message'] = htmlspecialchars_decode(request()->param('message'));
        $orderData['username'] = htmlspecialchars_decode(request()->param('userName'));
        $orderData['order_sn'] = generateSn(2, substr($orderData['phone'], -3));
        $orderData['mch_id'] = 1;
        $orderData['delivery'] = htmlspecialchars_decode(request()->param('delivery'));
        $orderData['created_at'] = time();
        $goodsDetail = request()->param('detail/a');
        // 开启事务
        Db::startTrans();
        try {
            $orderId = Db::name('order')->insertGetId($orderData);
            foreach ($goodsDetail as &$info) {
                $info['order_id'] = $orderId;
                $info['created_at'] = time();
            }
            // dump($orderId);dump($goodsDetail);die;
            $insert = Db::name('order_detail')->insertAll($goodsDetail);
            // 提交事务
            if (!$orderId || !$insert) {
                throw new \Exception("Data Not Insert");
            }
            Db::commit();
        } catch (\Exception $e) {
        // 回滚事务
            Db::rollback();
        }
        $data['order_sn'] = $orderData['order_sn'];
        if ($orderId && $insert) return objReturn(0, 'success', $data);
        return objReturn(400, 'failed');
    }

    /**
     * 获取用户订单信息
     * 1未付款 2待发货 3已发货 4待评价 5已完成 6已取消
     *
     * @return void
     */
    public function getOrderList()
    {
        // 请求方法校验
        if (request()->isGet()) return objReturn(400, 'Invaild Method');
        $uid = intval(request()->param('uid'));
        if (empty($uid)) return objReturn(401, 'Invaild Param');
        $state = intval(request()->param('state'));
        $pageNum = intval(request()->param('pageNum'));
        if (empty($uid)) return objReturn(401, 'failed');

        if ($state == 0) $status = [1, 2, 3, 4, 5, 6];
        if ($state == 1) $status = [1];
        if ($state == 2) $status = [2, 3];
        if ($state == 3) $status = [4];

        $orderList = getOrder($status, $pageNum, $uid);
        if (!$orderList) return objReturn(0, 'nothing');
        return objReturn(0, 'success', $orderList);
    }

    /**
     * 通过订单编号 orders 查找订单详情
     *
     * @return void
     */
    public function getOrderInfo()
    {
        // 请求方法校验
        if (request()->isGet()) return objReturn(400, 'Invaild Method');
        $uid = intval(request()->param('uid'));
        if (empty($uid)) return objReturn(401, 'Invaild Param');
        $orderSn = request()->param('ordersn');
        $orderInfo = getOrder(0, null, null, null, $orderSn);
        if (!$orderInfo) return objReturn(400, 'failed');
        // $orderInfo = $orderInfo[0];
        return objReturn(0, 'success', $orderInfo[0]);
    }

    /**
     * 用户取消订单
     */
    public function cancelOrder()
    {
        // 请求方法校验
        if (request()->isGet()) return objReturn(400, 'Invaild Method');
        $uid = intval(request()->param('uid'));
        $orderSn = request()->param('ordersn');
        if (empty($uid)) return objReturn(401, 'failed');
        $update = Db::name('order')->where('order_sn', $orderSn)->update(['status' => 6, 'cancel_at' => time()]);
        if ($update) return objReturn(0, 'success');
        return objReturn(400, 'failed');
    }

    /**
     * 用户提交订单评价
     *
     * @return void
     */
    public function submitRate()
    {
        if (request()->isGet()) return objReturn(400, 'Invaild Method');
        $uid = intval(request()->param('uid'));
        $mchId = intval(request()->param('mchid'));
        $ordersn = request()->param('ordersn');
        $shopRate = request()->param('shoprate/a'); // 店铺评分 分别对应了 描述 物流 服务
        $goodsComment = request()->param('goodscomment/a');
        // 简单数据处理
        // 构造商品评价
        $goodsCom = [];
        $mchCom = [];
        foreach ($goodsComment as $k => $v) {
            $temp = [];
            $temp['order_sn'] = $ordersn;
            $temp['goods_id'] = $v['goods_id'];
            $temp['comment'] = empty($v['comment']) ? htmlspecialchars($v['comment']) : '系统默认好评';
            $temp['satisfaction'] = $v['satisfy'];  // 用户满意度 1 好评 2 中评 3 差评 
            $temp['created_at'] = time();
            $temp['created_by'] = $uid;
            $goodsCom[] = $temp;
        }
        // 构造店铺评价
        $mchCom['mch_id'] = $mchId ? $mchId : 0;
        $mchCom['describ_rate'] = $shopRate[0];
        $mchCom['logi_rate'] = $shopRate[1];
        $mchCom['service_rate'] = $shopRate[2];
        $mchCom['created_at'] = time();
        $mchCom['created_by'] = $uid;
        // 启动事务
        Db::startTrans();
        try {
            $goodsComInsert = Db::name('goods_comment')->insertAll($goodsCom);
            $mchComInsert = Db::name('merchant_comment')->insert($mchCom);
            $updateOrder = Db::name('order')->where('order_sn', $ordersn)->update(['status' => 5, 'finish_at' => time()]);
            // 提交事务
            Db::commit();
            if (!$goodsComInsert || !$mchComInsert || !$updateOrder) throw new \Exception('Insert Failed');
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
        if (!$goodsComInsert || !$mchComInsert || !$updateOrder) return objReturn(400, 'Insert Failed');
        return objReturn(0, 'success');
    }
}