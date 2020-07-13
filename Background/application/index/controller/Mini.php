<?php

/**
 * 打印店小程序后台处理相关
 * @author Locked
 * createtime 2018-03-06
 */

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\Session;
use think\File;

// use WxPay\WxPayApi;

use app\index\model\Printlist;
use app\index\model\User_info;
use app\index\model\Img_path;
use app\index\model\User_coupon;
use app\index\model\Coupon;
use app\index\model\Track_co;
use app\index\model\Sizelist;

class Mini extends Controller{
    
    const APPID = "wx06a3684282ae583e";
    const APPSECRET = "ec46f43c22e8e8efc5311fd23f12c1ec";
    const DS = DIRECTORY_SEPARATOR;
    // 微信支付相关
    const REPORT_LEVENL = 0;
    const KEY = "ls2805aeu2w0epzeawisc21f9wolmovo";
    const CURL_PROXY_HOST = "0.0.0.0";
    const CURLOPT_PROXYPORT = 0;


    /**
     * 获取用户信息
     * @param array userInfo
     * @param string openid
     * @return json 是否插入成功成功
     */
    public function setUserInfo(){

    	$request = Request::instance();
        $openid = $request -> param('openid');

    	// 有一个Openid 的缓存array，如果已经将该用户数据插入过，在缓存中就会体现
    	// 判断缓存库中是否有该openid
    	$openidArr = array();
    	$openidArr = Cache::get('userOpenidArr');
        // dump($openidArr); die;
    	if ($openidArr) {
    		foreach ($openidArr as $v) {
    			if ($v == $openid) {
    				$res['code'] = "201";
    				return json_encode($res);
                    // die;
    			}
    		}
    	}
    	// 获取用户信息并入库
        $user_info = new User_info;
        $userInfo = $request -> param('userInfo/a');
        $userInfo['openid'] = $openid;
        $insert = $user_info -> insert($userInfo);
        if ($insert) {
        	$openidArr[]= $openid;
        	Cache::set('userOpenidArr', $openidArr, 0);
            $res['code'] = "200";
        }else{
            $res['code'] = "300";
        }
        return json_encode($res);
    }

    /**
     * 获取用户openID
     * @param string code
     * @return json 用户openid
     */
    public function getUserOpenid(){
        $request = Request::instance();
        $code = $request -> param('code');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".self::APPID."&secret=".self::APPSECRET."&js_code=".$code."&grant_type=authorization_code";
        $info = file_get_contents($url);
        $info = json_decode($info);
        $info =  get_object_vars($info);
        $openidInfo['openid'] = $info['openid'];
        // $isUserExist = Db::name('user_info') -> where('openid', $openidInfo['openid']) -> field('idx') -> find();
        Db::name('user_count') -> insert(['openid' => $openidInfo['openid'], 'create_time' => date('Y-m-d H:i:s', time())]);
        // openid入库 可节约多次请求
        return json_encode($openidInfo);
    }

    /**
     * 图片上传
     * @return [type] [description]
     */
    public function imageUpload(){
        // 获取表单上传文件
        $request = Request::instance();
        $file = $request -> file('file');
        // 当前打印类别判断 single mulit ident
        // $state = $request -> pram('state');
        // $siteroot = "https://print.up.maikoo.cn";
        // $fileName = md5($file['info']['tmp_name']);
        $targetDir = "." . DS . 'public' . DS . 'uploads';
        $save = $file -> move($targetDir);
        if ($save) {
            $res['code'] = "200";
            $res['fileName'] = $save -> getSaveName();
            $res['message'] = "success";
        }else{
            $res['code'] = "400";
            $res['message'] = "NETWORK ERROR";
        }
        return json_encode($res);
    }


    /**
     * 获取店家的QRCode
     * 用户可添加店家进行特殊打印文件的发送
     * @return json $res 用户的二维码链接地址
     */
    public function getAdminWxQrCode(){
        $qrCode = Cache::get('qrCode');
        // $qrCode = "";
        if (!$qrCode) {
            $qrCode = Db::name('admin') -> where('idx', 1) -> find();
            $qrCode = $qrCode['qrcode'];
            // $res['src'] = $qrCode['qrcode'];
            Cache::set('qrCode', $qrCode, 86400);
        }
        $res['src'] = $qrCode;
        $res['code'] = "200";
        $res['message'] = "success";
        return json_encode($res);
    }

    /**
     * 用户提交反馈
     * @param string telNumber,complain,imgPath,openid
     * @return json 提交状态
     */
    public function submitFeedback(){
        $request = Request::instance();
        $feedback['telNumber'] = intval($request -> param('telNumber'));
        $feedback['complain'] = htmlspecialchars($request -> param('complain'));
        $feedback['img_path'] = $request -> param('imgPath');
        $feedback['user_openid'] = $request -> param('openid');
        $feedback['createtime'] = time();
        $insert = Db::name('complain') -> insert($feedback);
        if ($insert) {
            $res['code'] = "200";
            $res['message'] = "success";
        }else{
            $res['code'] = "400";
            $res['message'] = "NETWORK ERROR";
        }

        return json_encode($res);
    }

    /**
     * 用户反馈图片上传
     * @return json 用户上传结果
     */
    public function complainImageUpload(){
        $request = Request::instance();
        $file = $request -> file('file');
        
        $targetDir = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'complain';
        $save = $file -> move($targetDir);
        
        if ($save) {
            $res['code'] = "200";
            $res['path'] = $save -> getSaveName();
            $res['message'] = "success";
        }else{
            $res['code'] = "400";
            $res['message'] = "NETWORK ERROR";
        }
        return json_encode($res);
    }

