<?php

/**
 * 小程序商城
 * @author Locked
 * createtime 2018-05-03
 */

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\Session;
use think\File;

use app\index\model\Usercount;
use app\index\model\Userinfo;
use app\index\model\Mini_click_count;
use app\index\model\Column_click_count;
use app\index\model\Clause;
use app\index\model\Cat_click_count;
use app\index\model\Setting;
use app\index\model\Rate;
use app\index\model\Article_click_count;

class Minibase extends Controller{
    // 小程序APPID
    const APPID = "wxe8906a23ac34d51c";
    // 小程序APPSECRET
    const APPSECRET = "af3d0948de2660a2567cf2a1b34cceda";
    const DS = DIRECTORY_SEPARATOR;
    const SITEROOT = "https://minipro.up.maikoo.cn/public";

    /**
     * 获取用户信息
     * @param array userInfo
     * @param string openid
     * @return json 是否插入成功成功
     */
    public function setUserInfo(Request $request){
        $userOpenid = $request -> param('openid');
    	// 有一个Openid 的缓存array，如果已经将该用户数据插入过，在缓存中就会体现
    	// 判断缓存库中是否有该openid
    	// 获取用户信息并入库
        $userinfo = new Userinfo;
        $userInfo = $request -> param('userInfo/a');
        $updateArr = $userInfo;
        $updateArr['is_auth'] = 1;
        $update = $userinfo -> where('user_openid', $userOpenid) -> update($updateArr);
        if (!$update) {
            return objReturn(200, 'failed', $insert);
        }
        // 更新用户信息到缓存
        $userAccountInfo = Cache::get('userAccountInfo');
        foreach ($userAccountInfo as $k => $v) {
            if ($v['user_openid'] == $userOpenid) {
                $userAccountInfo[$k]['userInfo'] = $userInfo;
                $userAccountInfo[$k]['isAuth'] = true;
                break 1;
            }
        }
        Cache::set('userAccountInfo', $userAccountInfo, 0);
        return objReturn(0, 'success', $userAccountInfo);
    }

    /**
     * @author Locked <462387164@qq.com>
     * 获取用户openID
     * 
     * @param string code 登陆时范湖的code
     * @return json 用户openid
     */
    public function getUserOpenid(Request $request){
        $code = $request -> param('code');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".self::APPID."&secret=".self::APPSECRET."&js_code=".$code."&grant_type=authorization_code";
        $info = file_get_contents($url);
        $info = json_decode($info);
        $info =  get_object_vars($info);
        $res = array();
        $res['openid'] = $info['openid'];
        // 判断当前用户是否在数据库中
        // 防止用户删除小程序之后重获取导致的数据不匹配
        $userAccountInfo = Cache::get('userAccountInfo');
        if ($userAccountInfo && sizeof($userAccountInfo) > 0) {
            foreach ($userAccountInfo as $k => $v) {
                if ($v['user_openid'] == $res['openid']) {
                    $res['user'] = $v;
                    $res['code'] = "200";
                    $res['message'] = "User Already Exist";
                    return json_encode($res);
                }
            }
        }
        // 每个账号的登录态有效期为3天
        // $res['expire_time'] = time() + 259200;
        // 将用户信息入库，记录用户进入小程序信息
        // $usercount = new Usercount;
        // $usercount -> insert(['user_openid' => $res['openid'], 'create_time' => date('Y-m-d H:i:s', time())]);
        // 将用户信息入库
        // $userinfo = new Userinfo;
        $userID = Db::name('userinfo') -> insertGetId(['user_openid' => $res['openid'], 'create_time' => time()]);
        // 将新用户的信息放入缓存
        $userAccountInfo = Cache::get('userAccountInfo');
        if (!$userAccountInfo) {
            $userAccountInfo = array();
        }
        $currentUser = array();
        $currentUser['user_openid'] = $res['openid'];
        $currentUser['userInfo'] = null;
        $currentUser['isAuth'] = false;
        $currentUser['userID'] = $userID;     //用户ID
        $userAccountInfo []= $currentUser;

        $res['user'] = $currentUser;
        Cache::set('userAccountInfo', $userAccountInfo, 0);

        return json_encode($res);
    }

