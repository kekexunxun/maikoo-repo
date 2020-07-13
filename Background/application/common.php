<?php
/*
 * @Author: Locked
 * @Date: 2018-06-06 10:24:01 
 * @Last Modified by: Locked
 * @Last Modified time: 2018-07-13 16:56:40
 * 
 * 小程序应用商城公共函数库
 */
 

// namespace  app\index\controller;

use \think\Controller;
use \think\Cache;
use \think\Db;
use \think\Session;
use \think\File;

use app\index\model\Userinfo;
use app\index\model\Bannerlist;
use app\index\model\Column;
use app\index\model\Catagory;
use app\index\model\Minipro;
use app\index\model\Search;
use app\index\model\User_fav;
use app\index\model\Article;
use app\index\model\Article_cat;
use app\index\model\Rank;

/**
 * 获取所有小程序应用或指定id的小程序应用
 *
 * @param string $field 查询的字段
 * @param boolean $isAll 查询条件（是否展示所有的小程序）
 * @return array $minis
 */
function getAllMini($field = null, $isAll = true){
    if ($isAll && !is_bool($isAll)) {
        return 'Invaild State';
    }

    $field = $field ? $field : 'mini_id, appid, path, name, avatarUrl, catagory_id, keywords, extra_data, views';

    $minipro = new Minipro;
    $minis = $minipro -> where('is_active', 'in', $isAll ? [0, 1] : [1]) -> where('is_delete', 0) -> field($field ? $field : '*') -> select();
    if (!$minis && count($minis) == 0) {
        return $minis;
    }
    $SITE_PIC_ROOT = "https://minipro.up.maikoo.cn/public";
    $minis = collection($minis) -> toArray();
    // 如果查找的field里面包含avatarUrl字段 则进行url完善
    if (!$field || strstr($field, 'avatarUrl')) {
        foreach ($minis as $k => $v) {
            $minis[$k]['avatarUrl'] = $SITE_PIC_ROOT . $v['avatarUrl'];
            if (strstr($field, "views")) {
                $mini[$k]['views'] = $v['views'] * rand(1, 9);
            }
        }
    }

    return $minis;
}

/**
 * 通过小程序ID获取小程序相关信息
 *
 * @param int $miniID 小程序ID
 * @param string $field 字段
 * @param boolean $isAll 查询条件（是否为正在使用的小程序）
 * @return array $mini 小程序信息
 */
function getMiniById($miniID, $field = null, $isAll = true){
    if (!$miniID || !is_int($miniID)) {
        return 'Invaild miniID';
    }
    if ($field && !is_string($field)) {
        return 'Invaild Field';
    }
    if ($isAll && !is_bool($isAll)) {
        return 'Invaild State';
    }
    // $field 可选
    $minipro = new Minipro;
    $mini = $minipro -> where('mini_id', $miniID) -> where('is_active', 'in', $isAll ? [0, 1] : [1]) -> where('is_delete', 0) -> field($field ? $field : '*') -> select();
    // dump($mini);die;
    if (!$mini || count($mini) == 0) {
        return null;
    }
    $mini = collection($mini) -> toArray();
    $mini = $mini[0];
    // 获取小程序对应的分类列表
    $catField = "catagory_id, father_id, name";
    $allCat = getAllCatagory($catField, false);
    // 小程序目录为两级结构
    foreach ($allCat as $k => $v) {
        if ($v['catagory_id'] == $mini['catagory_id']) {
            $mini['catagory_name'] = $v['name'];
            foreach ($allCat as $ke => $va) {
                if ($va['catagory_id'] == $v['father_id']) {
                    $mini['father_cat_id'] = $va['catagory_id'];
                    $mini['father_cat_name'] = $va['name'];
                    break 1;
                }
            }
            break 1;
        }
    }
    
    return $mini;
}

/**
 * 获取所有的专栏列表
 *
 * @param string $field 查询的字段
 * @param int $isAll 查询条件（是否查询全部）
 * @param string $miniField 查询条件（是否删除）
 * @param int $pageNum 查询条件（页数）
 * @return void
 */