    /**
     * 获取系统卡券所有卡券，并判定该用户是否拥有此卡券
     * @param userOpenid 用户openid
     * @return json coupon 系统卡券信息
     */
    public function getSysCoupon(){
        $request = Request::instance();
        $userOpenid = $request -> param('openid');
        
        $coupon = $this -> getCoupon();
        if ($coupon) {
            $res['code'] = "200";
            // 获取用户卡券
            $user_coupon = new User_coupon;
            $userCoupon = $user_coupon -> where('user_openid', $userOpenid) -> select();
            foreach ($coupon as $k => $v) {
                foreach ($userCoupon as $ke => $va) {
                    if ($va['coupon_id'] == $v['idx']) {
                        // 该账号中存在此卡券
                        $v['is_have'] = 1;
                        // 判断是否过期
                        break 1;
                    }
                }
                // 是否可领取（卡券开始时间判定）
                if (time() < intval($v['start_time'])) {
                    $v['can_have'] = 0;
                }else{
                    $v['can_have'] = 1;
                }
                $v['start_time_convert'] = $v['start_time'];
                $v['start_time'] = date('Y-m-d H:i', $v['start_time']);
                // 使用条件判断
                if($v['condition'] == 0){
                    $v['condition'] = "无限制";
                }else{
                    $v['condition'] = "消费满".$v['condition']."元可使用";
                }
                // 过期判断
                if (time() > intval($v['expire_time'])) {
                    $v['is_expire'] = 1;
                }else{
                    $v['is_expire'] = 0;
                }
                // 过期时间转换
                $v['expire_time_convert'] = $v['expire_time'];
                $v['expire_time'] = date('Y-m-d H:i', $v['expire_time']);

                // 计算有效期
                $diffDate = floor(($v['expire_time_convert'] - $v['start_time_convert']) / 86400);
                $diffHour = floor(($v['expire_time_convert'] - $v['start_time_convert']) % 86400 / 3600);
                // $diffMinute = floor(($v['expire_time_convert'] - $v['start_time_convert']) % 86400 / 60);
                $v['valid_time'] = $diffDate . '天' . $diffHour . '小时';

            }
            // 判断完成后对整个卡券进行排序
            $temp = array();
            $count = sizeof($coupon) - 1;
            for ($i=$count; $i >= 0; $i--) {
                if($coupon[$i]['is_expire'] == 1){
                    $temp []= $coupon[$i];
                    array_splice($coupon, $i, 1);
                }
            }
            // 将过期的coupon追加到返回的额 $coupon里
            foreach ($temp as $key => $value) {
                $coupon []= $value;
            }
            $res['coupon'] = $coupon;
            $res['message'] = "success";
        }else{
            $res['code'] = "201";
            $res['message'] = "NO COUPON";
        }

        return json_encode($res);
    }

    /**
     * 新增用户卡券
     * @param string openid 用户openid
     * @param string idx 卡券id
     * @return json res
     */
    public function addUserCoupon(){
        $user_coupon = new User_coupon;
        $request = Request::instance();
        $userCoupon['user_openid'] = $request -> param('openid');
        $userCoupon['coupon_id'] = $request -> param('idx');
        $userCoupon['createtime'] = time();
        $userCoupon['expire_time'] = time() + $request -> param('expire_time');
        $insert = $user_coupon -> insert($userCoupon);
        if ($insert) {
            $res['code'] = "200";
            $res['message'] = "success";
        }else{
            $res['code'] = "400";
            $res['message'] = "NETWORK ERROR";
        }
        return json_encode($res);
    }


    /**
     * 获取快递列表
     * @return json res 订单列表
     */
    public function getLogiList(){
        $track_co = new Track_co;
        $logi = $track_co -> where('del_state', 0) -> select();
        if($logi){
            $res['code'] = "200";
            $res['message'] = "success";
            $res['logi'] = $logi;
        }else{
            if (sizeof($logi) == 0) {
                $res['code'] = "201";
                $res['message'] = "NO LOGI";
            }else if (!$logi) {
                $res['code'] = "400";
                $res['message'] = "NETWORK ERROR";
            }
        }
        return json_encode($res);
    }

    /**
     * 获取用户可用卡券
     * @return json userCoupon 用户卡券列表
     */
    public function getUserCoupon(){
        $request = Request::instance();
        $userOpenid = $request -> param('openid');
        $totalPrice = $request -> param('totalFee');
        // 获取用户卡券
        $user_coupon = new User_coupon;
        $userCoupon = $user_coupon -> where('user_openid', $userOpenid) -> where('is_used', 0) -> where('is_expire', 0) -> where('expire_time', '>', time()) -> order('expire_time asc') -> select();
        // 获取系统卡券
        $couponList = $this -> getCoupon();
        // 卡券判定
        if ($userCoupon) {
            foreach ($userCoupon as $k => $v) {
                // 信息匹配
                foreach ($couponList as $ke => $va) {
                    if ($va['idx'] == $v['coupon_id']) {
                        $v['name'] = $va['name'];
                        $v['condition'] = $va['condition'];
                        // 条件判定
                        if($v['condition'] <= $totalPrice){
                            $v['can_use'] = 1;
                        }else{
                            $v['can_use'] = 0;
                            $v['reason'] = '消费金额不足';
                        }
                        $v['price'] = number_format($va['price'], 2);
                        break 1;
                    }
                }
            }
            $res['code'] = "200";
            $res['message'] = "success";
            $res['coupon'] = $userCoupon;
        }else{
            if (sizeof($userCoupon) == 0) {
                $res['code'] = "201";
                $res['message'] = "无优惠券";
            }else if(!$userCoupon){
                $res['code'] = "400";
                $res['message'] = "NETWORK ERROR";
            }
        }
        return json_encode($res);
    }

    /**
     * 获取卡券列表
     * 将卡券信息存入本地，当后台有卡券存入时，就清除本地缓存
     * @return array 卡券列表
     */
    public function getCoupon(){
        $couponList = Cache::get('coupon');
        if (!$couponList) {
            $coupon = new Coupon;
            $couponList = $coupon -> where('is_show', 1) -> order('createtime desc') -> select();
            Cache::set('coupon', $couponList, 30); 
        }
        return $couponList;
    }

