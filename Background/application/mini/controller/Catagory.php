<?php

/**
 * 小程序商城 分类相关
 * @author Locked
 * createtime 2018-06-28
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

class Catagory extends Controller{

    // 小程序APPID
    const APPID = "wxe8906a23ac34d51c";
    // 小程序APPSECRET
    const APPSECRET = "af3d0948de2660a2567cf2a1b34cceda";
    const SITEROOT = "https://minipro.up.maikoo.cn";

    /**
     * 分页获取小程序分类列表并判断当前小程序是否被用户收藏
     * 
     * @param Request $request
     * @return void
     */
    public function getUserCatagory(Request $request){
        $catId = $request -> param('catId');
        $userOpenid = $request -> param('openid');
        $catField = "catagory_id, father_id, name";
        $miniField = "mini_id, appid, avatarUrl, path, name, views, keywords, catagory_id";
        $catInfo = getCatagoryById($catId, $catField, $miniField, true, $userOpenid);
        return objReturn(0, 'success', $catInfo);
    }

    /**
     * 获取所有非父级分类列表
     * 用于用户TAB选择分类
     *
     * @return void
     */
    public function getCatList(){
        $catField = "catagory_id, father_id, name, pic";
        $catList = getAllCatagory($catField, false);
        if (!$catList) {
            return objReturn(200, 'wrong cat');
        }
        $sonCat = [];
        foreach ($catList as $k => $v) {
            if ($v['father_id'] != 0) {
                $v['pic'] = "https://minipro.up.maikoo.cn/public" . $v['pic'];
                $sonCat []= $v;
            }
        }
        // 将分类随机打乱
        shuffle($sonCat);
        return objReturn(0, 'success', $sonCat);
    }

    public function test(){
        $asdsad = '123123123123';
        print_r(explode('*', $asdsad));
    }


}