function getAllColumn($field = null, $isAll = true, $miniField = null, $pageNum = null){
    if ($field && !is_string($field)) {
        return 'Invaild Field';
    }
    if ($isAll && !is_bool($isAll)) {
        return 'Invaild State';
    }
    if (($pageNum && !is_int($pageNum)) || ($pageNum > 999 || $pageNum < 0)) {
        return 'Invaild PageNum';
    }
    $column = new Column;

    if (isset($pageNum) && is_int($pageNum)) {
        $allColumnList = $column -> where('is_active', 'in', $isAll ? [0, 1] : [1]) -> field($field ? $field : '*') -> where('is_delete', 0) -> limit(($pageNum - 1) * 12, 12) -> select();
    }else{
        $allColumnList = $column -> where('is_active', 'in', $isAll ? [0, 1] : [1]) -> field($field ? $field : '*') -> where('is_delete', 0) -> select();
    }
    // 将数据库查询出来的obj转为array
    if (!$allColumnList) {
        return null;
    }
    $allColumnList = collection($allColumnList) -> toArray();
    // foreach ($allColumnList as $k => $v) {
    //     $currentColumn = getColumnById($v['idx']);
    //     if (!$currentColumn) {
    //         $v['miniList'] = $currentColumn['miniList'];
    //     }
    // }
    return $allColumnList;
}

/**
 * 通过专栏ID获取专栏信息
 *  
 * @param int $columnID 专栏ID
 * @param string $field 查询的字段
 * @param string $miniField 查询指定专栏对应小程序的字段信息
 * @param boolean $isAll 当前小程序专栏是否正在使用中
 * @return array $columnInfo 专栏信息
 */
function getColumnById($columnID, $field = null, $miniField = null, $isAll = true){
    if (!$columnID || !is_int($columnID)) {
        return 'Invaild columnID';
    }
    if ($field && !is_string($field)) {
        return 'Invaild Field';
    }
    if ($isAll && !is_bool($isAll)) {
        return 'Invaild Param';
    }
    $column = new Column;
    $columnInfo = $column -> where('idx', $columnID) -> where('is_active', 'in', $isAll ? [0, 1] : [1]) -> where('is_delete', 0) -> field($field ? $field : '*') -> select();
    if (!$columnInfo || count($columnInfo) == 0) {
        return null;
    }
    // dump($columnInfo);die;
    $columnInfo = collection($columnInfo) -> toArray();
    // dump($columnInfo);die;
    $columnInfo = $columnInfo[0];
    if (!array_key_exists('minis', $columnInfo)) {
        return $columnInfo;
    }
    $minis = $columnInfo['minis'];
    // 如果获取的专栏含有小程序这一列才进行相应处理
    // 简单数据处理 获取对应的专栏的小程序信息
    // 构建返回数组
    $miniList = array();
    // 获取所有的小程序
    $allMiniList = getAllMini($miniField, $isAll);
    // 0:1*1:2*2:3 格式举例如此 小程序ID:排序
    $miniArr = explode('*', str_replace(':', '*', $minis));
    // dump($miniArr);die;
    // array(0, 1, 1, 2, 2, 3)
    // 构建小程序排序数据
    $miniSort = array();
    // 获取小程序信息
    $count = 0;
    foreach ($miniArr as $k => $v) {
        if ($k % 2 == 0) {
            foreach ($allMiniList as $ke => $va) {
                if ($va['mini_id'] == $v) {
                    // 简单数据处理
                    $va['views'] = $va['views'] * rand(1, 9);
                    // $va['create_time_convert'] = date('Y-m-d H:i:s', $va['create_time']);
                    $miniList []= $va;
                    break 1;
                }
            }
            $miniList[$count]['mini_id'] = $v;
        }else{
            $miniList[$count]['sort'] = $v;
            $miniSort []= $v;
            $count++;
        }
    }
    // dump($miniList);die;

    // 数据根据小程序的sort进行排序
    if (count($miniList) > 0 && count($miniSort) > 0) {
        array_multisort($miniSort, SORT_DESC, $miniList); 
    }
    
    $columnInfo['minis'] = $miniList;
    // dump($columnInfo);die;
    return $columnInfo;
}