    /**
     * 获取不同打印方式时的价格
     * @return json 价格列表
     */
    public function getSize(){
        $sizelist = new Sizelist;

        // 根据规格进行升序排列
        $sizeList = $sizelist -> where('del_state', 0) -> field('format_idx, size, price, type') -> order('format_idx asc') -> select();
        $format = Db::name('format') -> select();

        if($sizeList){
            $single = array();
            $mulit = array();
            $ident = array();
            foreach ($sizeList as $k => $v) {
                foreach ($format as $ke => $va) {
                    if ($va['idx'] == $v['format_idx']) {
                        $v['format'] = $va['format'];
                        break 1;
                    }
                }
                $v['price'] = number_format(floatval($v['price']), 2);
                if ($v['type'] == 'single') {
                    $single[]= $v;
                }elseif ($v['type'] == 'mulit') {
                    $mulit[]= $v;
                }else{
                    $ident[]= $v;
                }
            }
            $res['single'] = $single;
            $res['mulit'] = $mulit;
            $res['ident'] = $ident;
            $res['code'] = "200";
            $res['message'] = "success";
            // 去查找店铺的营业时间
            $shoptime = Cache::get('shoptime');
            if(!$shoptime){
                // 如果没有设置时间，默认为早07:30开门 晚18:30关门
                $shoptime = Db::name('shop_time') -> find();
                if(!$shoptime['open_time']){
                    $res['shoptime'] = "07:30 - 18:30";
                }else{
                    $res['shoptime'] = $shoptime['open_time'] . " - " . $shoptime['close_time'];
                }
            }else{
                $res['shoptime'] = $shoptime;
            }
        }else{
            $res['code'] = "400";
            $res['message'] = "NETWORK ERROR";
        }

        return json_encode($res);
    }

    /**
     * 获取用户订单列表
     * @param string openid 用户openid
     * @return json 用户订单列表
     */
    public function getUserPrintList(){
        // 获取数据
        $request = Request::instance();
        $userOpenid = $request -> param('openid');

        // 先判断是否有缓存
        $printList = Cache::get($userOpenid);
        if ($printList) {
            $res['code'] = "200";
            $res['message'] = "success";
            $res['print'] = $printList;
            return json_encode($res);
        }

        $printlist = new Printlist;
        // 获取卡券
        $coupon = $this -> getCoupon();
        
        // 根据state对订单进行筛选查找
        // 0 未付款 1 已付款不需发货 2已付款待发货 3已付款已发货 4 订单已完成 5订单已取消
        // 默认订单在已付款3天之后完成
        $updateArr = array();
        $printList = $printlist -> where('user_openid', $userOpenid) -> where('del_state', 0) -> field('idx, user_openid, order_id, format, quantity, type, money, state, img_path, createtime, coupon_id, unit_price, logi_time, isdelivery') -> order('createtime desc') -> select();
        // dump($printList); die;
        // 根据订单列表来进行筛选
        if ($printList) {
            $res['code'] = "200";
            $res['message'] = "success";
            foreach ($printList as $k => $v) {
                // 时效性判断
                // 已发货但用户未点击确认，那么七天之内自动收获成功
                if ($v['state'] == 3 && (time() > intval($v['logi_time'] + 7*86400))) {
                    $v['state'] = 4;
                    $updateArr[]= array('state' => 4, 'idx' => $v['idx'], 'finishtime' => time());
                }
                // 未付款订单15分钟后自动归为已取消订单
                if ($v['state'] == 0 && (time() - intval($v['createtime']) > 900)) {
                    $v['state'] = 5;
                    $updateArr[]= array('state' => 5, 'idx' => $v['idx'], 'finishtime' => time());
                }
                // 对图片展示做准备 当type为mulit时
                // 打印类别判定
                $siteroot = "https://print.up.maikoo.cn/public/uploads/";
                if ($v['type'] == "mulit") {
                    $imagePath = explode(',', $v['img_path']);
                    $v['showImagePath'] = $siteroot.$imagePath[0];
                    $v['type'] = '多张打印';
                    // 打印数量
                    $v['quantities'] = sizeof($imagePath) * $v['quantity'];
                }else if ($v['type'] == 'ident') {
                    $v['type'] = '证件照打印';
                    $v['path'] = $siteroot.$v['img_path'];
                }else if ($v['type'] == 'single') {
                    $v['type'] = '单张打印';
                    $v['path'] = $siteroot.$v['img_path'];
                }
                // 优惠券判定
                if ($v['coupon_id']) {
                    foreach ($coupon as $ke => $va) {
                        if ($va['idx'] == $v['coupon_id']) {
                            $v['coupon_name'] = $va['name'];
                            $v['coupon_price'] = $va['price'];
                            break 1;
                        }
                    }
                }
                // 价格调整
                $v['money'] = number_format($v['money'], 2);
                $v['unit_price'] = number_format($v['unit_price'], 2);
                // 订单状态判定 分类
                if($v['state'] == 0){
                    $v['state'] = '等待付款';
                    $v['expire_time'] = date('i分s秒', strval($v['createtime'] + 900 - time()));
                    $res['waitPay'][]= $v;
                }else if ($v['state'] == 1 || $v['state'] == 4) {
                    $v['state'] = '交易完成';
                    $res['alDone'][]= $v;
                }else if ($v['state'] == 2) {
                    $v['state'] = '等待配送';
                    $res['waitDeli'][]= $v;
                }else if ($v['state'] == 3) {
                    $v['state'] = '已经发货';
                    $res['alDeli'][]= $v;
                }else if ($v['state'] == 5) {
                    $v['state'] = '交易取消';
                    $res['alDone'][]= $v;
                }
            }
            // 如果有需要更新的数据就执行更新
            if ($updateArr) {
                foreach ($updateArr as $k => $v) {
                    $printlist -> update($updateArr[$k]);
                }
                if ($v['state'] == 5) {
                    // 调用微信统一下单接口去取消订单
                }
            }
            $res['print'] = $printList;
            Cache::set($userOpenid, $printList, 30);
        }else{
            $res['code'] = "400";
            $res['message'] = "NETWORK ERROR";
        }

        return json_encode($res);
    }

