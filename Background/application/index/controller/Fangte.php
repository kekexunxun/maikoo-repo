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

use app\index\model\Goods;

use app\index\model\Banner;



class Fangte extends Controller{

    

    const APPID = "wx06a3684282ae583e";

    const APPSECRET = "ec46f43c22e8e8efc5311fd23f12c1ec";

    const DS = DIRECTORY_SEPARATOR;

    // 微信支付相关

    const REPORT_LEVENL = 0;

    const KEY = "ls2805aeu2w0epzeawisc21f9wolmovo";

    const CURL_PROXY_HOST = "0.0.0.0";

    const CURLOPT_PROXYPORT = 0;





    /**

     * 根据关键词搜索商品信息

     *

     * @param Request $request

     * @return void

     */

    public function searchGoods(Request $request){

        // 获取传递过来的查询数据

        $inputVal = htmlspecialchars($request -> param('inputVal'));

        $goods = new Goods;

        $searchGoodsInfo = $goods -> where('is_active', 1) -> where('is_delete', 0) -> where('name', 'like', "%".$inputVal."%") -> limit(4) -> field('goods_id, name, pic') -> select();

        if ($searchGoodsInfo) {

            $res['goods'] = $searchGoodsInfo;

            $res['code'] = "200";

            $res['message'] = "search Success";

        }else{

            $res['code'] = "401";

            $res['message'] = "no Goods has been found";

        }

        return json_encode($res);

    }



    public function getShopInfo(){

        $banner = new Banner;

        $bannerList = $banner -> where('is_active', 1) -> where('is_delete', 0) -> order('orderby desc') -> field('banner_src, goods_id') -> select();

        // 对拿到的Banner列表做处理

        foreach ($bannerList as $k => $v) {

            $v['banner_src'] = "https://ft.up.maikoo.cn" . $v['banner_src'];

        }



        $res['banner'] = $bannerList;

        return json_encode($res);

    }





    public function getGoods(Request $request){

        // 先直接接入数据库 每次获取12个

        $goodsCatId = intval($request -> param('catId'));

        $goodsPage = intval($request -> param('page'));

        $searchStart = $goodsPage * 12;

        $searchEnd = $searchStart + 11;

        $goods = new Goods;

        // 获取商品总数

        $goodsCount = Cache::get('shopGoodsCount');

        if (!$goodsCount) {

            $goodsCount = $goods -> where('is_active', 1) -> where('is_delete', 0) -> field('catagory_id') -> select();

            $goodsCountArr = array();

            foreach ($goodsCount as $k => $v) {

                $goodsCountArr[]= $v['catagory_id'];

            }

            // 计算每个分类的总数

            $goodsCount = array_count_values($goodsCountArr);

            Cache::set('shopGoodsCount', $goodsCount, 0);

        }

        // 获取当前分类的商品总数

        $currentCatCount = 0;

        foreach ($goodsCount as $k => $v) {

            if ($goodsCatId == $k) {

                $currentCatCount = $v;

            }

        }

        // 如果当前分类没有商品

        if ($currentCatCount == 0) {

            $res['code'] = "201";

            $res['message'] = "No Goods";

            return json_encode($res);

        }

        // 判断商品的获取范围

        if ($searchEnd >= $currentCatCount) {

            $searchEnd = $currentCatCount - 1;

            $res['isEnd'] = true;

        }else{

            $res['isEnd'] = false;

        }

        // 获取商品信息

        $goodsList = $goods -> where('is_active', 1) -> where('is_delete', 0) -> where('catagory_id', $goodsCatId) -> field('goods_id, catagory_id, name, price, shop_price, pic, spec, promotion_id, is_express') -> limit($searchStart, $searchEnd) -> order('orderby desc') -> select();

        if ($goodsList) {

            // 对图片地址做处理

            foreach ($goodsList as $k => $v) {

                $v['pic'] = "https://ft.up.maikoo.cn" . $v['pic'];

            }

            $res['code'] = "200";

            $res['goods'] = $goodsList;

            $res['message'] = "Search Success";

        }

        dump($goodsList); die;

        return json_encode($res);

    }



    public function test(){

        $goods = new Goods;

        $goodsList = $goods-> where('is_active', 1) -> where('is_delete', 0) -> field('catagory_id') -> select();

        dump($goodsList);

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

        $info = json_decode($output);

        $info =  get_object_vars($info);

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





}

