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

class Search extends Controller{

    // 小程序APPID
    const APPID = "wxe8906a23ac34d51c";
    // 小程序APPSECRET
    const APPSECRET = "af3d0948de2660a2567cf2a1b34cceda";
    const SITEROOT = "https://minipro.up.maikoo.cn";

    /**
     * 分页获取小程序专栏列表
     * 
     * @param Request $request
     * @return void
     */
    public function getList(Request $request){
        $searchField = "idx, mini_id";
        $searchList = getSearchList($searchField, false);
        return objReturn(0, 'success', $searchList);
    }

    /**
     * 获取搜索结果
     * 
     * @param string $value 搜索的关键词
     * @param Request $request
     * @return void
     */
    public function getSearchReasult(Request $request){
        $value = htmlspecialchars($request -> param('value'));

        // 获取mini缓存
        $allMini = Cache::get('miniSearchCache');
        if (!$allMini) {
            $miniField = "mini_id, appid, path, name, avatarUrl, keywords, views";
            $allMini = getAllMini($miniField, false);
            if (!$allMini || count($allMini) == 0) {
                return objReturn(200, 'No Mini Exist');
            }
            // 保存该缓存十分钟
            Cache::set('miniSearchCache', $allMini, 600);
        }

        // 构造返回数据
        $result = [];
        // 获取到所有mini之后去判断
        // 字段匹配时 与name、keywords的相似度只要到达50% 都可被认定为相关数据
        foreach ($allMini as $k => $v) {
            $isSimiliar = false;
            // 做一次name匹配
            similar_text($v['name'], $value, $percent);
            if ($percent > 50 || strstr($v['name'], $value)) {
                $isSimiliar = true;
            }
            if (!$isSimiliar && !empty($v['keywords'])) {
                // 做一次keywords匹配
                if (strstr($v['keywords'], $value)) {
                    $isSimiliar = true;
                }else{
                    $keywords = explode(",", $v['keywords']);
                    foreach ($keywords as $ke => $va) {
                        similar_text($va, $value, $percent);
                        if ($percent > 50) {
                            $isSimiliar = true;
                            break 1;
                        }
                    }
                }
                
            }
            if ($isSimiliar) {
                // $v['avatarUrl'] = "https://minipro.up.maikoo.cn" . $v['avatarUrl'];
                $v['keywords'] = str_replace(",", " ", $v['keywords']);
                $v['name'] = htmlspecialchars_decode($v['name']);
                $result []= $v;
            }
        }
        return objReturn(0, 'success', $result);
    }

    public function test(){
        dump(config('minipro'));
    }

}