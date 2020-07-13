<?php

/**
 * 小程序商店排行榜获取
 * @author Locked
 * createtime 2018-03-06
 */

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\Session;

class Rank extends Controller{

    public function getRankList(Request $request){

        $userOpenid = $request -> param('openid');
        $pageNum = intval($request -> param('pageNum'));
        $rankList = getRank(false, $pageNum);
        // 可以增加判断当前列表用户是否收藏
        return objReturn(0, "success", $rankList);
    }

}