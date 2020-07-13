<?php

/**
 * 方特小程序微信数据相关
 * @author Locked
 * createtime 2018-05-03
 */

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\Session;
use think\File;


class WxInfo extends Controller{

    // 小程序APPID
    const APPID = "wx06a3684282ae583e";
    // 小程序APPSECRET
    const APPSECRET = "ec46f43c22e8e8efc5311fd23f12c1ec";
    const DS = DIRECTORY_SEPARATOR;

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
    				$res['msg'] = "Info Already Exist";
    				return json_encode($res);
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
            $res['msg'] = "Info Insert Success";
        }else{
            $res['code'] = "300";
            $res['msg'] = "Info Insert Falied";
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

}