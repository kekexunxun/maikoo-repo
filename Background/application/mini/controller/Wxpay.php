<?php

/**
 * 微信支付整合
 * @author Locked
 * createtime 2018-05-24
 */

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use app\index\model\Order;
use app\index\model\Course_user;

/**
 * ---------------------------------------------------------
 * ---------------------------------------------------------
 *                         微信支付
 * ---------------------------------------------------------
 * ---------------------------------------------------------
 */
class Wxpay extends Controller
{

    // 微信支付相关
    const REPORT_LEVENL = 0;                                        // 上报等级
    const CURL_PROXY_HOST = "0.0.0.0";                              // 代理地址
    const CURLOPT_PROXYPORT = 0;                                    // 代理端口

    /**
     * 小程序调用此程序来调用微信支付相关
     * @return json result
     */
    public function createWxPay()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $openid = request()->param('openid');
        if (empty($openid)) return objReturn(400, 'Invaild Param');

        $openid = request()->param('openid');
        $orderSn = request()->param('ordersn');
        $uid = intval(request()->param('uid'));
        
        // 若没有对应缓存 此时就相当于重新下单
        $orderFee = Db::name('order')->where('order_sn', $orderSn)->value('fee');
        if (!$orderFee) return objReturn(400, 'Network Error');
        $payInfo['totalFee'] = intval($orderFee * 100);
        $payInfo['openid'] = $openid;
        $payInfo['orderSn'] = $orderSn;
        $payInfo['uid'] = $uid;

