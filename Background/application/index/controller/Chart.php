<?php
namespace app\index\controller;

use app\index\model\Goods;
use app\index\model\Order;
use app\index\model\Order_detail;
use app\index\model\User;
use \think\Controller;
use \think\Request;

class Chart extends Controller
{
    /**
     * 用户统计页面
     * @return 页面
     */
    public function userchart()
    {
        $user = new User;
        $num  = $user->field('uid')->count();
        $this->assign('num', $num);
        return $this->fetch();
    }

    /**
     * 获取用户统计数据
     * @return ary     返回数据
     */
    public function getUserData(Request $request)
    {
        $select = intval($request->param('select'));
        // 选择7天
        if ($select == 7) {
            // 今天0点时间戳
            $endTime   = strtotime(date("Y-m-d 23:59:59"));
            $startTime = $endTime - 7 * 86400 + 1;
            $xData     = array();
            for ($i = 0; $i < 7; $i++) {
                $xData[] = date('Y-m-d', $startTime + $i * 86400); //每隔一天赋值给数组
            }
        }
        // 选择30天
        if ($select == 30) {
            // 今天0点时间戳
            $endTime   = strtotime(date("Y-m-d 23:59:59"));
            $startTime = $endTime - 30 * 86400 + 1;
            $xData     = array();
            for ($i = 0; $i < 30; $i++) {
                $xData[] = date('Y-m-d', $startTime + $i * 86400); //每隔一天赋值给数组
            }
        }
        // 选择6个月内
        if ($select == 666) {
            $startTime = strtotime($request->param('startTime'));
            $endTime   = strtotime($request->param('endTime'));
            $xData     = array();
            // x轴数据
            for ($i = $startTime; $i <= $endTime; $i += 86400) {
                $xData[] = date('Y-m-d', $i); //每隔1天赋值给数组
            }
            // dump($xData);die;
            $endTime = $endTime + 86400; //加一天
        }
        // 查询数据 订单数据
        $order = new User;
        $data  = $order->field('uid,created_at')->where('created_at', 'between', [$startTime, $endTime])->select();
        // 非空判断
        if (!empty($data)) {
            // 数组里判断时间
            foreach ($xData as $k => $v) {
                $isAll[$k] = 0;
                $begin     = strtotime($v);
                $end       = strtotime($v) + 86400;
                foreach ($data as $ke => $va) {
                    if ($va['created_at'] >= $begin && $va['created_at'] < $end) {
                        $isAll[$k] += 1;
                    }
                }
            }
        } else {
            // 当所有数据为空，显示空数据
            // 构造数据 y轴商品名称 x轴三个数据
            $isAll = [];
            foreach ($xData as $k => $v) {
                $isAll[$k] = 0;
            }
        }
        // y轴数据
        $data = array(
            'data' => $isAll,
            'name' => '新用户',
        );
        $seriesData = array();
        array_push($seriesData, $data);
        $title      = '新用户统计';
        $courseData = array(
            'seriesData' => $seriesData,
            'title'      => $title,
            'xData'      => $xData,
        );
        return json($courseData);
    }

    /**
     * 商品统计页面
     * @return 页面
     */
    public function goodschart()
    {
        return $this->fetch();
    }