/**
 * 构造返回数据
 *
 * @param int $code 返回码
 * @param string $msg 返回信息
 * @param array $data 返回的数据
 * @return json $data
 */
function objReturn($code, $msg, $data = null){
    if (!is_int($code)) {
        return 'Invaild Code';
    }
    if (!is_string($msg)) {
        return 'Invaild Msg';
    }
    $res['code'] = $code;
    $res['msg'] = $msg;
    if ($data) {
        $res['data'] = $data;
    }
    return json($res);
}

/**
 * 更细数据库相关信息
 *
 * @param int $table 需要更新的表名
 * @param array $where 更新的字段
 * @param int $isUpdate 是更新还是新增
 * @return int $isSuccess 是否更新成功
 */
function saveData($table, $where, $isUpdate = true){
    if (!$table || !is_string($table)) {
        return 'Invaild Table';
    }
    if (!$where || !is_array($where)) {
        return 'Invaild Field';
    }
    if ($isUpdate && !is_bool($isUpdate)) {
        return 'Invaild State';
    }
    // 表名
    $tableName = null;
    switch ($table) {
        case 'mini':
            $tableName = new Minipro;
            break;
        case 'column':
            $tableName = new Column;
            break;
        case 'sort':
            $tableName = new Sort;
            break;
        case 'banner':
            $tableName = new Bannerlist;
            break;        
        case 'search':
            $tableName = new Search;
            break;
        case 'article':
            $tableName = new Article;
            break;
        case 'article_cat':
            $tableName = new Article_cat;
            break;
        case 'catagory':
            $tableName = new Catagory;
            break;
    }
    // 判断数据长度
    $isSuccess = $tableName -> isUpdate($isUpdate) -> save($where);
    // 结果返回
    return $isSuccess;
}

/**
 * 获取用户信息
 *
 * @param int/string $user 用户的openid或userid
 * @param string $field 需要查询的字段
 * @return mixed $userInfo
 */
function getUserinfo($user = null, $field = null){
    if ($user && !(is_string($user) || is_int($user))) {
        return 'Invaild User';
    }
    if ($field && !is_string($field) && !($field == '*' || (strchr($field, 'user_id') && strchr($field, 'user_openid')))) {
        return 'Invaild Field';
    }
    $userinfo = new Userinfo;
    $userInfo = $userinfo -> field($field ? $field : '*') -> select();
    // 数据库OBJ对象 -> Collection对象 -> Array
    $userInfo = collection($userInfo) -> toArray();
    if ($user) {
        foreach ($userInfo as $k => $v) {
            if ($user == $v['user_openid'] || $user == $v['user_id']) {
                return $v;
            }
        }
        return null;
    }
    return $userInfo;
}

/**
 * 获取所有的分类
 *
 * @param string $field 查询的字段
 * @param boolean $isAll 查询条件（是否获取全部）
 * @return void
 */
function getAllCatagory($field = null, $isAll = true){
    if ($field && !is_string($field)) {
        return 'Invaild Field';
    }
    if ($isAll && !is_bool($isAll)) {
        return 'Invaild State';
    }
    $catagory = new Catagory;
    $catList = $catagory -> field($field ? $field : '*') -> where('is_active', 'in', $isAll ? [0, 1] : [1]) -> where('is_delete', 0) -> select();
    if ($catList && count($catList) > 0) {
        $catList = collection($catList) -> toArray();
    }
    return $catList;
}

/**
 * 通过分类ID获取小程序详情
 * 如果是父级分类ID则会获取对应子集分类ID的所有小程序信息
 *
 * @param int $catID 分类ID
 * @param string $field 查询的字段
 * @param string $miniField 查询的小程序对应的字段
 * @param boolean $isInUse 查询条件（是否为可使用的）
 * @param string $userOpenid 若传openid则会判断该小程序是否被用户收藏
 * @return void
 */
