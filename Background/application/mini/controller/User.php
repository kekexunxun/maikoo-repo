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

use app\index\model\User_fav_log;
use app\index\model\User_fav;
use app\index\model\Rate;

class User extends Controller{

    // 小程序APPID
    const APPID = "wxe8906a23ac34d51c";
    // 小程序APPSECRET
    const APPSECRET = "af3d0948de2660a2567cf2a1b34cceda";
    const SITE_PIC_ROOT = "https://minipro.up.maikoo.cn/public";

    /**
     * 用户新增收藏
     * favType 1 小程序 2 专栏
     * 
     * @param Request $request
     * @return void
     */
    public function userAddFav(Request $request){
        $userOpenid = $request -> param('openid');
        $favType = $request -> param('favType');
        $favId = $request -> param('favId');
        $appid = $request -> param('appid');
        // 如果没有openid则返回
        if (!$userOpenid) {
            return objReturn(200, 'failed');
        }
        $user_fav = new User_fav;
        $user_fav_log = new User_fav_log;
        // 先判断当前数据库中是否已经含有对应的数据了
        $isHaveData = $user_fav -> where('user_openid', $userOpenid) -> where('fav_id', $favId) -> where('fav_type', $favType) -> where('is_fav', 0) -> select();
        $isHaveData = collection($isHaveData) -> toArray();
        if ($isHaveData && count($isHaveData) == 1) {
            $update = $user_fav -> update(['idx' => $isHaveData[0]['idx'], 'is_fav' => 1]);
            if ($update) {
                $insert = $user_fav_log -> insert(['user_openid' => $userOpenid, 'fav_id' => $favId, 'fav_type' => $favType, 'fav_action' => 1, 'create_time' => time()]);
                return objReturn(0, 'success', $isHaveData[0]['idx']);
            }else{
                return objReturn(200, 'failed');
            }
        }
        // 向数据库写入当前操作记录
        $insertId = $user_fav -> insertGetId(['user_openid' => $userOpenid, 'fav_id' => $favId, 'fav_type' => $favType]);
        if ($insertId) {
            // 更新user_fav_log表
            $insert = $user_fav_log -> insert(['user_openid' => $userOpenid, 'fav_id' => $favId, 'fav_type' => $favType, 'fav_action' => 1, 'create_time' => time()]);
            if ($insert) {
                return objReturn(0, 'success', $insertId);
            }else{
                $update = $user_fav -> update(['idx' => $idx, 'is_fav' => 0]);
                return objReturn(200, 'failed');
            }
        }else{
            return objReturn(200, 'failed');
        }
        
    }

    // /**
    //  * 检测用户是否可以添加收藏
    //  * 如果当前缓存中没有该favId 则返回false表明可以新增
    //  * 如果当前缓存中有favId 则返回对应$va 包含 favId 收藏的Id favIdx 数据库中对应的索引idx isFav 是否处于收藏状态
    //  * 
    //  * @return void
    //  */
    // public function checkUserFavCache($userOpenid, $favType, $favId){
    //     $userFavCache = Cache::get('userFavCache');
    //     if (!$userFavCache || count($userFavCache) == 0) {
    //         return false;
    //     }
    //     foreach ($userFavCache as $k => $v) {
    //         if ($v['openid'] == $userOpenid) {
    //             if ($favType == 1 && isset($v['mini'])) {
    //                 foreach ($v['mini'] as $ke => $va) {
    //                     if ($va['favId'] == $favId) {
    //                         return $va;
    //                     }
    //                 }
    //                 return false;
    //             }
    //             if ($favType == 2 && isset($v['mini'])) {
    //                 foreach ($v['mini'] as $ke => $va) {
    //                     if ($va['favId'] == $favId) {
    //                         return $va['favIdx'];
    //                     }
    //                 }
    //                 return false;
    //             }
    //             break;
    //         }
    //     }
    //     return false;
    // }


    /**
     * 获取用户的收藏列表
     *
     * @param int $favType 获取收藏的类型 1 小程序 2 专栏
     * @return void
     */
    public function getUserFav(Request $request){
        $userOpenid = $request -> param('openid');
        $favType = intval($request -> param('favType'));
        $pageNum = intval($request -> param('pageNum'));
        $favField = "idx, fav_id, fav_type";
        $userFav = getUserFavList($userOpenid, $favField, 1, false, $pageNum);
        if (!$userFav && count($userFav) == 0) {
            return objReturn(0, 'success');
        }
        // 获取所有的小程序列表然后判断
        $miniField = "mini_id, appid, name, avatarUrl, catagory_id, keywords";
        $allMini = getAllMini($miniField, false);
        if (!$allMini) {
            return objReturn(201, 'No Mini Exist');
        }
        foreach ($userFav as $k => $v) {
            // 新增一个isFav 便于后续操作
            foreach ($allMini as $ke => $va) {
                if ($v['fav_id'] == $va['mini_id']) {
                    // 对keywords做简单处理
                    $va['keywords'] = str_replace(",", " ", $va['keywords']);
                    // 对小程序头像做简单处理
                    // $va['avatarUrl'] = self::SITE_PIC_ROOT . $va['avatarUrl'];
                    $userFav[$k]['mini'] = $va;
                    $userFav[$k]['isFav'] = true;
                    break 1;
                }
            }
        }
        return objReturn(0, 'success', $userFav);
    }

    /**
     * 用户取消收藏操作
     *
     * @return json
     */
    public function userCancelFav(Request $request){
        $userOpenid = $request -> param('openid');
        $favId = intval($request -> param('favId'));
        $favType = intval($request -> param('favType'));
        $idx = intval($request -> param('idx'));
        if (!$idx) {
            return objReturn(200, 'failed');
        }
        // 更新user_fav表
        $user_fav = new User_fav;
        $update = $user_fav -> update(['idx' => $idx, 'is_fav' => 0]);
        // dump($update);die;
        if ($update) {
            // 更新user_fav_log表
            $user_fav_log = new User_fav_log;
            $insert = $user_fav_log -> insert(['user_openid' => $userOpenid, 'fav_id' => $favId, 'fav_type' => $favType, 'fav_action' => 0, 'create_time' => time()]);
            if ($insert) {
                return objReturn(0, 'success');
            }else{
                $update = $user_fav -> update(['idx' => $idx, 'is_fav' => 1]);
                return objReturn(200, 'failed');
            }
        }else{
            return objReturn(200, 'failed');
        }
    }

    /**
     * 用户进行小程序的评分
     *
     * @param Request $request
     * @return json 是否评价成功
     */
    public function submitRate(Request $request){
        $userOpenid = $request -> param('openid');
        $appid = $request -> param('appid');
        $miniId = $request -> param('miniId');
        $userRate = intval($request -> param('rate'));
        $rate = new Rate;
        $insert = $rate -> insert(['mini_id' => $miniId, 'appid' => $appid, 'user_openid' => $userOpenid, 'create_time' => time(), 'rate' => $userRate]);
        if ($insert) {
            return objReturn(0, 'success');
        }else{
            return objReturn(200, 'failed');
        }
    }


}