        // 调用统一下单接口
        $res = $this->unifiedOrder($payInfo);
        // 缓存PrepayId及其它相关信息
        if (isset($res['code'])) {
            return objReturn(0, 'success', $res);
        } else {
            return objReturn(401, 'failed', $res);
        }
    }

    /**
     * 支付结果检测
     *
     * @param Request $request
     * @return void
     */
    public function checkWxpay()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $orderSn = request()->param('ordersn');
        $prepayCache = Cache::get('prepayCache');
        $isChecked = false;
        foreach ($prepayCache as $k => $v) {
            if ($v['orderSn'] == $orderSn && $v['isChecked'] == 1) {
                $isChecked = true;
                // array_splice($prepayCache, $k, 1);
                break;
            }
        }
        if ($isChecked) {
            // Cache::set('prepayCache', $prepayCache, 0);
            
            // 支付检测成功 用户课程更新
            // 通过order查找相关信息
            $order = new Order;
            $orderInfo = $order->alias('o')->join('art_course c', 'o.course_id = c.course_id', 'LEFT')->join('art_classes_user cu', 'o.uid = cu.uid and o.class_id = cu.class_id', 'LEFT')->field('o.uid, o.class_id, c.course_period, c.course_times, cu.course_end_at, cu.course_left_times, cu.renew_times')->where('o.order_sn', $orderSn)->find();
            $classUser['course_left_times'] = $orderInfo['course_times'] + $orderInfo['course_left_times'];
            $classUser['course_end_at'] = $orderInfo['course_end_at'] + $orderInfo['course_period'] * 86400 - 1;
            $classUser['renew_times'] = $orderInfo['renew_times'] + 1;

            Db::startTrans();
            try {
                $updateClass = Db::name('classes_user')->where('uid', $orderInfo['uid'])->where('class_id', $orderInfo['class_id'])->update($classUser);
                // 修改订单状态
                $updateOrder = Db::name('order')->where('order_sn', $orderSn)->update(['status' => 1, 'pay_at' => time()]);
                // 提交事务
                Db::commit();
                if (!$updateClass || !$updateOrder) {
                    throw new \Exception('Update Failed');
                }
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return objReturn(400, 'failed');
            }
            return objReturn(0, 'success');
        }
        return objReturn(400, 'failed');
    }

    /**
     * 新增订单预支付缓存信息
     *
     * @return void
     */
    public function setPrepayCache($prepayInfo)
    {
        $prepayCache = $this->checkPrepay();
        if (!$prepayCache) {
            $prepayCache = array();
            $prepayCache[] = $prepayInfo;
        } else {
            $prepayCache[] = $prepayInfo;
        }
        Cache::set('prepayCache', $prepayCache, 0);
    }

    /**
     * 获取订单预支付缓存信息
     *
     * @return void
     */
    public function checkPrepay($orderSn = null)
    {
        $prepayCache = Cache::get('prepayCache');
        if (!$prepayCache) {
            return null;
        }
        if ($orderSn && $prepayCache) {
            $isExpire = null;
            foreach ($prepayCache as $k => $v) {
                if (isset($v['orderSn']) && $v['orderSn'] == $orderSn) {
                    if ($v['order_expire_time'] < time()) {
                        $order = new Order;
                        $order->where('order_sn', $orderSn)->update(['status' => 2, 'cancel_at' => time()]);
                        $isExpire = $k;
                        break;
                    } else {
                        $prepayCache[$k]['isCache'] = true;
                        Cache::set('prepayCache', $prepayCache, 0);
                        return $v;
                    }
                }
            }
            if ($isExpire) {
                array_splice($prepayCache, $isExpire, 1);
                Cache::set('prepayCache', $prepayCache, 0);
            }
            return null;
        }
        return $prepayCache;
    }

    /**
     * 微信支付结果回调 检测用户订单支付情况
     *
     * @param string $orderId 订单号
     * @return void
     */
    public function updatePrepayCache($orderSn)
    {
        $prepayCache = Cache::get('prepayCache');
        foreach ($prepayCache as $k => $v) {
            if ($v['orderSn'] == $orderSn) {
                $prepayCache[$k]['isChecked'] = true;
                break;
            }
        }
        Cache::set('prepayCache', $prepayCache, 0);
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
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

        $values = array(
            'appid' => config('APPID'),                                    // 调起微信支付的小程序appid
            'body' => '吸铁石美术-课程费用',                                      // 商品描述
            'limit_pay' => 'no_credit',                                 // 是否支持信用卡支付
            'mch_id' => config('MCH_ID'),                                   // 商家的商户号
            'nonce_str' => self::getNonceStr(),                            // 随机字符串
            'notify_url' => config('SITEROOT') . DS . config('WXPAY_NOTIFY_URL'),              // 支付回调地址
            'openid' => $payInfo['openid'],                                    // 用户openid
            'out_trade_no' => $payInfo['orderSn'],                                  // 商户产生的订单号
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],                        // 调起支付的服务器IP地址
            'time_expire' => strval(date('YmdHis', intval(time() + 600))),   // 订单结束时间 10分钟有效期
            'time_start' => strval(date('YmdHis', time())),
            'total_fee' => $payInfo['totalFee'],
            'trade_type' => 'JSAPI',
        );
    
        // MakeSign 签名
        $values['sign'] = $this->MakeSign($values);
        // 生成XML
        $values_xml = $this->ToXml($values);
        // 发送XML
        // $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($values_xml, $url, false);
        // 签名再校验
        $result = $this->Init($response);
        // 签名请求上报
        // self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
        // 数据判断 若成功则返回 已签名后的paySign 否则 返回错误信息
        $result = $this->checkResult($result);
        // 设置过期时间为 1180s
        $result['order_expire_time'] = intval(time() + 7200);
        if (isset($result['code']) && $result['code'] == '200') {
            $result['isCache'] = false;
            $result['orderSn'] = $payInfo['orderSn'];
            $this->setPrepayCache($result);
        }

        return $result;
    }

    /**
     * 检查用户支付返回结果
     * @return res 签名结果
     */
    public function checkResult($result)
    {
        if ($result['return_code'] != "SUCCESS") {
            return $result;
        }
        if ($result['result_code'] == "SUCCESS" && $result['return_code'] == "SUCCESS") {
            // 对小程序支付需要的参数再签名
            $time = time();
            $paySign = MD5('appId=' . config('APPID') . '&nonceStr=' . $result['nonce_str'] . '&package=prepay_id=' . $result['prepay_id'] . '&signType=MD5&timeStamp=' . strval($time) . '&key=' . config('WXPAY_KEY'));
            $res['prepay_id'] = $result['prepay_id'];
            $res['appId'] = $result['appid'];
            $res['nonce_str'] = $result['nonce_str'];
            $res['paySign'] = $paySign;
            $res['timeStamp'] = strval($time);
            $res['isChecked'] = false;
            $res['code'] = "200";
            return $res;
        } else {
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
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($values)
    {
        //签名步骤一：按字典序排序参数
        ksort($values);
        $string = $this->ToUrlParams($values);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . config('WXPAY_KEY');
        //签名步骤三：MD5加密
        $string = md5($string);
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
        foreach ($values as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
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
        if (!is_array($values) || count($values) <= 0) {
            return 0;
        }

        $xml = "<xml>";
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</xml>";
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
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($useCert == true) {
            // 设置证书
            // 使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, self::SSLCERT_PATH);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, self::SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
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
        $time = explode(" ", microtime());
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode(".", $time);
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
        $values = $this->FromXml($xml);
        //fix bug 2015-06-29
        if ($values['return_code'] != 'SUCCESS') {
            return $values;
        }
        if ($this->CheckSign($values)) {
            return $values;
        } else {
            return "签名错误";
        }
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
    {
        if (!$xml) {
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
        if (!$this->IsSignSet($values)) {
            return "签名错误！";
        }

        $sign = $this->MakeSign($values);
        if ($values['sign'] == $sign) {
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
        if (self::REPORT_LEVENL == 0) {
            return;
        }
    }

    public function checkWxPayResult()
    {
        //解析xml
        $input = file_get_contents("php://input");
        $data = $this->FromXml($input);

        // Cache::set('data', $data);
        
        // $data = Cache::get('data');
        // dump($this->checkPrepay($data['out_trade_no']));die;

        if ($data['return_code'] == 'SUCCESS' && $data['result_code'] == 'SUCCESS') {
            //已重判断
            $otn = $this->checkPrepay($data['out_trade_no']);
            if ($otn && $otn['isChecked']) {
                exit('Already Checked');
            }
            
            // 从订单中获取部分字段信息
            $orderFee = Db::name('order')->where('order_sn', $data['out_trade_no'])->value('fee');
            $totalFee = intval($orderFee * 100);
            // 校验金额
            if (!$totalFee == $data['total_fee']) {
                exit('Wrong fee');
            }
            //校验sign
            if (!$this->CheckSign($data)) {
                // 校验失败
                exit('Wrong Sign');
            }

            // 校验成功 更新对应缓存
            $this->updatePrepayCache($data['out_trade_no']);
            //成功返回给微信
            return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }
    }

}