function getCatagoryById($catID, $field = null, $miniField = null, $isInUse = true, $userOpenid = null){
    // $field初始化
    $field = $field ? $field : "catagory_id, father_id, name";
    // $miniField初始化
    $miniField = $miniField ? $miniField : "mini_id, appid, name, avatarUrl, views, rate, catagory_id";

    // 首先判断当前catId是否为顶级Id
    // 若不是顶级Id则直接查询对应的分类列表
    // 若是顶级Id 则该Id目录下所以子目录的Id都要展示
    $allCat = getAllCatagory($field, !$isInUse);
    if (!$allCat || count($allCat) == 0) {
        return null;
    }
    // dump($allCat);die;
    $catInfo = null;
    $catInfoIds = null;
    $currentCat = null;
    $userFavMini = null;
    foreach ($allCat as $k => $v) {
        // 获取当前Id子目录信息
        if ($catID != 'all') {
            if ($v['father_id'] == $catID) {
                $catInfo []= $v;
                $catInfoIds []= $v['catagory_id'];
            }
            // 获取当前Id信息
            if ($v['catagory_id'] == $catID) {
                $currentCat = $v;
            }
        }
        // 如果当前传递过来的catID 为 all
        if ($catID == "all" && $v['father_id'] != 0) {
            $catInfoIds []= $v['catagory_id'];
            $catInfo []= $v; 
        }
    }
        
    // 没找到的情况
    if(!$catInfo && !$currentCat){
        return null;
    }
    // 有查找到目录的情况下去获取对应的Id下的catInfo
    if (!$catInfo && $currentCat) {
        $catInfo []= $currentCat;
        $catInfoIds []= $currentCat['catagory_id'];
    }
    // dump($catInfo);die;
    $minipro = new Minipro;
    $catMini = $minipro -> where('catagory_id', 'in', $catInfoIds) -> field($miniField) -> where('is_delete', 0) -> where('is_active', 'in', $isInUse ? [1] : [0, 1]) -> order('create_time desc') -> select();
    if (!$catMini || count($catMini) == 0) {
        return $catInfo;
    }
    $catMini = collection($catMini) -> toArray();
  
    // 如果有传openid 则判断当前小程序是否被用户所收藏
    if ($userOpenid) {
        // 1 构造当前分类中的所有小程序ID
        $allMiniIds = [];
        foreach ($catMini as $k => $v) {
            $allMiniIds []= $v['mini_id'];
        }
        // 2 查询用户收藏表中包含这些ID的数据
        $user_fav = new User_fav;
        $userFavMini = $user_fav -> where('user_openid', $userOpenid) -> where('fav_id', 'in', $allMiniIds) -> where('fav_type', 1) -> where('is_fav', 1) -> field('idx, fav_id') -> select();
        if ($userFavMini) {
            $userFavMini = collection($userFavMini) -> toArray();
        }
    }
    // 构造CatInfo
    if (count($catInfo) > 0) {
        foreach ($catInfo as $k => $v) {
            $catInfo[$k]['father_name'] = $currentCat['name'];
            foreach ($catMini as $ke => $va) {
                if ($v['catagory_id'] == $va['catagory_id']) {
                    $va['avatarUrl'] = "https://minipro.up.maikoo.cn/public" . $va['avatarUrl'];
                    // 判断当前小程序是否被用户收藏
                    $va['isFav'] = false;   // 默认数据添加
                    $va['favIdx'] = null;   // 默认数据添加
                    if ($userOpenid && $userFavMini) {
                        foreach ($userFavMini as $key => $val) {
                            if ($val['fav_id'] == $va['mini_id']) {
                                $va['isFav'] = true;
                                $va['favIdx'] = $val['idx'];
                                break 1;
                            }
                        }
                    }
                    // 对小程序关键词做处理
                    if (isset($va['keywords'])) {
                        $va['keywords'] = str_replace(",", " ", $va['keywords']);
                    }
                    if (isset($va['views'])) {
                        $va['views'] = $va['views'] * rand(1, 9);
                    }
                    $catInfo[$k]['minis'] []= $va;
                }
            }
        }
    }
    return $catInfo;
}

/**
 * 获取小程序搜索界面的展示列表
 *
 * @param boolean $isAll 查找条件（是否获取全部）
 * @param string $field 查找条件（需要查找的字段）
 * @return void
 */
