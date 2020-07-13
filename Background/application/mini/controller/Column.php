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

class Column extends Controller{

    // 小程序APPID
    const APPID = "wxe8906a23ac34d51c";
    // 小程序APPSECRET
    const APPSECRET = "af3d0948de2660a2567cf2a1b34cceda";
    const SITEROOT = "https://minipro.up.maikoo.cn/public";

    /**
     * 分页获取小程序专栏列表
     * 
     * @param Request $request
     * @return void
     */
    public function getColumnList(Request $request){
        $pageNum = intval($request -> param('pageNum'));
        if (!$pageNum) {
            return objReturn(200, 'Invaild Page');
        }
        $field = "idx, pic";
        $columnList = getAllColumn($field, false, null, $pageNum);
        // 对columnList做简单的数据处理
        foreach ($columnList as $k => $v) {
            $columnList[$k]['pic'] = self::SITEROOT . $v['pic'];
            // foreach ($v['minis'] as $ke => $va) {
            //     $columnList[$k]['minis'][$ke]['avatarUrl'] = self::SITE_MiNI_ROOT . $va['avatarUrl'];
            // }
        }
        return objReturn(0, 'success', $columnList);
    }

    /**
     * 获取单个专栏的详情
     *
     * @param Request $request
     * @return void
     */
    public function getColumnInfo(Request $request){
        // Cache::rm('userFav');
        $columnId = intval($request -> param('columnid'));
        $userOpenid = $request -> param('openid');
        if (!$columnId) {
            return objReturn(200, 'Invaild ColumnID');
        }
        $columnField = "idx, name, brief, pic, minis, views, create_time";
        $miniField = "mini_id, appid, name, avatarUrl, brief, views, catagory_id, create_time, rate";
        $columnInfo = getColumnById($columnId, $columnField, $miniField);
        // 简单处理
        $columnInfo['pic'] = self::SITEROOT . $columnInfo['pic'];
        // dump(Cache::get('userFavFlushTime'));die;

        // 获取用户收藏列表
        // 判断当前专栏中是否有用户收藏的小程序
        $favField = "idx, fav_id, fav_type";
        $userFav = getUserFavList($userOpenid, $favField, 0, false, 0);
        // 给当前专栏是否被用户收藏做初始值
        $columnInfo['isFav'] = false;
        if ($userFav && count($userFav) > 0) {
            foreach ($columnInfo['minis'] as $k => $v) {
                $columnInfo['minis'][$k]['isFav'] = false;
                foreach ($userFav as $ke => $va) {
                    // 专栏中小程序收藏判断
                    if ($va['fav_type'] == 1 && $va['fav_id'] == $v['mini_id']) {
                        $columnInfo['minis'][$k]['isFav'] = true;
                        $columnInfo['minis'][$k]['favIdx'] = $va['idx'];
                        break 1;
                    }
                    // 专栏收藏判断
                    if ($va['fav_type'] == 2 && $va['fav_id'] == $columnId) {
                        $columnInfo['isFav'] = true;
                        $columnInfo['favIdx'] = $va['idx'];
                        break 1;
                    }
                }
            }
        }
        // 判断该专栏是否被用户收藏

        return objReturn(0, 'success', $columnInfo);
    }


}