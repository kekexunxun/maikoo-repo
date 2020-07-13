<?php

/**
 * 打印店小程序后台处理相关
 * @author Locked
 * createtime 2018-03-06
 */

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\Session;
use think\File;

use app\index\model\Userinfo;
use app\index\model\Bannerlist;
use app\index\model\Column;
use app\index\model\Catagory;
use app\index\model\Minipro;
use app\index\model\Column_click_count;
use app\index\model\Update_time;
use app\index\model\Mini_click_count;
use app\index\model\Article_click_count;
use app\index\model\Article;

class Shop extends Controller{
    
    const APPID = "wxe8906a23ac34d51c";
    const APPSECRET = "af3d0948de2660a2567cf2a1b34cceda";
    const DS = DIRECTORY_SEPARATOR;

    /**
     * 获取小程序初始化信息
     *
     * @return void
     */
    public function getShopInfo(){
        // 获取Banner
        $bannerlist = new Bannerlist;
        $banner = $bannerlist -> where('is_active', 1) -> where('is_delete', 0) -> order('orderby desc') -> field('idx, pic, navigate, navigate_name, navigate_id') -> select();
        // 对拿到的Banner列表做处理
        $siteroot = "https://minipro.up.maikoo.cn/public";
        $miniBanner = array();
        if ($banner && sizeof($banner) > 0) {
            foreach ($banner as $k => $v) {
                $v['pic']= $siteroot . $v['pic'];
                $miniBanner []= $v;
            }
        }

        // 2 获取前6个专题
        $column = new Column;
        $miniColumn = array();
        $columnList = $column -> where('is_delete', 0) -> where('is_active', 1) -> field('idx, pic') -> limit(6) -> select();
        if ($columnList && sizeof($columnList) > 0) {
            foreach ($columnList as $k => $v) {
                $v['pic'] = $siteroot . $v['pic'];
                $miniColumn []= $v;
            }
        }

        // 3 获取所有父级分类 展示所有子分类排名前2的小程序信息
        $catagory = new Catagory;
        $miniCatagory = array();
        $catagoryList = $catagory -> where('is_delete', 0) -> where('is_active', 1) -> field('catagory_id, father_id, name') -> order('orderby desc') -> select();
        $fatherCat = array();
        $sonCat = array();
        foreach ($catagoryList as $k => $v) {
            // 如果父级ID为0 则是一个顶级目录
            if ($v['father_id'] == 0) {
                $fatherCat []= $v;
            }else{
                $temp['minis'] = $this -> getMiniPro(null, $v['catagory_id']);
                $temp['father_id'] = $v['father_id'];
                $sonCat []= $temp;
            }
        }
        // dump($fatherCat);die;
        // 将所有子级目录查询到的小程序整合到父级目录
        foreach ($fatherCat as $k => $v) {
            $v['minis'] = array();
            foreach ($sonCat as $ke => $va) {
                if ($va['father_id'] == $v['catagory_id'] && $va['minis']) {
                    $v['minis'] = array_merge($v['minis'], $va['minis']);
                }
            }
        }

        // 构造返回数据
        $res['banner'] = $miniBanner;
        $res['catagory'] = $fatherCat;
        $res['column'] = $miniColumn;

        // 检测是否需要更新系统信息
        // $this -> checkUpdateSystemData();

        return json_encode($res);
    }

    /**
     * 获取小程序的信息
     * 可以通过小程序的ID获取或者通过目录ID获取
     * 
     * @param int $miniId 小程序ID
     * @param int $catagoryId 分类ID
     * @param int $limit 每次获取的小程序数量限制，当$catagoryId存在时才有效
     * @return array $mini 小程序个体或者小程序信息合集
     */
    public function getMiniPro($miniId = null, $catagoryId = null, $limit = 4){
        
        $siteroot = 'https://minipro.up.maikoo.cn/public';
        // 获取当前小程序的缓存  或者直接查询
        $miniProList = Cache::get('miniProList');
        if (!$miniProList) {
            $minipro = new Minipro;
            $miniProList = $minipro -> where('is_delete', 0) -> where('is_active', 1) -> field('mini_id, appid, name, avatarUrl, brief, pics, intro, views, catagory_id, create_time, is_openable') -> select();
            // 如果当前查询没有查询到数据 就直接返回
            if (!$miniProList) {
                return null;
            }
            // 后期可规定这个缓存的有效时间为300s 或者 600s左右
            Cache::set('miniProList', $miniProList, 5);
        }

        // 肯定只会传一个参数过来，要么找单个，要么找一个分类，当然还有可能找一个列表（这个后期再说）
        // 判断是否是查找指定的小程序
        if ($miniId) {
            foreach ($miniProList as $k => $v) {
                if ($v['mini_id'] == $miniId) {
                    // $v['create_time_convert'] = date('Y-m-d H:i:s', $v['create_time']);
                    // $miniProList[$k]['avatarUrl'] = $siteroot . $v['avatarUrl'];
                    return $v;
                }
            }
            return null;
        }

        // 判断是否是查找目录 此时$limit 有效
        if ($catagoryId) {
            // 查询数量非空判断
            if (intval($limit) <= 0) {
                return null;
            }
            $catMini = array();
            $count = 0;
            foreach ($miniProList as $k => $v) {
                if ($v['catagory_id'] == $catagoryId) {
                    // $v['create_time_convert'] = date('Y-m-d H:i:s', $v['create_time']);
                    $v['avatarUrl'] = $siteroot . $v['avatarUrl'];
                    $catMini []= $v;
                    $count++;
                    // 如果查询到指定的数量 直接结束
                    if ($limit && $count == $limit) {
                        break;
                    }
                }
            }
            // 数据返回
            return sizeof($catMini) > 0 ? $catMini : null;
        }

        // 如果不传任何参数 直接返回所有数据
        return $miniProList;
    }
    