    /**
     * 获取商品销量图表数据
     * @return ary          对应的数据
     */
    public function getGoodsStat(Request $request)
    {
        $select = intval($request->param('select'));
        // 选择7天
        if ($select == 7) {
            // 今天0点时间戳
            $endTime   = strtotime(date("Y-m-d 23:59:59"));
            $startTime = $endTime - 7 * 86400 + 1;
        }
        // 选择30天
        if ($select == 30) {
            // 今天0点时间戳
            $endTime   = strtotime(date("Y-m-d 23:59:59"));
            $startTime = $endTime - 30 * 86400 + 1;
        }
        // 选择6个月内
        if ($select == 666) {
            $startTime = strtotime($request->param('startTime'));
            $endTime   = strtotime($request->param('endTime'));
            $endTime   = $endTime + 86400; //加一天
        }
        // 查询数据 商品数据
        $goods     = new Goods;
        $goodsData = $goods->field('goods_id, goods_name')->where('status', '<>', 4)->select();
        // 查询数据 订单数据
        $order_detail = new Order_detail;
        // $data         = $order_detail->field('goods_id, created_at, quantity')->where('created_at', 'between', [$startTime, $endTime])->select();
        //给要关联的表取别名,并让两个值关联
        $data = $order_detail->alias('a')->join('order w', 'a.order_sn = w.order_sn', 'left')->field('a.goods_id, a.quantity, w.status')->where('w.status', 4)->where('a.created_at', 'between', [$startTime, $endTime])->select();
        // 非空判断
        if (!empty($data) && !empty($goodsData)) {
            $xData = [];
            foreach ($goodsData as $k => $v) {
                $xData[]   = $v['goods_name'];
                $isAll[$k] = 0;
                foreach ($data as $ke => $va) {
                    if ($v['goods_id'] == $va['goods_id']) {
                        $isAll[$k] += $va['quantity'];
                    }
                }
            }
        } else {
            // 当所有数据为空，显示空数据
            // 构造数据 y轴商品名称 x轴三个数据
            $isAll = [];
            $xData = [];
            foreach ($goodsData as $k => $v) {
                $xData[]   = $v['goods_name'];
                $isAll[$k] = 0;
            }
        }
        // y轴数据
        $data = array(
            'data' => $isAll,
            'name' => '销量',
        );
        $seriesData = array();
        array_push($seriesData, $data);
        $title      = '销量统计';
        $courseData = array(
            'seriesData' => $seriesData,
            'title'      => $title,
            'xData'      => $xData,
        );
        return json($courseData);
    }

    /**
     * 订单统计页面
     * @return 页面
     */
    public function orderchart()
    {
        return $this->fetch();
    }

    /**
     * 获取订单图表数据
     * @return ary          对应的数据
     */
    public function getOrderStat(Request $request)
    {
        $select = intval($request->param('select'));
        // 选择7天
        if ($select == 7) {
            // 今天0点时间戳
            $endTime   = strtotime(date("Y-m-d 23:59:59"));
            $startTime = $endTime - 7 * 86400 + 1;
            $xData     = array();
            for ($i = 0; $i < 7; $i++) {
                $xData[] = date('Y-m-d', $startTime + $i * 86400); //每隔一天赋值给数组
            }
        }
        // 选择30天
        if ($select == 30) {
            // 今天0点时间戳
            $endTime   = strtotime(date("Y-m-d 23:59:59"));
            $startTime = $endTime - 30 * 86400 + 1;
            $xData     = array();
            for ($i = 0; $i < 30; $i++) {
                $xData[] = date('Y-m-d', $startTime + $i * 86400); //每隔一天赋值给数组
            }
        }
        // 选择6个月内
        if ($select == 666) {
            $startTime = strtotime($request->param('startTime'));
            $endTime   = strtotime($request->param('endTime'));
            $xData     = array();
            // x轴数据
            for ($i = $startTime; $i <= $endTime; $i += 86400) {
                $xData[] = date('Y-m-d', $i); //每隔1天赋值给数组
            }
            // dump($xData);die;
            $endTime = $endTime + 86400; //加一天
        }
        // 查询数据 订单数据
        $order = new Order;
        $data  = $order->field('order_id, finish_at, status')->where('finish_at', 'between', [$startTime, $endTime])->select();
        // 非空判断
        if (!empty($data)) {
            // 数组里判断时间
            foreach ($xData as $k => $v) {
                $isNot[$k] = 0;
                $isAll[$k] = 0;
                $begin     = strtotime($v);
                $end       = strtotime($v) + 86400;
                foreach ($data as $ke => $va) {
                    if ($va['finish_at'] >= $begin && $va['finish_at'] < $end) {
                        if ($va['status'] == 3) {
                            $isAll[$k] += 1;
                        } else {
                            $isNot[$k] += 1;
                        }
                    }
                }
            }
        } else {
            // 当所有数据为空，显示空数据
            // 构造数据 y轴商品名称 x轴三个数据
            $isNot = [];
            $isAll = [];
            foreach ($xData as $k => $v) {
                $isNot[$k] = 0;
                $isAll[$k] = 0;
            }
        }
        // y轴数据
        $data = array(
            'data' => $isAll,
            'name' => '已成交',
        );
        $data2 = array(
            'data' => $isNot,
            'name' => '未成交',
        );
        $seriesData = array();
        array_push($seriesData, $data);
        array_push($seriesData, $data2);
        $title      = '订单统计';
        $courseData = array(
            'seriesData' => $seriesData,
            'title'      => $title,
            'xData'      => $xData,
        );
        return json($courseData);
    }

