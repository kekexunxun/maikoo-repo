<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use app\index\model\Goods;

class Cart extends Controller
{
    /**
     * 获取用户购物车数据
     *
     * @param string $openid 用户openid
     * @return obj 用户购物车数据
     */
    public function getCart()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $openid = request()->param('openid');
        if (empty($openid)) return objReturn(401, 'Invaild Param');
        $cartList = Cache::get('cartList');
        if (!$cartList) return objReturn(0, 'No Cart Exist');
        $cartGoodsList = null;
        foreach ($cartList as &$info) {
            if ($info['openid'] == $openid) {
                $cartGoodsList = $info['goodsInfo'];
                break;
            }
        }
        if (!$cartGoodsList) return objReturn(0, 'No Goods Left');
        // 构造goodsIdx去查询
        $goodsIdx = [];
        foreach ($cartGoodsList as &$cartGoods) {
            $goodsIdx[] = $cartGoods['goodsid'];
        }
        // 判断是否有商品无效
        $goods = new Goods;
        $goodsList = $goods->where('goods_id', 'in', $goodsIdx)->field('goods_id, goods_sn, goods_name, goods_img, market_price, shop_price, sales_num, keywords, unit, points, status')->order('sort desc')->select();
        if (!$goodsList || count($goodsList) == 0) return objReturn(501, 'Goods Not Found');
        $goodsList = collection($goodsList)->toArray();
        // 是否所有原在购物车的商品都可以找到对应的详情
        $isAllGoodsFixed = [];
        $isHaveGoodsChange = false;
        foreach ($cartGoodsList as &$cartGoods) {
            foreach ($goodsList as &$good) {
                if ($cartGoods['goodsid'] == $good['goods_id']) {
                    $isAllGoodsFixed[] = 1;
                    $good['goods_name'] = htmlspecialchars_decode($good['goods_name']);
                    $good['unit'] = htmlspecialchars_decode($good['unit']);
                    $good['goods_name'] = str_replace(',', ' ', htmlspecialchars_decode($good['keywords']));
                    $good['market_price'] = number_format($good['market_price'], 2);
                    $good['shop_price'] = number_format($good['shop_price'], 2);
                    $good['goods_img'] = config('STATIC_SITE_PATH') . $good['goods_img'];
                    unset($goods['goods_id']);
                    if ($good['status'] != 2) {
                        $isHaveGoodsChange = true;
                    }
                    $cartGoods = array_merge($cartGoods, $good);
                    break 1;
                }
            }
        }

        if (!$isHaveGoodsChange) $isHaveGoodsChange = count($cartGoodsList) == count($isAllGoodsFixed) ? false : true;

        $data['isHaveGoodsChange'] = $isHaveGoodsChange;
        $data['cartList'] = $cartGoodsList;

        return objReturn(0, 'success', $data);
    }

    /**
     * 用户新增购物车数据
     *
     * @param string $openid 用户openid
     * @param int $goodsid 商品id
     * @return obj 是否新增成功
     */
    public function addToCart()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $openid = request()->param('openid');
        $goodsid = request()->param('goodsid');
        if (empty($openid) || empty($goodsid)) return objReturn(401, 'Invaild Param');
        $cartList = Cache::get('cartList');
        $isFindUser = false;
        if ($cartList) {
            foreach ($cartList as &$info) {
                if ($info['openid'] == $openid) {
                    $isFindUser = true;
                    $isFindGoods = false;
                    foreach ($info['goodsInfo'] as &$goods) {
                        if ($goods['goodsid'] == $goodsid) {
                            $isFindGoods = true;
                            $goods['quantity'] += 1;
                            break 1;
                        }
                    }
                    if (!$isFindGoods) {
                        $temp['goodsid'] = $goodsid;
                        $temp['quantity'] = 1;
                        $temp['created_at'] = time();
                        $info['goodsInfo'][] = $temp;
                    }
                    break 1;
                }
            }
        } else {
            $cartList = [];
        }
        if (!$cartList || !$isFindUser) {
            $temp['openid'] = $openid;
            $temp['goodsInfo'] = [];
            $goodsTemp['goodsid'] = $goodsid;
            $goodsTemp['quantity'] = 1;
            $goodsTemp['created_at'] = time();
            $temp['goodsInfo'][] = $goodsTemp;
            $cartList[] = $temp;
        }
        Cache::set('cartList', $cartList);
        return objReturn(0, 'success', $cartList);
    }

    /**
     * 更新用户购物车数据
     *
     * @return void
     */
    public function updateUserCart()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $openid = request()->param('openid');
        $goodsInfo = request()->param('goodsInfo/a');
        if (empty($openid) || empty($goodsInfo))  return objReturn(401, 'Invaild Param');
        $cartList = Cache::get('cartList');
        foreach ($cartList as &$info) {
            if ($info['openid'] == $openid) {
                $info['goodsInfo'] = $goodsInfo;
                break;
            }
        }
        Cache::set('cartList', $cartList);
        return objReturn(0, 'success');
    }

    public function test()
    {
        dump(Cache::get('cartList'));
    }

}