    /**
     * 用户取消订单
     * @param string orderid 订单号 
     * @param string openid 用户的openid
     * @return json res 是否取消成功
     */
    public function orderCancel(){
        $request = Request::instance();
        $userOpenid = $request -> param('openid');
        $orderId = $request -> param('orderid');

        $update = Db::name('printlist') -> where('user_openid', $userOpenid) -> where('order_id', $orderId) -> update(['state' => 6, 'del_state' => 1]);
        if($update){
            $res['code'] = "200";
            $res['message'] = "success";
            // 删除当前订单附带的文件
            $imgPath = $request -> param('imgsrc');
            $imgPathArr = explode(',', $imgPath);
            foreach ($imgPathArr as $k => $v) {
                @unlink('public'.DS.'uploads'.DS.$v);
            }
            // 订单状态更新成功后删除对应缓存
            // 删除订单缓存
            Cache::rm($userOpenid);
            // 删除当前订单的支付缓存
            Cache::rm($orderId);
        }else{
            $res['code'] = "400";
            $res['message'] = "NETWORK ERROR";
        }

        // 发送订单已经被取消的通知

        return json_encode($res);
    }

    /**
     * 设置用户订单
     * @return json 下单状态
     */
    public function setUserPrint(){
        $request = Request::instance();
        $userOpenid = $request -> param('openid');
        $printlist = new Printlist;
        // 首先判断用户是否有未完成订单 如果有 则必须先完成当前订单
        // $isHaveNoPay = $printlist -> where('user_openid', $userOpenid) -> where('state', 0) -> field('state') -> find();
        // if ($isHaveNoPay) {
        //     $res['code'] = "401";
        //     $res['message'] = "用户有未完成订单";
        //     return json_encode($res);
        // }
        $totalFee = $request -> param('totalFee');
        $state = $request -> param('state');
        $userPrint['user_openid'] = $userOpenid;
        $userOrderId = $request -> param('order_id');
        $userPrint['order_id'] = $userOrderId;
        $userPrint['money'] = $totalFee;
        $userPrint['quantity'] = $request -> param('count');
        $userPrint['track_co_no'] = $request -> param('logi_co');
        $userPrint['format'] = $request -> param('format');
        $userPrint['unit_price'] = $request -> param('unitPrice');
        $userPrint['type'] = $state;
        // 打印类型判定 
        // 如果是single、ident就把path放到path 如果是mulit就把dir放到path
        // if ($state != 'mulit') {
            // $userPrint['imgPath'] = $request -> param('dir');
        $userPrint['img_path'] = $request -> param('path');
        // }
        // 地址判定
        $userAddress = htmlspecialchars($request -> param('address'));
        if($userAddress){
            $userPrint['isdelivery'] = 1;
        }

        // 用户备注
        $userRemark = $request -> param('message') ? $request -> param('message') : '无';
        $userRemark = htmlspecialchars($userRemark);
        $userPrint['remark'] = $userRemark;

        // 支付状态判定
        // 如果支付成功则直接删除对应缓存(删不删都无所谓，已经设置了定时删除)
        $is_pay = $request -> param('is_pay');
        // 打印的图片共有张数
        $totalNum = $request -> param('totalNum'); 
            
        // 订单配送状态
        $logiState = $userAddress ? "需要配送" : "上门自取";
        // 订单状态
        if ($state == 'single') {
            $printState = '单张打印';
        }else if ($state == 'mulit') {
            $printState = '多张打印';
        }else if ($state == 'ident') {
            $printState = '证件照打印';
        }

        $formId = $request -> param('formid');

        // foreach ($formId as $k => $v) {
            // Db::name('formid') -> insert(['formid' => $formId, 'openid' => $userOpenid, 'createtime' => date('Y-m-d H:i:s', time())]);
        // }
        if ($is_pay) {
            $userPrint['pay_time'] = time();
            // Cache::rm($userOrderId);
            // 获取prepay_id用于模板消息发送
            $orderInfo = Cache::get($userOrderId);
            $prepay_id = $orderInfo['prepay_id'];

            // 构造模板消息进行订单发送
            // 对应模板消息名称为 - 下单成功通知 - 发送给下单客户
            $post_data = array(
                'touser'            =>      $userOpenid,
                'template_id'       =>      'f_kLDqFES5nZQ5W710UxXLUKQY04PeOTcZbu-4vpf3I',
                'page'              =>      '/pages/orderdetail/orderdetail?orderid='.$userOrderId,
                'form_id'           =>      $formId,
                'data'              =>      array(
                                                'keyword1'  =>  array('value' => $userOrderId),                         //订单编号
                                                'keyword2'  =>  array('value' => date('Y-m-d H:i:s', time())),          //下单时间
                                                'keyword3'  =>  array('value' => $totalFee),                            //订单金额
                                                'keyword4'  =>  array('value' => $printState.' 共'.$totalNum.'张'),     //订单内容
                                                'keyword5'  =>  array('value' => '支付成功'),                           //订单状态
                                                'keyword6'  =>  array('value' => $logiState),                           //配送方式
                                                'keyword7'  =>  array('value' => '13906051853'),                        //客服电话
                                                'keyword8'  =>  array('value' => '福建省厦门市湖里区江顺里237号之68'),           //商户地址
                                                'keyword9'  =>  array('value' => '订单备注：'.$userRemark)              //温馨提示
                                            )
            );
            
            $post_data = json_encode($post_data);
            // 执行模板消息发送
            $createData = $this -> sendTempletMessage($post_data);

            // 谁产生的 Formid或者Prepay_id，只能将模板消息发送给对应的人
            // 构造模板消息
            // 对应模板消息名称为 - 新订单通知 - 发送给商家
            
            // $post_data = array(
            //     'touser'            =>      'orC205D3giPeNOJWzueyJvvmFQJ4',     //商家openid
            //     'template_id'       =>      'tJxCwHdBBwkeA4TbtfjmDaZsLsTXNyIUIQCf53twtXE',
            //     // 'page'              =>      '/pages/orderspec/orderspec?orderid='.$orderId.'&way=2',
            //     'form_id'           =>      $prepay_id,
            //     'data'              =>      array(
            //                                     'keyword1'  =>  array('value' => date('Y-m-d H:i:s', time())),          //下单时间
            //                                     'keyword2'  =>  array('value' => $userOrderId),                         //订单号
            //                                     'keyword3'  =>  array('value' => $totalFee),                            //订单总价
            //                                     'keyword4'  =>  array('value' => $printState.' 共'.$totalNum.'张'),     //订单详情
            //                                     'keyword5'  =>  array('value' => '支付成功'),                           //订单状态
            //                                     'keyword6'  =>  array('value' => $userRemark)                           //订单备注
            //                                 )
            // );
            
            // $post_data = json_encode($post_data);
            // // 执行模板消息发送
            // $createData = $this -> sendTempletMessage($post_data);

        }else{
            // 订单未支付
            // 构造模板消息进行订单发送
            // 对应模板消息名称为 - 待支付提醒 - 发送给下单客户
            
            $post_data = array(
                'touser'            =>      $userOpenid,
                'template_id'       =>      'ulPBxNbqzdc86Z9EtVGyMk6WXYXLCw6RjVCUR1vOEls',
                'page'              =>      'pages/orderdetail/orderdetail?orderid='.$userOrderId,
                'form_id'           =>      $formId,
                'data'              =>      array(
                                                'keyword1'  =>  array('value' => $userOrderId),                         //订单号
                                                'keyword2'  =>  array('value' => date('Y-m-d H:i:s', time())),          //下单时间
                                                'keyword3'  =>  array('value' => $totalFee.'元'),                       //订单价格
                                                'keyword4'  =>  array('value' => '等待支付'),                           //订单状态
                                                'keyword5'  =>  array('value' => $printState.' 共'.$totalNum.'张'),     //商品名称
                                                'keyword6'  =>  array('value' => '有问题请拨打客服电话13906051853')     //温馨提示
                                            )
            );
            
            $post_data = json_encode($post_data);
            // 执行模板消息发送
            $createData = $this -> sendTempletMessage($post_data);
            
        }

        // 订单状态判定
        if ($userAddress && $is_pay) {
            $userPrint['state'] = 2;
        }else if($is_pay){
            $userPrint['state'] = 1;
        }else if(!$is_pay){
            $userPrint['state'] = 0;
        }
        
        $userPrint['address'] = $userAddress;
        $userPrint['phone'] = $request -> param('telNumber');
        $userPrint['name'] = $request -> param('userName');
        $couponId = $request -> param('coupon_id');

        $userPrint['format_idx'] = $request -> param('formatIdx');

        $userPrint['coupon_id'] = $couponId ? $couponId : 0;
        // 如果有卡券信息 需要去更新用户卡券表
        if ($couponId) {
            $couponIdx = $request -> param('coupon_idx');
            $user_coupon = new User_coupon;
            $user_coupon -> where('user_openid', $userOpenid) -> where('idx', $couponIdx) -> update(['is_used' => 1, 'use_time' => time()]);
            Cache::rm('coupon');
        }

        $userPrint['createtime'] = time();
        // 订单状态判定
        $insert = Db::name('printlist') -> insert($userPrint);
        if($insert){
            $res['code'] = "200";
            $res['message'] = "success";
            $res['templateData'] = $createData;
            // 插入用户订单成功后，将之前缓存的订单列表删除
            Cache::rm($userOrderId);
        }else{
            $res['code'] = "400";
            $res['message'] = "NETWORK ERROR";
        }

        return json_encode($res);
    }

