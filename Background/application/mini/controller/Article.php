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

class Article extends Controller{

    /**
     * 获取制定类型的文章列表
     *
     * @param Request $request
     * @return void
     */
    public function getArticle(Request $request){
        $articleType = intval($request -> param('articleType'));
        $subId = $request -> param('subId');
        $pageNum = intval($request -> param('pageNum'));
        $articleField = "article_id, title, author, views, create_time, pic, brief";
        $articleList = getArticleList($articleType, $articleField, false, $subId, $pageNum);
        return objReturn(0, "success", $articleList);
    }

    /**
     * 获取资讯的列表 和 前10条资讯
     *
     * @param Request $request
     * @return void
     */
    public function getArticleInfo(Request $request){
        
        // 先回去所有的分类
        $catField = "cat_id, name";
        $allArticleCat = getArticleCat($catField, false);
        // 获取全部的资讯文章
        // 此时要获取全部 就subId 传 all
        $allArticleList = getArticleList(1, null, false, 'all', 0);
        // 将获取到的数据增加一个pageNum
        if ($allArticleList) {
            foreach ($allArticleCat as $k => $v) {
                $allArticleCat[$k]['pageNum'] = 0;
            }
            $all['cat_id'] = "all";
            $all['name'] = "全部";
            $all['pageNum'] = 1;
            $all['list'] = $allArticleList;
            array_unshift($allArticleCat, $all);
        }
        
        return objReturn(0, 'success', $allArticleCat);
    }

    /**
     * 获取制定Id的文章详情
     *
     * @param Request $request
     * @return void
     */
    public function getArticleSpec(Request $request){
        $userOpenid = $request -> param('openid');
        $articleId = intval($request -> param('articleId'));
        if (empty($articleId)) {
            return objReturn(200, 'Invaild Param');
        }
        // 数据查询
        $articleSpec = getArticleById($articleId, null, true);
        return objReturn(0, 'success', $articleSpec);
    }

}