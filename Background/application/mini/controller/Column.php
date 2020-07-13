<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use app\index\model\Column_goods;

class Column extends Controller
{

    /**
     * 获取会员版块界面
     *
     * @return void
     */
    public function getMemColumn()
    {
        if (request()->isGet()) {
            return objReturn(400, 'Invaild Method');
        }
        $uid = intval(request()->param('uid'));
        if (!isset($uid)) {
            return objReturn(401, 'failed');
        }
        $columnField = "column_id";
        $columnList = getColumn($columnField, false, 2);
        if (!$columnList) {
            return objReturn(0, 'No Column');
        }
        foreach ($columnList as &$info) {
            $columnInfo = getColumnById($info['column_id'], false, 6);
            if ($columnInfo) {
                $info = $columnInfo;
            }
        }
        return objReturn(0, 'success', $columnList);
    }

    /**
     * 获取专栏中的商品信息
     *
     * @return void
     */
    public function getColumnGoods()
    {
        if (!request()->isPost()) {
            return objReturn(400, 'Invaild Method');
        }
        $uid = intval(request()->param('uid'));
        $columnId = intval(request()->param('columnid'));
        $pageNum = intval(request()->param('pageNum'));
        if (!isset($uid)) {
            return objReturn(401, 'failed');
        }
        // 如果传回来的pageNum == 0 就直接调用公共方法
        if ($pageNum == 0) {
            $columnInfo = getColumnById($columnId, false, 10);
            if (!$columnInfo) return objReturn(400, 'failed');
            // 将columnInfo 和 columnGoodsList 区分开
            $data['goodsList'] = $columnInfo['goods'];
            unset($columnInfo['goods']);
            $data['info'] = $columnInfo; 
            return objReturn(0, 'success', $data);
        }
        // 如果传回来的pageNum != 0 那么就只获取商品即可
        $column_goods = new Column_goods;
        $columnGoodsList = $column_goods->alias('c')->join('sm_goods g', 'c.goods_id = g.goods_id', 'LEFT')->where('c.column_id', $columnId)->where('c.status', 1)->field('c.idx, c.goods_id, c.sort, c.status, g.goods_sn, g.goods_name, g.goods_img, g.market_price, g.shop_price, g.keywords, g.unit')->order('c.sort desc')->limit($pageNum * 10, 10)->select();
        if (!$columnGoodsList || count($columnGoodsList) == 0) {
            return objReturn(0, 'No More Goods');
        }
        $columnGoodsList = collection($columnGoodsList)->toArray();
        foreach ($columnGoodsList as &$info) {
            $info['goods_name'] = htmlspecialchars_decode($info['goods_name']);
            $info['unit'] = htmlspecialchars_decode($info['unit']);
            $info['goods_img'] = config('STATIC_SITE_PATH') . $info['goods_img'];
            $info['market_price'] = number_format($info['market_price'], 2);
            $info['shop_price'] = number_format($info['shop_price'], 2);
            $info['keywords'] = str_replace(',', ' ', $info['keywords']);
        }
        // 构造goodsList
        $data['goodsList'] = $columnGoodsList;
        return objReturn(0, 'success', $data);
    }

}