    /**
     * 用于监听用户未下单关闭界面时 将指定图片删除的操作
     */
    public function deleteOrderImage(){
        $request = Request::instance();
        $imgPath = $request -> param('imgsrc');
        $imgPathArr = explode(',', $imgPath);
        foreach ($imgPathArr as $k => $v) {
            @unlink('public'.DS.'uploads'.DS.$v);
        }
        // $res['code'] = "200";
        // 可不用返回
        // return json_encode($res);
    }

    /**
     * 更新用户订单状态 已发货->已完成 state 3 -> 4
     * @return res 完成状态
     */
    public function updateOrderState(){
        $request = Request::instance();
        $userOpenid = $request -> param('openid');
        $orderId = $request -> param('orderid');
        // 先进行订单状态判定
        // $orderState = Db::name('printlist') -> where('user_openid', $userOpenid) -> where('order_id', $orderId) -> field('state') -> find();
        // if ($orderState['state'] == 4) {
        //     $res['code'] = "201";
        //     $res['message'] = "订单状态已更新";
        //     return json_encode($res);
        // }
        $orderState = 4;
        $update = Db::name('printlist') -> where('user_openid', $userOpenid) -> where('order_id', $orderId) -> update(['state' => $orderState]);
        if($update){
            $res['code'] = "200";
            $res['message'] = "success";
        }else{
            $res['code'] = "400";
            $res['message'] = "NETWORK ERROR";
        }
        // 清楚订单缓存
        Cache::rm($userOpenid);
        return json_encode($res);
    }

    /**
     * 更新订单状态 - 支付
     * @return res 请求是否成功
     */
    public function updateState(){
        $request = Request::instance();
        $userOpenid = $request -> param('openid');
        $orderId = $request -> param('orderid');
        $isdeli = $request -> param('isdeli');
        // 判断用户是否需要发货
        if ($isdeli) {
            // 如果需要发货
            $state = 2;
        }else{
            $state = 1;
        }
        $update = Db::name('printlist') -> where('user_openid', $userOpenid) -> where('order_id', $orderId) -> update(['state' => $state, 'pay_time' => strval(time())]);
        if($update){
            $res['code'] = "200";
            $res['message'] = "success";
        }else{
            $res['code'] = "400";
            $res['message'] = "NETWORK ERROR";
        }

        $orderInfo = Db::name('printlist') -> where('user_openid', $userOpenid) -> where('order_id', $orderId) -> find();
        $state = $orderInfo['type'];
        if ($state == 'single') {
            $printState = '单张打印';
        }else if ($state == 'mulit') {
            $printState = '多张打印';
        }else if ($state == 'ident') {
            $printState = '证件照打印';
        }
        if ($orderInfo['isdelivery']) {
            $trackInfo = Db::name('track_co') -> where('idx', $orderInfo['track_no']) -> find();
            $logiState = $trackInfo['track_co_name'];
        }else{
            $logiState = "上门自取";
        }

        // 打印总张数
        $imgPath = $orderInfo['img_path'];
        $imgPathArr = explode(',', $imgPath);
        $totalNum = $orderInfo['quantity'] * sizeof($imgPathArr);

        $payInfo = Cache::get($orderId);
        $prepay_id = $payInfo['prepay_id'];
        // 执行模板消息发送
        // 构造模板消息
        // 对应模板消息名称为 - 下单成功通知 - 发送给下单客户
        $post_data = array(
            'touser'            =>      $userOpenid,
            'template_id'       =>      'f_kLDqFES5nZQ5W710UxXLUKQY04PeOTcZbu-4vpf3I',
            'page'              =>      'pages/orderdetail/orderdetail?orderid='.$orderId,
            'form_id'           =>      $prepay_id,
            'data'              =>      array(
                                            'keyword1'  =>  array('value' => $orderId),                                               //订单编号
                                            'keyword2'  =>  array('value' => date('Y-m-d H:i:s', $orderInfo['createtime'])),          //下单时间
                                            'keyword3'  =>  array('value' => $orderInfo['money'].'元'),                                    //订单金额
                                            'keyword4'  =>  array('value' => $printState.' 共'.$totalNum.'张'),                       //订单内容
                                            'keyword5'  =>  array('value' => '支付成功'),                                             //订单状态
                                            'keyword6'  =>  array('value' => $logiState),                                             //配送方式
                                            'keyword7'  =>  array('value' => '13906051853'),                                          //客服电话
                                            'keyword8'  =>  array('value' => '福建省厦门市湖里区江顺里237号之68'),                           //商户地址
                                            'keyword9'  =>  array('value' => '订单备注：'.$orderInfo['remark'])                       //温馨提示
                                        )
        );

        $post_data = json_encode($post_data);
        // 执行模板消息发送
        $createData = $this -> sendTempletMessage($post_data);

        $res['error_message'] = $createData;

        // 清除支付缓存
        Cache::rm($orderId);
        // 清除订单缓存
        Cache::rm($userOpenid);

        return json_encode($res);
    }

