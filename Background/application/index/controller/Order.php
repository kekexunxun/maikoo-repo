<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Cache;
use \think\Db;
use \think\Session;
use \think\File;
use app\index\model\Goods;
use app\index\model\Order as OrderDb;
use app\index\model\Order_detail;

class Order extends Controller
{
	/**
	 * 订单页面
	 * @return 订单数据
	 */
	public function orderlist(){
		return $this->fetch();
	}

	public function getOrderData(Request $request){
        //给要关联的表取别名,并关联
        // $data = $order->alias('a') -> join('order_detail w', 'a.order_sn = w.order_sn', 'left') -> join('merchant n', 'a.mch_id = n.mch_id', 'left') -> field('a.order_id, a.order_sn, a.total_fee, a.phone ,a.address, a.message, a.created_at, a.cancel_at, a.finish_at, a.status  ')->where('w.status', 4) ->select();	
        $num = $request->param('start');
        // dump($num);die;
		// 调用公共函数
		// $orderData = getOrder(0,1);
		// dump($orderData);die;
        // $start = $request->param('start');
        // $length = intval($request->param('length'));
        // $draw = intval($request->param('draw'));
        	
		$ary = array(
						'order_id'=>'1',
						'order_sn'=>'100',
						'username'=>'test',
						'total_fee'=>'100',
						'phone'=>'110',
						'address'=>'china',
						'message'=>'test666',
						'pay_at'=>'1',
						'created_at'=>'1',
						'finish_at'=>'1',
						'cancel_at'=>'1',
						'status'=>'1',
						'goods_name'=>'test777',
						'quantity'=>'99',
						'fee'=>'100',
						'mch_name'=>'market',
						// 'fee'=>'100',
					);
		for ($i= 0; $i < 12; $i++) { 
			$data[] = $ary;
		}
		$pageData = array(
						'draw' => 1,
						'recordsTotal' =>12,
						'recordsFiltered' =>12,
						'data' => $data

		);
        return json($pageData);
        // return objReturn(0,'success');
	}	

	// 获取订单状态
	public function getOrderStat(Request $request){
		$num = intval($request->param('num'));
		$ary = array(
						'order_id'=>'2',
						'order_sn'=>'200',
						'username'=>'test',
						'total_fee'=>'100',
						'phone'=>'110',
						'address'=>'china',
						'message'=>'test666',
						'pay_at'=>'1',
						'created_at'=>'1',
						'finish_at'=>'1',
						'cancel_at'=>'1',
						'status'=>'1',
						'goods_name'=>'test777',
						'quantity'=>'99',
						'fee'=>'100',
						'mch_name'=>'market',
						// 'fee'=>'100',
					);	
		for ($i= 0; $i < 12; $i++) { 
			$data[] = $ary;
		}
        return objReturn(0, 'success' ,$data);	
	}
	
	public function downloadExcel(Request $request){
	    $startTime = strtotime($request->param('startTime'));
     	$endTime   = strtotime($request->param('endTime'));
     	
		$excel = new Excel;
		$res = $excel ->test();
		return objReturn(0, '下载中>>>' ,$res);
	}

	


}