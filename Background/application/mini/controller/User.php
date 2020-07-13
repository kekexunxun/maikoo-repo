<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use app\index\model\User_fav;
use app\index\model\User_fav_log;

class User extends Controller
{

    /**
     * 获取会员界面
     *
     * @return void
     */
    public function updateFav()
    {
        if (request()->isGet()) {
            return objReturn(400, 'Invaild Method');
        }
        $uid = intval(request()->param('uid'));
        $goodsId = intval(request()->param('goodsid'));
        $favAction = intval(request()->param('favaction'));
        if (!isset($uid) || !isset($goodsId)) {
            return objReturn(401, 'failed');
        }
        $user_fav = new User_fav;
        $user_fav_log = new User_fav_log;
        if ($favAction) {
            $updateFav = $user_fav->insert(['uid' => $uid, 'goods_id' => $goodsId, 'created_at' => time()]);
        } else {
            $updateFav = $user_fav->where('uid', $uid)->where('goods_id', $goodsId)->delete();
        }
        $insertFavLog = $user_fav_log->insert(['uid' => $uid, 'goods_id' => $goodsId, 'created_at' => time(), 'fav_action' => $favAction]);
        if ($updateFav) {
            return objReturn(0, 'success', $updateFav);
        } else {
            return objReturn(400, 'failed', $updateFav);
        }
    }

    /**
     * 获取用户收藏列表
     *
     * @return void
     */
    public function getUserFav()
    {
        if (request()->isGet()) {
            return objReturn(400, 'Invaild Method');
        }
        $uid = intval(request()->param('uid'));
        $pageNum = intval(request()->param('pageNum'));
        if (!isset($uid) || !isset($pageNum)) {
            return objReturn(401, 'failed');
        }
        $user_fav = new User_fav;
        $userFavList = $user_fav->alias('f')->join('mk_goods g', 'f.goods_id = g.goods_id', 'LEFT')->field('f.fav_id, g.goods_id, g.goods_name, g.goods_img, g.shop_price, g.market_price, g.keywords, g.unit')->where('f.uid', $uid)->where('g.status', 2)->limit($pageNum * 10, 10)->select();
        if (!$userFavList || count($userFavList) == 0) {
            return objReturn(0, 'no favList');
        }
        $userFavList = collection($userFavList)->toArray();
        foreach ($userFavList as &$info) {
            $info['goods_img'] = config('STATIC_SITE_PATH') . $info['goods_img'];
            $info['shop_price'] = number_format($info['shop_price'], 2);
            $info['market_price'] = number_format($info['market_price'], 2);
            $info['keywords'] = str_replace(',', ' ', htmlspecialchars_decode($info['keywords']));
            $info['unit'] = htmlspecialchars_decode($info['unit']);
            $info['goods_name'] = htmlspecialchars_decode($info['goods_name']);
        }
        return objReturn(0, 'success', $userFavList);
    }

}