    /**
     * 通过订单号查找订单信息
     * @param orderid 订单号
     * @return json 订单详情
     */ 
    public function getOrderById(){
        $request = Request::instance();
        $userOpenid = $request -> param('openid');
        $orderId = $request -> param('orderid');
        // 获取卡券
        $coupon = $this -> getCoupon();
        $printlist = new Printlist;
        $orderInfo = $printlist -> where('order_id', $orderId) -> where('user_openid', $userOpenid) -> find();
        if ($orderInfo) {
            if($orderInfo['state'] == 6){
                $res['code'] = "202";
                $res['message'] = "Order Already Delete";
            }else{
                $res['code'] = "200";
                $res['message'] = "success";
            }
        }else{
            $res['code'] = "400";
            $res['message'] = "NETWORK ERROR";
            return json_encode($res);
        }
        // 订单状态判定
        // 时效性判断
        // 已发货但用户未点击确认，那么七天之内自动收获成功
        $updateArr = array();
        if ($orderInfo['state'] == 3 && (time() > intval($orderInfo['logi_time'] + 7*86400))) {
            $orderInfo['state'] = 4;
            $updateArr[]= array('state' => 4, 'idx' => $orderInfo['idx'], 'finishtime' => time());
        }
        // 未付款订单15分钟后自动归为已取消订单
        if ($orderInfo['state'] == 0 && (time() - intval($orderInfo['createtime']) > 900)) {
            $orderInfo['state'] = 5;
            $updateArr[]= array('state' => 5, 'idx' => $orderInfo['idx'], 'finishtime' => time());
        }
        // 优惠券判定
        if ($orderInfo['coupon_id']) {
            foreach ($coupon as $ke => $va) {
                if ($va['idx'] == $orderInfo['coupon_id']) {
                    $orderInfo['coupon_name'] = $va['name'];
                    $orderInfo['coupon_price'] = number_format(floatval($va['price']), 2);
                    break 1;
                }
            }
        }
        // 价格格式调整
        $orderInfo['money'] = number_format(floatval($orderInfo['money']), 2);
        $orderInfo['unit_price'] = number_format(floatval($orderInfo['unit_price']), 2);
        // 订单状态判定 分类
        if($orderInfo['state'] == 0){
            $orderInfo['state'] = '等待付款';
            $orderInfo['expire_time'] = date('i分s秒', strval($orderInfo['createtime'] + 900 - time()));
        }else if ($orderInfo['state'] == 1 || $orderInfo['state'] == 4) {
            $orderInfo['state'] = '交易完成';
        }else if ($orderInfo['state'] == 2) {
            $orderInfo['state'] = '等待配送';
        }else if ($orderInfo['state'] == 3) {
            $orderInfo['state'] = '已经发货';
        }else if ($orderInfo['state'] == 5) {
            $orderInfo['state'] = '交易取消';
        }
        // 打印类别判定
        $siteroot = "https://print.up.maikoo.cn/public/uploads/";
        if ($orderInfo['type'] == "mulit") {
            $imagePath = explode(',', $orderInfo['img_path']);
            $orderInfo['showImagePath'] = $siteroot.$imagePath[0];
            $orderInfo['type'] = '多张打印';
            // 打印数量
            $orderInfo['quantities'] = sizeof($imagePath) * $orderInfo['quantity'];
            $orderInfo['count'] = sizeof($imagePath);
        }else if ($orderInfo['type'] == 'ident') {
            $orderInfo['type'] = '证件照打印';
            $orderInfo['path'] = array($siteroot.$orderInfo['img_path']);
        }else if ($orderInfo['type'] == 'single') {
            $orderInfo['type'] = '单张打印';
            $orderInfo['path'] = array($siteroot.$orderInfo['img_path']);
        }
        if ($updateArr) {
            $printlist -> update($updateArr[0]);
        }
        // 日期时间调整
        $orderInfo['createtime_convert'] = date('Y-m-d H:i:s', $orderInfo['createtime']);
        // $orderInfo['createtime_convert'] = date('Y-m-d H:i:s', $orderInfo['createtime']);
        // $orderInfo['createtime_convert'] = date('Y-m-d H:i:s', $orderInfo['createtime']);

        $res['info'] = $orderInfo;
        return json_encode($res);

    }


    /**
     * ---------------------------------------------------------
     * ---------------------------------------------------------
     *                        推送消息发送
     * ---------------------------------------------------------
     * ---------------------------------------------------------
     */
    
