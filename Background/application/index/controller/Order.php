<?php
namespace app\index\controller;

use app\index\model\Order as OrderDb;
use \think\Controller;
use \think\Request;
use \think\Session;

class Order extends Controller
{
    /**
     * 订单页面
     * @return 订单数据
     */
    public function orderlist()
    {
        return $this->fetch();
    }

    public function getOrdData(Request $request)
    {
        //给要关联的表取别名,并关联
        // $data = $order->alias('a') -> join('order_detail w', 'a.order_sn = w.order_sn', 'left') -> join('merchant n', 'a.mch_id = n.mch_id', 'left') -> field('a.order_id, a.order_sn, a.total_fee, a.phone ,a.address, a.message, a.created_at, a.cancel_at, a.finish_at, a.status  ')->where('w.status', 4) ->select();

        // dump($num);die;
        // 调用公共函数
        // $orderData = getOrder(0,1);
        // dump($orderData);die;
        // $start = $request->param('start');
        // $length = intval($request->param('length'));
        // $draw = intval($request->param('draw'));

        $num = $request->param('start');
        $ary = array(
            'order_id' => '1',
            'order_sn' => '100',
            'username' => 'test',
            'total_fee' => '100',
            'phone' => '110',
            'address' => 'china',
            'message' => 'test666',
            'pay_at' => '1',
            'created_at' => '1',
            'finish_at' => '1',
            'cancel_at' => '1',
            'status' => '1',
            'goods_name' => 'test777',
            'quantity' => '99',
            'fee' => '100',
            'mch_name' => 'market',
            // 'fee'=>'100',
        );
        for ($i = 0; $i < 12; $i++) {
            $data[] = $ary;
        }
        $pageData = array(
            'draw' => 1,
            'recordsTotal' => 12,
            'recordsFiltered' => 12,
            'data' => $data,

        );
        return json($pageData);
        // return objReturn(0,'success');
    }

    /**
     * 获取所有的订单
     * @return ary          返回值
     */
    public function getOrderData(Request $request)
    {
        // 查询mch_id的值
        $mchId = Session::get('mch_id');
        if (!$mchId) {
            $mchId = null;
        }
        // 调用公共函数
        $orderData = getOrder(0, null, null, $mchId);
        if ($orderData) {
            return objReturn(0, 'success', $orderData);
        } else {
            return objReturn(400, 'failed');
        }
    }

    /**
     * 订单详情
     * @return ary 订单详情数组
     */
    public function orderdetail()
    {
        $orderId = intval(request()->param('order_id'));
        $order = new OrderDb;
        $orderDetail = $order->alias('o')->join('order_detail d', 'o.order_id = d.order_id', 'LEFT')->join('mk_merchant m', 'o.mch_id = m.mch_id', 'LEFT')->join('goods g', 'd.goods_id = g.goods_id', 'LEFT')->field('o.order_id, d.quantity, d.fee, d.goods_id, g.goods_name, g.goods_img, m.mch_name')->where('o.order_id', $orderId)->select();
        $orderDetail = collection($orderDetail)->toArray();
        $this->assign('orderDetail', $orderDetail);
        return $this->fetch();
    }

    /**
     * 获取订单状态
     * @param  Request $request 参数
     * @return ary              返回值
     */
    public function getOrderStat(Request $request)
    {
        $status = intval($request->param('num'));
        // 查询mch_id的值
        $mchId = Session::get('mch_id');
        if (!$mchId) {
            $mchId = null;
        }
        // 调用公共函数
        $orderData = getOrder($status, null, null, $mchId);
        if (!$orderData) {
            $orderData = 401;
        }
        return objReturn(0, 'success', $orderData);
    }

    /**
     * 下载订单的excel信息
     * @param  Request $request 参数
     * @return ary              返回值
     */
    public function downloadExcel(Request $request)
    {
        $startTime = strtotime($request->param('startTime'));
        $endTime = strtotime($request->param('endTime'));

        $excel = new Excel;
        $res = $excel->orderExcel($startTime, $endTime);
        return objReturn(0, '表格生成中...', $res);
    }

    /**
     * 发货商品
     * @return ary           发货结果
     */
    public function expressTo(Request $request)
    {
        $update['order_id'] = $request->param('order_id');
        $update['logi_name'] = htmlspecialchars($request->param('express_co'));
        $update['logi_code'] = $request->param('express_num');
        $update['delivery_at'] = time();
        $update['status'] = 3;
        // 调用公共函数保存，参数true为更新
        $new = saveData('order', $update, true);
        if ($new) {
            return objReturn(0, '发货成功！');
        } else {
            return objReturn(400, '发货失败！');
        }
    }
}
