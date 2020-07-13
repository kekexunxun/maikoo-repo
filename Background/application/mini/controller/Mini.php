<?php

/**
 * 方特小程序微信数据相关
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
use app\index\model\Invite_code;
use app\index\model\Mini_click_count;
use app\index\model\Column_click_count;
use app\index\model\User_fav;
use app\index\model\Rate;

class Mini extends Controller{

    // 小程序APPID
    const APPID = "wxe8906a23ac34d51c";
    // 小程序APPSECRET
    const APPSECRET = "af3d0948de2660a2567cf2a1b34cceda";
    const DS = DIRECTORY_SEPARATOR;
    const SITEROOT = "https://minipro.up.maikoo.cn/public/";

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
     * 获取制定MiniId的小程序详情
     *
     * @param Request $request
     * @return void
     */
    public function getMini(Request $request){
        $miniId = intval($request -> param('miniId'));
        $userOpenid = $request -> param('openid');
        if (!$miniId) {
            return objReturn(200, 'Invaild Param');
        }
        $miniField = "appid, mini_id, path, name, avatarUrl, brief, pics, intro, views, keywords, create_time, catagory_id, rate, extra_data";
        $miniInfo = getMiniById($miniId, $miniField, false);
        if (!$miniInfo || count($miniInfo) == 0) {
            return objReturn(200, 'failed', $miniInfo);
        }
        // $miniInfo = $miniInfo[0];
        // 简单数据处理
        $miniInfo['avatarUrl'] = self::SITEROOT . $miniInfo['avatarUrl'];
        if (isset($miniInfo['pics'])) {
            $miniInfo['pics'] = explode('*', $miniInfo['pics']);
        }
        foreach ($miniInfo['pics'] as $k => $v) {
            $miniInfo['pics'][$k] = self::SITEROOT . $v;
        }
        if (isset($miniInfo['intro']) && $miniInfo['intro']) {
            $miniInfo['intro'] = htmlspecialchars($miniInfo['intro']);
        }

        $miniInfo['brief'] = htmlspecialchars_decode($miniInfo['brief']);
        $miniInfo['name'] = htmlspecialchars_decode($miniInfo['name']);
        $miniInfo['keywords'] = str_replace(",", " ", $miniInfo['keywords']);
        if (!empty($miniInfo['extra_data'])) {
            $extraData = htmlspecialchars_decode($miniInfo['extra_data']);
            // 构造extra_data eg: from=wxcpa&tag=qm43k-pfa1
            $extraData = explode("&", $extraData);
            // $miniInfo['test'] = $extraData;
            $miniInfo['extra_data'] = [];
            foreach ($extraData as $k => $v) {
                $temp = explode("=", $v);
                // $miniInfo['test'] []= $temp[0];
                $extraTemp = array();
                $extraTemp = array("$temp[0]" => $temp[1]);
                // $extraTemp[$temp[0]] = $temp[1];
                $miniInfo['extra_data'] = array_merge($miniInfo['extra_data'], $extraTemp);
            }
        }

        // 判断当前小程序用户是否有收藏
        $user_fav = new User_fav;
        $isUserFav = $user_fav -> where('user_openid', $userOpenid) -> where('fav_id', $miniId) -> where('fav_type', 1) -> where('is_fav', 1) -> field('idx') -> find();
        // dump($isUserFav); die;
        if ($isUserFav) {
            $miniInfo['isFav'] = true;
            $miniInfo['favIdx'] = $isUserFav['idx'];
        }else{
            $miniInfo['isFav'] = false;
        }

        // 判断当前用户是否有评价
        $rate = new Rate;
        $rateInfo = $rate -> where('mini_id', $miniInfo['mini_id']) -> where('appid', $miniInfo['appid']) -> where('user_openid', $userOpenid) -> field('rate') -> select();
        if ($rateInfo) {
            $rateInfo = collection($rateInfo) -> toArray();
            // dump($rateInfo);die;
            $miniInfo['user_rate'] = $rateInfo[0]['rate'];
        }else{
            $miniInfo['user_rate'] = false;
        }
        return objReturn(0, 'success', $miniInfo);
    }

}