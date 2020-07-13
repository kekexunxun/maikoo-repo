<?php

/**
 * 发送短信
 * @author Locked
 * createtime 2018-05-07
 */

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\Session;
use think\File;

use app\index\model\Sms_log;

class Sms extends Controller{

    // 阿里云短信验证相关
    const APPID = "1400083565";
    const APPKEY = "fd5534c1a0cd409e52e6e150672a242a";
    const NUMBER_MIXED = 1;
    const NUMBER_PURE = 2;

    public function sendSingleSms(Request $request){
        
        $mobile = intval($request -> param('telNum'));
        // 手机号正则匹配
        if(!preg_match('/^1\d{10}$/', $mobile)) {
            $res['code'] = "401";
            $res['message'] = "Invaid TelNum";
            return json_encode($res);
        }
        // 短信发送

        $sign = '麦应用';
        
        $random = rand(100000, 999999);
        // $response = file_get_contents("https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=1400083565&random=xxxx".http_build_query($sms_param));
        $url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=".self::APPID."&random=".$random;
        // 构造短信dataObj
        // 生成当前时间
        $curTime = time();
        // 生成验证码Code
        $code = $this -> getRandomNum(6, self::NUMBER_PURE);
        $content = "尊敬的用户，您的短信验证码为" . $code;
        // 短信签名
        $dataObj = array();
        $dataObj['sig'] = hash("sha256", "appkey=".self::APPKEY."&random=" . $random . "&time=" . $curTime . "&mobile=" . $mobile, FALSE);
        $dataObj['ext'] = ""; 
        $dataObj['extend'] = ""; 
        $dataObj['msg'] = "【麦口网】".$content; 
        $dataObj['tel'] = array("mobile" => $mobile, "nationcode" => "86"); 
        $dataObj['time'] = intval($curTime); 
        $dataObj['type'] = 0; 

        // 使用curl来发送短信
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($curl);
        if (false == $ret) {
            // curl_exec failed
            $res['code'] = "400";
            $res['message'] = curl_error($curl);
        } else {
            $rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $res['code'] = "402";
                $res['message'] = "Error Code " . $rsp;
            } else {
                $res['code'] = "403";
                $res['message'] = "Error Code " . $rsp;
            }
        }
        curl_close($curl);

        // 发送成功 将数据插入数据库
        $smsLog['user_openid'] = $request -> param('openid');
        $smsLog['telNum'] = $mobile;
        $smsLog['create_time'] = time();

        $res['code'] = "200";
        $res['validateCode'] = $code;
        $res['message'] = "Sms Send Success";

        return json_encode($res);
    }


    /**
     * 获取指定类型和长度的随机数
     *
     * @param int $length
     * @param int $type
     * @return int 随机数
     */
    public function getRandomNum($length, $type){
        if ($type == self::NUMBER_MIXED) {
            $pool = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }else if ($type == self::NUMBER_PURE) {
            $pool = "0123456789";
        }
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

}