    /**
     * 检测当前系统数据是否需要更新
     * 1. 小程序 views
     * 2. 专栏 views
     * 3. 分类 views
     * 4. 文章 views
     * @return void
     */
    public function checkUpdateSystemData(){
        $updateCache = Cache::get('updateCache');
        // 如果当前缓存存在，就不更新
        if ($updateCache) {
            return "null";
        }
        // 如果当前缓存不存在，就去更新
        // 初始化更新数组
        $updateColumnArr = array();
        $updateMiniArr = array();
        $updateArticleArr = array();
        // 先获取上一次的updateTime
        $update_time = new Update_time;
        $lastUpdateTime = $update_time -> limit(1) -> order('create_time desc') -> select();
        // 获取当前时间
        $currentTime = time();
        // 1 更新column的views
        $column_click_count = new Column_click_count;
        $columnStartTime = isset($lastUpdateTime['column_update_time']) ? $lastUpdateTime['column_update_time'] : strtotime("2018-05-01");
        $columnClick = $column_click_count -> where('click_time', 'between', [$columnStartTime, $currentTime]) -> field('column_id') -> select();
        // dump($columnClick);die;
        if ($columnClick && count($columnClick) > 0) {
            $columnClick = collection($columnClick) -> toArray();
            // 计算重复次数 构造Values
            $columnValues = array();
            foreach ($columnClick as $k => $v) {
                $columnValues []= $v['column_id'];
            }
            $columnClickCount = array_count_values($columnValues);
            // 完善更新数组
            // 从column中取出对应column_id的view 做相加操作
            $columnIds = array_keys($columnClickCount);
            $column = new Column;
            $oriColunmViews = $column -> where('idx', 'in', $columnIds) -> field('idx, views') -> select();
            $oriColunmViews = collection($oriColunmViews) -> toArray();
            $temp = array();
            foreach ($columnClickCount as $k => $v) {
                foreach ($oriColunmViews as $ke => $va) {
                    if ($va['idx'] == $k) {
                        $temp['idx'] = $k;
                        $temp['views'] = $v + $va['views'];
                        $updateColumnArr []= $temp;
                        break 1;
                    }
                }
            }
            // dump($updateColumnArr);die;
            $column -> update($updateColumnArr);
        }
        // 2 更新minis的views
        $mini_click_count = new Mini_click_count;
        $miniStartTime = isset($lastUpdateTime['mini_update_time']) ? $lastUpdateTime['mini_update_time'] : strtotime("2018-05-01");
        $miniClick = $mini_click_count -> where('click_time', 'between', [$miniStartTime, $currentTime]) -> field('mini_id') -> select();
        if ($miniClick && count($miniClick) > 0) {
            $miniClick = collection($miniClick) -> toArray();
            // 计算重复次数 构造Values
            $miniValues = array();
            foreach ($miniClick as $k => $v) {
                $miniValues []= $v['mini_id'];
            }
            $miniClickCount = array_count_values($miniValues);
            // 完善更新数组
            // 从minipro中取出对应mini_id的view 做相加操作
            $miniIds = array_keys($miniClickCount);
            $minipro = new Minipro;
            $oriMiniViews = $minipro -> where('mini_id', 'in', $miniIds) -> field('mini_id, views') -> select();
            $oriMiniViews = collection($oriMiniViews) -> toArray();
            $temp = array();
            foreach ($miniClickCount as $k => $v) {
                foreach ($oriMiniViews as $ke => $va) {
                    if ($va['mini_id'] == $k) {
                        $temp['mini_id'] = $k;
                        $temp['views'] = $v * rand(1,9) + $va['views'];
                        $updateMiniArr []= $temp;
                        break 1;
                    }
                }
            }
            $minipro -> update($updateMiniArr);
        }
        // 3 更新article的views
        $article_click_count = new Article_click_count;
        $articleStartTime = isset($lastUpdateTime['article_update_time']) ? $lastUpdateTime['article_update_time'] : strtotime("2018-05-01");
        $articleClick = $article_click_count -> where('click_time', 'between', [$articleStartTime, $currentTime]) -> field('article_id') -> select();
        if ($articleClick && count($articleClick) > 0) {
            $articleClick = collection($articleClick) -> toArray();
            // 计算重复次数 构造Values
            $articleValues = array();
            foreach ($articleClick as $k => $v) {
                $articleValues []= $v['article_id'];
            }
            $articleClickCount = array_count_values($articleValues);
            // 完善更新数组
            // 从article中取出对应article_id的view 做相加操作
            $articleIds = array_keys($articleClickCount);
            $article = new Article;
            $oriArticleViews = $article -> where('article_id', 'in', $articleIds) -> field('article_id, views') -> select();
            $oriArticleViews = collection($oriArticleViews) -> toArray();
            $temp = array();
            foreach ($articleClickCount as $k => $v) {
                foreach ($oriArticleViews as $ke => $va) {
                    if ($va['article_id'] == $k) {
                        $temp['article_id'] = $k;
                        $temp['views'] = $v * rand(1,9) + $va['views'];
                        $updateArticleArr []= $temp;
                        break 1;
                    }
                }
            }
            $minipro -> update($updateArticleArr);
        }
        // 向数据库写入更新时间缓存
        $update_time -> insert(['mini_update_time' => $currentTime, 'column_update_time' => $currentTime, 'article_update_time' => $currentTime, 'create_time' => time()]);
        // 设置下次更新的缓存时间
        Cache::set('updateCache', $currentTime, 1800);
    }

}