function getSearchList($field = null, $isAll = true){
    if ($field && !is_string($field) && !strchr($field, 'mini_id')) {
        return 'Invaild Field';
    }
    if ($isAll && !is_bool($isAll)) {
        return 'Invaild State';
    }
    $search = new Search;
    $searchList = $search -> field($field ? $field : '*') -> where('is_active', 'in', $isAll ? [0, 1] : [1]) -> where('is_delete', 0) -> order('orderby desc') -> select();
    if (!$searchList || count($searchList) == 0) {
        return null;
    }
    $searchList = collection($searchList) -> toArray();
    // 完善其中的小程序信息
    $miniField = "mini_id, appid, name, avatarUrl";
    $allMiniList = getAllMini($miniField, false);
    if (!$allMiniList) {
        return $searchList;
    }
    // $imageSiteroot = "https://minipro.up.maikoo.cn/public";
    foreach ($searchList as $k => $v) {
        foreach ($allMiniList as $ke => $va) {
            if ($v['mini_id'] == $va['mini_id']) {
                $searchList[$k]['name'] = $va['name'];
                $searchList[$k]['appid'] = $va['appid'];
                $searchList[$k]['avatarUrl'] = $va['avatarUrl'];
                break 1;
            }
        }
    }
    return $searchList;
}

/**
 * 获取指定用户的收藏列表
 *
 * @param string $userOpenid 用户的openid
 * @param int $favType 需要查找的类型 0 全部 1 小程序 2 专栏
 * @param boolean $isAll 是否需要获取全部数据
 * @param string $field 查找条件（需要查找的字段）
 * @param int $pageNum 查找条件（需要查账号的页码 默认每页10个）
 * @return array $userFavList 用户的收藏列表
 */
function getUserFavList($userOpenid, $field = null, $favType = 0, $isAll = true, $pageNum = null){
    if (!$userOpenid || !is_string($userOpenid)) {
        return "Invaild Openid";
    }
    if (!is_int($favType)) {
        return "Invaild FavType";
    }
    if (!is_bool($isAll)) {
        return "Invaild Param";
    }
    if (($pageNum && !is_int($pageNum)) || ($pageNum > 999 || $pageNum < 0)) {
        return 'Invaild PageNum';
    }
    $user_fav = new User_fav;
    if (isset($pageNum) && is_int($pageNum)) {
        $userFav = $user_fav -> where('user_openid', $userOpenid) -> where('is_fav', 'in', $isAll ? [0, 1] : [1]) -> where('fav_type', "in", $favType == 0 ? [1, 2] : [$favType]) -> field($field ? $field : '*') -> limit($pageNum * 10, 10) -> select();
        // dump($userFav);die;
    }else{
        $userFav = $user_fav -> where('user_openid', $userOpenid) -> where('is_fav', 'in', $isAll ? [0, 1] : [1]) -> where('fav_type', "in", $favType == 0 ? [1, 2] : [$favType]) -> field($field ? $field : '*') -> select();
        
    }
    // 非空返回
    if (!$userFav || count($userFav) == 0) {
        return $userFav;
    }
    $userFav = collection($userFav) -> toArray();

    return $userFav;
}

/**
 * 获取文章列表
 *
 * @param int $articleType 文章类别 0 评测 1 资讯
 * @param string $field 用户需要查询的字段
 * @param boolean $isAll 是否获取所有的文章列表
 * @param int $subId 子集分类ID 当articleType=1时 此项必传
 * @param int $pageNum 需要查询的页码
 * @return void
 */
