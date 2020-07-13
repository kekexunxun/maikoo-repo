<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;

use app\index\model\User_fav;

class Goods extends Controller
{

    /**
     * 获取商品ID
     *
     * @param int $goodsid 商品ID
     * @param int $uid 用户ID
     * @return void
     */
    public function getGoodsInfo()
    {
        $goodsId = intval(request() -> param('goodsid'));
        $uid = intval(request() -> param('uid'));
        if (!isset($uid) || !isset($goodsId)) {
            return objReturn(401, 'failed');
        }

        $goodsInfo = getGoodsById($goodsId, false);
        if(!$goodsInfo){
            return objReturn(400, 'No Goods');
        }
        // 判断用户是否有收藏当前商品
        $user_fav = new User_fav;
        $isFav = $user_fav -> where('uid', $uid) -> where('goods_id', $goodsId) -> count();
        $goodsInfo['isFav'] = $isFav == 1 ? true : false;

        return objReturn(0, 'success', $goodsInfo);
    }

}