    /**
     * 获取微信accesstoken 并返回
     * 利用TP5 Cache类去维护accessToken减少后台交互，提升使用速度
     * @return string asscessToken
     */
    public function getAccessToken(){
        $accessToken = Cache::get('accessToken');
        if (!$accessToken) {
            $appid = 'wx06a3684282ae583e';
            $appsecret = 'ec46f43c22e8e8efc5311fd23f12c1ec';
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            
            $info = file_get_contents($url);
            $info = json_decode($info);
            $info = get_object_vars($info);
            
            $accessToken = $info['access_token'];
            // $expirs_in = $info['expires_in'] - 100;
            // 将accessToken的有效期设置为3600s（一般情况下有效期7200s）
            Cache::set('accessToken', $accessToken, 6800);
        }

        return $accessToken;
    }

    /*
     *  发送模板消息
     */
    public function sendTempletMessage($postData){

        $accessToken = $this -> getAccessToken();
        // $access_token = "4_Jy79EbZz8z04qBNdILIs6ZdAWWN1dAs0Dz7BJrLpDCUgBaNiaoL9o2ulH5Ki89Zx01BvYzRMvS4-ArKMgm4eAaMmNXlrWCWNhC8zyoi7BATKxplujaf_wW1Az2hnv9geHWgCL6P5psNtRr9ECYTjACASOJ";

        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$accessToken;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 这句话很重要 因为是SSL加密协议
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $output = curl_exec($ch);
        curl_close($ch);
        // var_dump(curl_error($ch));
        $info = json_decode($output);
        $info =  get_object_vars($info);
        // dump($info);
        // die;
        return $info;
    }


    /**
     * ---------------------------------------------------------
     * ---------------------------------------------------------
     *                         微信支付
     * ---------------------------------------------------------
     * ---------------------------------------------------------
     */
    

    /**
     * 小程序调用此程序来调用微信支付相关
     * @return json result
     */
    public function createWxPay(){

        $request = Request::instance();
        
        $orderId = $request -> param('orderid');

        // 因为是支付失败，设置的支付有效期为15分钟，所以肯定能够取到之前的prepayid进行二次支付
        if ($orderId) {
            $res = Cache::get($orderId);
            if (!$res) {
                $res = array();
                $res['code'] = "401";
                $res['message'] = "订单已失效";
            }
            return json_encode($res);
        }else{
            $orderId = $this -> getTradeNo();
            $payInfo['totalFee'] = intval($request -> param('totalFee') * 100);
            $payInfo['userOpenid'] = $request -> param('openid');
            $payInfo['orderId'] = $orderId;
        }

        // 调用统一下单接口
        $res = $this -> unifiedOrder($payInfo);
        // 缓存PrepayId及其它相关信息
        Cache::set($orderId, $res, 1800);

        return json_encode($res);

    }

    /**
     * 
     * 统一下单，WxPayUnifiedOrder中out_trade_no、body、total_fee、trade_type必填
     * @param array $payInfo 商品总价
     * @param int $timeOut
     * @return 成功时返回，其他抛异常
     */
    public function unifiedOrder($payInfo, $timeOut = 6)
    {
        $request = Request::instance();
        // 将元转化为分
        // $totalFee = intval($request -> param('totalFee') * 100);
        // $userOpenid = $request -> param('openid');

        $totalFee = $payInfo['totalFee'];
        $userOpenid = $payInfo['userOpenid'];
        $out_trade_no = $payInfo['orderId'];

        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        
        // 微信支付相关参数
        // 服务上版本
        // $values = array(
        //             'appid'             =>  'wx176f770dff4fdc02',   //特约商户公众号的appid
        //             'mch_id'            =>  '1441564402',           //特约商户的商户号
        //             'sub_appid'         =>  'wx06a3684282ae583e',   //调起微信支付的小程序appid
        //             'sub_mch_id'        =>  '1499383782',           //商家的商户号
        //             'body'              =>  '厦门云打印-打印费用',
        //             'spbill_create_ip'  =>  $_SERVER['REMOTE_ADDR'],
        //             'notify_url'        =>  'https://print.up.maikoo.cn/wxpay/checkWxPayResult',
        //             'trade_type'        =>  'JSAPI',
        //             'nonce_str'         =>  self::getNonceStr(),
        //             'out_trade_no'      =>  $this -> getTradeNo(),    //商户产生的订单号
        //             'total_fee'         =>  $totalFee,
        //             'limit_pay'         =>  'no_credit',              //不使用信用卡
        //             'sub_openid'        =>  $userOpenid        
        //             // 'time_start'        =>  date('YmdHis',time()),    //订单开始时间
        //             // 'time_expire'       =>  strval(date('YmdHis',time()) + 920)     //订单结束时间 15分钟有效期
        //             // 'sign_type'         =>  'MD5'
        //         );

        //商户版本
        $values = array(
                    'appid'             =>  'wx06a3684282ae583e',   //调起微信支付的小程序appid
                    'mch_id'            =>  '1499383782',           //商家的商户号
                    'body'              =>  '厦门云打印-打印费用',
                    'spbill_create_ip'  =>  $_SERVER['REMOTE_ADDR'],
                    'notify_url'        =>  'https://print.up.maikoo.cn/wxpay/checkWxPayResult',
                    'trade_type'        =>  'JSAPI',
                    'nonce_str'         =>  self::getNonceStr(),
                    'out_trade_no'      =>  $out_trade_no,    //商户产生的订单号
                    'total_fee'         =>  $totalFee,
                    // 'limit_pay'         =>  'no_credit',
                    'openid'            =>  $userOpenid,
                    'time_start'        =>  date('YmdHis',time()),
                    'time_expire'       =>  strval(date('YmdHis',time() + 1900)),    //订单结束时间 15分钟有效期
                );
        
        // 排序
        ksort($values);
        // MakeSign 签名
        $values['sign'] = $this -> MakeSign($values);

        $values_xml = $this -> ToXml($values);
        // $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($values_xml, $url, false, $timeOut);
        // 签名再校验
        $result = $this -> Init($response);
        // self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        // 数据判断 若成功则返回 已签名后的paySign 否则 返回错误信息
        $result = $this -> checkResult($result);
        // 将orderid加入回调数据,便于订单生成
        $result['orderid'] = $values['out_trade_no'];

        return $result;
    }

