<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use app\index\model\Goods;

class Search extends Controller
{

    /**
     * 获取搜索关键词列表
     *
     * @return obj
     */
    public function getList()
    {
        $uid = request()->param('uid');
        if (empty($uid)) {
            return objReturn(401, 'failed');
        }
        $searchList = getSearchValue(false);
        return objReturn(0, 'success', $searchList);
    }

    public function getSearchReasult()
    {
        $value = htmlspecialchars(request()->param('value'));
        if (empty($value)) {
            return objReturn(401, 'failed');
        }
        // 将所有商品先取出放到缓存，搜索时查询缓存提升效率
        $goodsList = Cache::get('goodsList');
        if (!$goodsList) {
            $goods = new Goods;
            $field = "goods_id, goods_sn, goods_name, goods_img, shop_price, market_price, keywords, unit";
            $goodsList = $goods->where('status', 2)->field($field)->order('sort desc')->select();
            if ($goodsList && count($goodsList) > 0) {
                $goodsList = collection($goodsList)->toArray();
                Cache::set('goodsList', $goodsList);
            } else {
                $goodsList = null;
            }
        }
        if (!$goodsList) {
            return objReturn(0, 'No Goods Exist');
        }
        $searchResult = [];
        // 搜索结果 name匹配度40% 或该value需在keyword中存在
        foreach ($goodsList as $k => $v) {
            $isOK = false;
            $v['goods_name'] = htmlspecialchars($v['goods_name']);
            similar_text($v['goods_name'], $value, $percent);
            if ($percent > 40) {
                $isOK = true;
            }
            if (!$isOK && strstr($v['keywords'], $value)) {
                $isOK = true;
            }
            if ($isOK) {
                $v['keywords'] = str_replace(',', ' ', $v['keywords']);
                $v['goods_img'] = config('STATIC_SITE_PATH') . $v['goods_img'];
                $v['unit'] = htmlspecialchars($v['unit']);
                $v['shop_price'] = number_format($v['shop_price'], 2);
                $v['market_price'] = number_format($v['market_price'], 2);
                $searchResult []= $v;
            }
        }
        return objReturn(0, 'success', $searchResult);
    }
}