function getArticleList($articleType, $field = null, $isAll = true, $subId = null, $pageNum = null){
    if (!($articleType === 0 || $articleType === 1)) {
        return "Invaild Param";
    }
    if ($articleType === 1 && !$subId) {
        return "Invaild Param";
    }
    // 初始化field
    $field = $field ? $field : "article_id, title, author, views, create_time, pic, is_active";
    $isAll = $isAll ? [0, 1] : [1];
    $article = new Article;

    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : "";
    // dump($limit); die;

    $subId = isset($subId) ? $subId : 0;
    // 如果要获取全部 那么subId就传all
    if ($subId == 'all') {
        $articleList = $article -> field($field) -> where('is_active', 'in', $isAll) -> where('type', $articleType) -> where('is_delete', 0) -> limit($limit) -> select();
    }else{
        $articleList = $article -> field($field) -> where('is_active', 'in', $isAll) -> where('type', $articleType) -> where('cat_id', $subId) -> where('is_delete', 0) -> limit($limit) -> select();
    }
    if(!$articleList || count($articleList) == 0) return null;
    $articleList = collection($articleList) -> toArray();
    // 如果查询的字段中有pic字段，则对其进行简单处理
    if (strstr($field, "pic")) {
        foreach ($articleList as &$articleInfo) {
            $articleInfo['pic'] = "https://minipro.up.maikoo.cn/public" . $articleInfo['pic'];
        }
    }
    return $articleList;
}

/**
 * 获取资讯的分类
 *
 * @param string $field 用户需要查询的字段
 * @param boolean $isAll 是否获取所有的文章列表
 * @return void
 */
function getArticleCat($field = null, $isAll = true){
    // 初始化部分参数
    $field = $field ? $field : "cat_id, name, is_active";
    $isAll = $isAll ? [0, 1] : [1];
    $article_cat = new Article_cat;
    $articleCatList = $article_cat -> where('is_delete', 0) -> where('father_id', 1) -> where('is_active', 'in', $isAll) -> field($field) -> order('orderby desc') -> select();
    $articleCatList = collection($articleCatList) -> toArray();
    return $articleCatList;
}

/**
 * 通过文章Id获取文章的详细信息
 *
 * @param int $articleId 文章ID
 * @param string $field 需要查询的字段
 * @param boolean $isInUse 是否为展示出的（is_active = 1）
 * @return void
 */
function getArticleById($articleId, $field = null, $isInUse = false){
    // 初始化部分参数
    $field = $field ? $field : 'article_id, title, content, pic, author, cat_id, type, views, create_time';
    $isInUse = $isInUse ? [0, 1] : [1];
    // 数据查询
    $article = new Article;
    $ArticleInfo = $article -> where('article_id', $articleId) -> where('is_active', 'in', $isInUse) -> field($field) -> select();
    if ($ArticleInfo && count($ArticleInfo) > 0) {
        $ArticleInfo = collection($ArticleInfo) -> toArray();
        $ArticleInfo = $ArticleInfo[0];
        // 简单数据处理
        $ArticleInfo['title'] = htmlspecialchars_decode($ArticleInfo['title']);
        // $ArticleInfo['brief'] = htmlspecialchars_decode($ArticleInfo['brief']);
        $ArticleInfo['content'] = htmlspecialchars_decode($ArticleInfo['content']);
        $ArticleInfo['pic'] = "https://minipro.up.maikoo.cn/public" . $ArticleInfo['pic'];
    }
    return $ArticleInfo;
}

/**
 * 获取小程序排行榜数据
 *
 * @param boolean $isAll 是否获取全部的小程序
 * @param integer $pageNum 需要分页获取的页码
 * @return void
 */
function getRank($isAll = true, $pageNum = null){

    // 部分数据初始化
    if (isset($pageNum)) {
        $limit = $pageNum * 10 . ", 10";
    }else{
        $limit = "";
    }
    $isActive = $isAll ? [0, 1] : [1];
    $rank = new Rank;
    $rankList = $rank -> alias('r') -> join('sp_minipro m', 'm.mini_id = r.mini_id', 'LEFT') -> field('r.idx, r.mini_id, r.orderby, r.is_active, m.appid, m.keywords, m.name, m.avatarUrl') -> where('r.is_active', 'in', $isActive) -> where('r.is_delete', 0) -> limit($limit) -> select();
    if (!$rankList && count($rankList) == 0) {
        return null;
    }
    $rankList = collection($rankList) -> toArray();
    // 简单数据处理
    foreach ($rankList as &$info) {
        $info['keywords'] = $info['keywords'] ? str_replace(",", " ", $info['keywords']) : "";
        $info['avatarUrl'] = "https://minipro.up.maikoo.cn/public" . $info['avatarUrl'];
    }
    return $rankList;
}