    /**
     * 检查用户支付返回结果
     * @return res 签名结果
     */
    public function checkResult($result){
        if($result['return_code'] != "SUCCESS"){
            return $result;
        }
        if ($result['result_code'] == "SUCCESS" && $result['return_code'] == "SUCCESS") {
            // 对小程序支付需要的参数再签名
            $time = time();
            $paySign = MD5('appId='.self::APPID.'&nonceStr='.$result['nonce_str'].'&package=prepay_id='.$result['prepay_id'].'&signType=MD5&timeStamp='.strval($time).'&key='.self::KEY);
            $res['prepay_id'] = $result['prepay_id'];
            $res['appId'] = $result['appid'];
            $res['nonce_str'] = $result['nonce_str'];
            $res['paySign'] = $paySign;
            $res['timeStamp'] = strval($time);
            $res['code'] = "200";
            return $res;
        }else{
            return $result;
        }

    }

    /**
     * 
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public static function getNonceStr($length = 32) 
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {  
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
        } 
        return $str;
    }

    /**
     * 产生订单号
     * @return string 订单号 生成规则为 0323 + timestamp后二位 + microtime前三位(小数点后)
     */
    public function getTradeNo(){
        $out_trade_no = "";
        $micorTime = microtime();
        $micorTime = explode('.', $micorTime);
        $micorTime = substr($micorTime[1], 0, 3);
        $out_trade_no = date('md', time()) . substr(strval(time()), -3, -1) . $micorTime;
        // $out_trade_no .= substr(time(), -4);
        return $out_trade_no;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($values)
    {
        //签名步骤一：按字典序排序参数
        ksort($values);
        $string = $this -> ToUrlParams($values);
        // $string = "";
        // dump(htmlspecialchars_decode($string)); die;
        $string = htmlspecialchars_decode($string);
        //签名步骤二：在string后加入KEY
        $string = htmlspecialchars_decode($string . "&key=".self::KEY);
        //签名步骤三：MD5加密
        $string = md5(htmlspecialchars_decode($string));
        // echo $string;
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($values)
    {
        $buff = "";
        foreach ($values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
    **/
    public function ToXml($values)
    {
        if(!is_array($values) || count($values) <= 0)
        {
            return 0;
        }
        
        $xml = "<xml>";
        foreach ($values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key.">".$val."</".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml; 
    }

        /**
     * 以post方式提交xml到对应的接口url
     * 
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    private static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {       
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        //如果有配置代理这里就设置代理
        // if(self::CURL_PROXY_HOST != "0.0.0.0" 
        //     && self::CURL_PROXY_PORT != 0){
        //     curl_setopt($ch,CURLOPT_PROXY, self::CURL_PROXY_HOST);
        //     curl_setopt($ch,CURLOPT_PROXYPORT, self::CURL_PROXY_PORT);
        // }
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
        // if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
        //     curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        //     curl_setopt($ch,CURLOPT_SSLCERT, self::SSLCERT_PATH);
        //     curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        //     curl_setopt($ch,CURLOPT_SSLKEY, self::SSLKEY_PATH);
        // }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else { 
            $error = curl_errno($ch);
            curl_close($ch);
            return "curl出错，错误码:$error";
        }
    }
    
    /**
     * 获取毫秒级别的时间戳
     */
    private static function getMillisecond()
    {
        //获取毫秒的时间戳
        $time = explode ( " ", microtime () );
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode( ".", $time );
        $time = $time2[0];
        return $time;
    }


    // 支付请求结果返回验证
    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function Init($xml)
    {   
        $values = $this -> FromXml($xml);
        //fix bug 2015-06-29
        if($values['return_code'] != 'SUCCESS'){
             return $values;
        }
        $this -> CheckSign($values);
        return $values;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
    {   
        if(!$xml){
            return "xml数据异常！";
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);      
        return $values;
    }

    /**
     * 
     * 检测签名
     */
    public function CheckSign($values)
    {
        //fix异常
        if(!$this->IsSignSet($values)){
            return "签名错误！";
        }
        
        $sign = $this->MakeSign($values);
        if($values['sign'] == $sign){
            return true;
        }
        return "签名错误！";
    }

    /**
    * 判断签名，详见签名生成算法是否存在
    * @return true 或 false
    **/
    public function IsSignSet($values)
    {
        return array_key_exists('sign', $values);
    }

    /**
     * 
     * 上报数据， 上报的时候将屏蔽所有异常流程
     * @param string $usrl
     * @param int $startTimeStamp
     * @param array $data
     */
    public static function reportCostTime($url, $startTimeStamp, $data)
    {
        //如果不需要上报数据
        if(self::REPORT_LEVENL == 0){
            return;
        } 
    }


    public function test(){
        // echo strlen('109A310CB8548B79C534B28F732858F6');
        // echo htmlspecialchars("&not");
        // echo time();
                // $out_trade_no = date('Ymd', time());
        
        // $date1 = date_create('2015年06月29 19:23:23');
        // $date2 = date_create("2015年06月30 19:21:22");
        // echo date_diff($date1, $date2);
        echo date('Y-m-d H:i:s', '1522929600');
        
        echo "</br>";
        echo date('Y-m-d H:i:s', '1522755566');
        // $out_trade_no = "";
        // $micorTime = microtime();
        // $micorTime = explode('.', $micorTime);
        // $micorTime = substr($micorTime[1], 0, 4);
        // $out_trade_no = date('md', time()) . substr(strval(time()), -2, -1) . $micorTime;
        // echo $out_trade_no;
        // $out_trade_no = "";
        // $micorTime = microtime();
        // $micorTime = explode('.', $micorTime);
        // $micorTime = substr($micorTime[1], 0, 3);
        // $out_trade_no = date('md', time()) . substr(strval(time()), -3, -1) . $micorTime;
        // echo $out_trade_no;
        // echo "</br>";
        // dump($_SERVER);
        // $out_trade_no .= substr(time(), -4);
        // dump(strval(date('YmdHis',time()) + 1500));
        // echo substr(time(), -4);
    }



}