    /**
     * 金额统计页面
     * @return 页面
     */
    public function incomechart()
    {
        return $this->fetch();
    }

    /**
     * 获取订单金额数据
     * @return ary          对应的数据
     */
    public function getIncomeStat(Request $request)
    {
        $select = intval($request->param('select'));
        // 选择7天
        if ($select == 7) {
            // 今天0点时间戳
            $endTime   = strtotime(date("Y-m-d 23:59:59"));
            $startTime = $endTime - 7 * 86400 + 1;
            $xData     = array();
            for ($i = 0; $i < 7; $i++) {
                $xData[] = date('Y-m-d', $startTime + $i * 86400); //每隔一天赋值给数组
            }
        }
        // 选择30天
        if ($select == 30) {
            // 今天0点时间戳
            $endTime   = strtotime(date("Y-m-d 23:59:59"));
            $startTime = $endTime - 30 * 86400 + 1;
            $xData     = array();
            for ($i = 0; $i < 30; $i++) {
                $xData[] = date('Y-m-d', $startTime + $i * 86400); //每隔一天赋值给数组
            }
        }
        // 选择6个月内
        if ($select == 666) {
            $startTime = strtotime($request->param('startTime'));
            $endTime   = strtotime($request->param('endTime'));
            $xData     = array();
            // x轴数据
            for ($i = $startTime; $i <= $endTime; $i += 86400) {
                $xData[] = date('Y-m-d', $i); //每隔1天赋值给数组
            }
            // dump($xData);die;
            $endTime = $endTime + 86400; //加一天
        }
        // 查询数据 订单数据
        $order_detail = new Order_detail;
        $data  = $order_detail->field('idx, created_at, quantity ,fee')->where('created_at', 'between', [$startTime, $endTime])->select();
        // 非空判断
        if (!empty($data)) {
            // 数组里判断时间
            foreach ($xData as $k => $v) {
                $isAll[$k] = 0;
                $begin     = strtotime($v);
                $end       = strtotime($v) + 86400;
                foreach ($data as $ke => $va) {
                    if ($va['created_at'] >= $begin && $va['created_at'] < $end) {
                        $isAll[$k] += $va['quantity']*$va['fee'];
                    }
                }
            }
        } else {
            // 当所有数据为空，显示空数据
            // 构造数据 y轴商品名称 x轴三个数据
            $isAll = [];
            foreach ($xData as $k => $v) {
                $isAll[$k] = 0;
            }
        }
        // y轴数据
        $data = array(
            'data' => $isAll,
            'name' => '已完成',
        );
        $seriesData = array();
        array_push($seriesData, $data);
        $title      = '收入统计';
        $courseData = array(
            'seriesData' => $seriesData,
            'title'      => $title,
            'xData'      => $xData,
        );
        return json($courseData);
    }    




}
