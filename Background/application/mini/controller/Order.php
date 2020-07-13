<?php

/**
 * 吸铁石美术小程序 订单有关方法
 * @author Locked
 * createtime 2018-05-03
 */

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use app\index\model\Order as od;
use app\index\model\Classes;


class Order extends Controller
{

    /**
     * 用户课程续费下单
     *
     * @return void
     */
    public function createOrder()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $uid = intval(request()->param('uid'));
        $classId = intval(request()->param('classid'));
        if (empty($uid) || empty($classId)) return objReturn(400, 'Invaild Param');
        
        // 判断当前课程是否有效
        $classes = new Classes;
        $courseStatus = $classes->alias('c')->join('art_course cse', 'c.course_id = cse.course_id', 'LEFT')->where('c.class_id', $classId)->field('cse.status as course_status, c.status as class_status, cse.course_id, cse.course_price')->find();
        if ($courseStatus['course_status'] != 1) return objReturn(403, 'Course Is Invaild');

        $orderInfo['uid'] = $uid;
        $orderInfo['course_id'] = $courseStatus['course_id'];
        $orderInfo['class_id'] = $classId;
        $orderInfo['order_sn'] = $this->getOrderSn();
        // $orderInfo['fee'] = $courseStatus['course_price'];
        $orderInfo['fee'] = 0.01;
        $orderInfo['created_at'] = time();

        $insert = Db::name('order')->insert($orderInfo);
        if ($insert) return objReturn(0, 'success', $orderInfo['order_sn']);
        return objReturn(400, 'failed');
    }

    /**
     * 产生订单号
     * @return string 订单号 生成规则为 20180323 + timestamp后二位 + microtime前三位(小数点后)
     */
    public function getOrderSn()
    {
        $orderSn = "";
        $micorTime = microtime();
        $micorTime = explode('.', $micorTime);
        $micorTime = substr($micorTime[1], 0, 3);
        $orderSn = date('Ymd', time()) . substr(strval(time()), -5, -1) . $micorTime;
        return $orderSn;
    }

    /**
     * 获取用户订单
     *
     * @param Request $request
     * @return void
     */
    public function getUserOrder(Request $request)
    {
        $uid = intval($request->param('uid'));
        $pageNum = intval($request->param('pageNum'));
        if (!isset($uid)) {
            return objReturn(400, 'Invaild Param');
        }
        $orderList = getOrderList($uid, $pageNum);
        $res['list'] = $orderList;
        $res['isHaveMore'] = empty($orderList) || count($orderList) < 10 ? false : true;
        return objReturn(0, 'success', $res);
    }

    /**
     * 取消订单
     *
     * @return boolean
     */
    public function cancelOrder(Request $request)
    {
        $orderSn = $request->param('ordersn');
        if (empty($orderSn)) {
            return objReturn(400, 'Invaild Param');
        }
        $order = new od;
        $update = $order->where('order_sn', $orderSn)->update(['status' => 2, 'cancel_at' => time()]);
        if ($update) {
            $prepayCache = Cache::get('prepayCache');
            foreach ($prepayCache as $k => $v) {
                if ($v['orderid'] == $orderSn) {
                    array_splice($prepayCache, $k, 1);
                }
            }
            Cache::set('prepayCache', $prepayCache, 0);
            return objReturn(0, 'success');
        } else {
            return objReturn(400, 'failed');
        }
    }

    public function test()
    {
        $prepayCache = Cache::get('prepayCache');
        // foreach ($prepayCache as $k => $v) {
        //     if($v['ordersn'] == '201808019832537'){
        //         $prepayCache[$k]['ordersn'] = $v['orderid'];
        //     }
        // }
        // Cache::set('prepayCache', $prepayCache);
        dump($prepayCache);
        // Cache::rm('prepayCache');
    }
}