    /**
     * 将用户登陆信息插入数据库中
     * 将用户点击小程序的信息插入到数据库中
     * 
     * @param Request $request
     * @return void
     */
    public function setUserLog(Request $request){
        $logs = $request -> param('logs/a');
        $miniLogs = $request -> param('miniLogs/a');
        $columnLogs = $request -> param('columnLogs/a');
        $catLogs = $request -> param('catLogs/a');
        $articleLogs = $request -> param('articleLogs/a');
        $openid = $request -> param('openid');
        $logArr = array();
        arsort($logs);
        foreach ($logs as $k => $v) {
            $array['open_time'] = date('Y-m-d H:i:s', $v);
            $array['user_openid'] = $openid;
            $logArr []= $array;
        }
        $usercount = new Usercount;
        $usercount -> saveAll($logArr);
        // 如果有小程序点击的log就存入对应数据库
        if ($miniLogs && count($miniLogs) > 0) {
            foreach ($miniLogs as $k => $v) {
                $miniLogs[$k]['create_time'] = time();
                $miniLogs[$k]['user_openid'] = $openid;
            }
            $mini_click_count = new Mini_click_count;
            $mini_click_count -> saveAll($miniLogs);
        }
        // 如果有专栏点击的log就存入对应的数据库
        if ($columnLogs && count($columnLogs) > 0) {
            foreach ($columnLogs as $k => $v) {
                $columnLogs[$k]['create_time'] = time();
                $columnLogs[$k]['user_openid'] = $openid;
            }
            $column_click_count = new Column_click_count;
            $column_click_count -> saveAll($columnLogs);
        }
        // 如果有分类点击的log就存入对应的数据库
        if ($catLogs && count($catLogs) > 0) {
            foreach ($catLogs as $k => $v) {
                $catLogs[$k]['create_time'] = time();
                $catLogs[$k]['user_openid'] = $openid;
            }
            $cat_click_count = new Cat_click_count;
            $cat_click_count -> saveAll($catLogs);
        }
        // 如果有文章点击的log就存入对应的数据库
        if ($articleLogs && count($articleLogs) > 0) {
            foreach ($articleLogs as $k => $v) {
                $articleLogs[$k]['create_time'] = time();
                $articleLogs[$k]['user_openid'] = $openid;
            }
            $article_click_count = new Article_click_count;
            $article_click_count -> saveAll($articleLogs);
        }
        
    }

    /**
     * 判断该用户是否为管理员
     *
     * @param Request $request
     * @return void
     */
    public function getUserAccountState(Request $request){
        $userOpenid = $request -> param('openid');

        // 用户信息缓存
        $userAccountInfo = Cache::get('userAccountInfo');

        if ($userAccountInfo) {
            foreach ($userAccountInfo as $k => $v) {
                if ($v['user_openid'] == $userOpenid) {
                    $res['data'] = $v;
                    $res['code'] = "200";
                    $res['msg'] = "Get Current User Success";
                    return json_encode($res);
                }
            }
        }

        $res['code'] = "400";
        $res['message'] = "No Info Exist";

        return json_encode($res);
    }

    /**
     * 获取当前系统的用户协议
     *
     * @return void
     */
    public function getCaluse(){
        $clause = new Clause;
        $clauseInfo = $clause -> where('idx', 1) -> field('clause') -> select();
        $clauseInfo = collection($clauseInfo) -> toArray();
        return objReturn(0, 'success', $clauseInfo[0]);
    }

    /**
     * 此方法为自动调用方法 每隔一小时3600s执行一次
     * 1 计算小程序的得分
     * 2 计算小程序的热度
     *
     * @return void
     */
    public function autoRun(){
        // 获取当前设置
        $setting = new Setting;
        $settings = $setting -> where('idx', 1) -> select();
        $settings = collection($settings) -> toArray();
        // 当系统设置开启时才进行运行 否则就不运行
        if ($settings['is_auto_cal_mini_data'] == 0) {
            return;
        }
        // 1 计算所有小程序的得分
        $rate = new Rate;
        
    }
}