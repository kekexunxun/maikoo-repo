<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use app\index\model\Catagory as CatagoryDB;

class Catagory extends Controller
{

    /**
     * 获取商城首页列表数据
     *
     * @return void
     */
    public function getCatagoryList()
    {
        if (request()->isGet()) {
            return objReturn(400, 'Invaild Method');
        }
        $uid = intval(request()->param('uid'));
        if (!isset($uid)) {
            return objReturn(401, 'Invaild Param');
        }
        $catagoryList = getCatagory(false);
        if (!$catagoryList) {
            return objReturn(0, 'success');
        }
        // 构造分类展示列表
        // 1 获取全部父级分类
        $catList = [];
        foreach ($catagoryList as &$info) {
            $info['cname'] = htmlspecialchars($info['cname']);
            if ($info['parent_id'] == 0) {
                unset($info['img']);
                $info['list'] = [];
                $catList[] = $info;
            }
        }
        // 2 构造二级分类
        foreach ($catList as &$info) {
            foreach ($catagoryList as $k => $v) {
                if ($info['cat_id'] == $v['parent_id']) {
                    $v['img'] = $v['img'];
                    $info['list'][] = $v;
                }
            }
        }
        return objReturn(0, 'success', $catList);
    }

    /**
     * 通过分类ID获取分类中的商品详情
     *
     * @return void
     */
    public function getCatagorySpec()
    {
        $catId = intval(request()->param('catid'));
        $uid = intval(request()->param('uid'));
        $pageNum = intval(request()->param('pageNum'));
        if (!isset($catId) || !isset($uid) || !isset($pageNum)) {
            return objReturn(401, 'Invaild Param');
        }
        // 获取cat name
        $catagory = new CatagoryDB;
        $catagoryName = $catagory->where('cat_id', $catId)->value('cname');
        $goodsList = getCatagoryById($catId, null, false, $pageNum);
        // 构造返回数据
        $data['info'] = $catagoryName;
        $data['goodsList'] = $goodsList ? $goodsList : [];
        return objReturn(0, 'success', $data);
    }
}
