<?php

use \think\Cache;

use app\index\model\User;
use app\index\model\User_profile;
use app\index\model\Msg;
use app\index\model\Banner;
use app\index\model\Admin;
use app\index\model\Goods;
use app\index\model\Catagory;
use app\index\model\Article;
use app\index\model\Coupon;
use app\index\model\Question;
use app\index\model\Feedback;
use app\index\model\Order;
use app\index\model\Search_kw;
use app\index\model\Column;
use app\index\model\Column_goods;
use app\index\model\Power;
use app\index\model\Order_detail;
use think\Db;

/**
 * 构造返回数据
 *
 * @param int $code 返回码
 * @param string $msg 返回信息
 * @param array $data 返回的数据
 * @return json $data
 */
function objReturn($code, $msg, $data = null)
{
    if (!is_int($code)) {
        return 'Invaild Code';
    }
    if (!is_string($msg)) {
        return 'Invaild Msg';
    }
    $res['code'] = $code;
    $res['msg'] = $msg;
    if (isset($data) && !empty($data)) {
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
function saveData($table, $where, $isUpdate = true)
{
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
        case 'profile':
            $tableName = new User_profile;
            break;
        case 'user':
            $tableName = new User;
            break;
        case 'msg':
            $tableName = new Msg;
            break;
        case 'banner':
            $tableName = new Banner;
            break;
        case 'admin':
            $tableName = new Admin;
            break;
        case 'goods':
            $tableName = new Goods;
            break;
        case 'cat':
            $tableName = new Catagory;
            break;
        case 'article':
            $tableName = new Article;
            break;
        case 'power':
            $tableName = new Power;
            break;
        case 'order':
            $tableName = new Order;
            break;
    }
    // 判断数据长度
    $isSuccess = $tableName->isUpdate($isUpdate)->save($where);
    // 结果返回
    return $isSuccess;
}

/**
 * 获取系统商品
 *
 * @param string $field 需要查找的字段
 * @param int $mchId 商家ID
 * @param boolean $isAll 是否查找所有状态的商品
 * @param int $pageNum 分页查找的页码
 * @return void
 */
function getGoods($field = null, $mchId = null, $isAll = true, $pageNum = null)
{
    $field = $field ? $field : "goods_id, goods_sn, goods_name, goods_img, cat_id, market_price, shop_price, member_price, keywords, sort, unit, status";
    $status = $isAll ? [0, 1, 2, 3] : [2];
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    $goods = new Goods;
    // 如果有传商家ID
    if (isset($mchId)) {
        $field = "g.goods_id, g.goods_sn, g.goods_name, g.goods_img, g.cat_id, g.market_price, g.shop_price, g.member_price, g.keywords, g.sort, g.unit, g.status, s.stock, s.sales_num";
        $goodsList = $goods->alias('g')->join('mk_merchant_stock s', 'g.goods_id = s.goods_id', 'RIGHT')->where('s.mch_id', $mchId)->field($field)->select();
    } else {
        $goodsList = $goods->where('status', 'in', $status)->field($field)->limit($limit)->select();
    }
    if (!$goodsList || count($goodsList) == 0) {
        return null;
    }
    $goodsList = collection($goodsList)->toArray();
    foreach ($goodsList as &$good) {
        if (isset($good['goods_name'])) $good['goods_name'] = htmlspecialchars_decode($good['goods_name']);
        if (isset($good['keywords'])) $good['keywords'] = str_replace(',', ' ', htmlspecialchars_decode($good['keywords']));
        if (isset($good['unit'])) $good['unit'] = htmlspecialchars_decode($good['unit']);
        if (isset($good['market_price'])) $good['market_price'] = number_format($good['market_price'], 2);
        if (isset($good['shop_price'])) $good['shop_price'] = number_format($good['shop_price'], 2);
        if (isset($good['member_price'])) $good['member_price'] = number_format($good['member_price'], 2);
        if (isset($good['goods_img'])) $good['goods_img'] = "https//xnps.up.maikoo.cn/static" . $good['goods_img'];
    }
    return $goodsList;
}

/**
 * 通过商品Id获取商品详情
 *
 * @param int $goodsId 商品ID
 * @param boolean $isInUse 商品上架状态
 * @return obj 能 array 商品信息 否 null
 */
function getGoodsById($goodsId, $isInUse = true)
{
    if (empty($goodsId)) {
        return "Invaild Param";
    }
    $baseUrl = "https://xnps.up.maikoo.cn/static";
    $isInUse = $isInUse ? [2] : [0, 1, 2, 3];
    $goods = new Goods;
    $goodsInfo = $goods->alias('g')->join('mk_catagory c', 'g.cat_id = c.cat_id', 'LEFT')->where('g.goods_id', $goodsId)->where('g.status', 'in', $isInUse)->field('g.goods_id, g.goods_sn, g.goods_img, g.keywords, g.goods_name, g.market_price, g.shop_price, g.member_price, g.stock, g.sales_num, g.is_new, g.is_hot, g.points, g.unit, c.cat_id, c.cname, g.created_at, g.goods_desc, g.status')->select();
    if (!$goodsInfo || count($goodsInfo) == 0) {
        return null;
    }
    $goodsInfo = collection($goodsInfo)->toArray();
    $goodsInfo = $goodsInfo[0];
    // 部分变量调整
    $goodsInfo['goods_img'] = $baseUrl . $goodsInfo['goods_img'];
    $goodsInfo['goods_name'] = htmlspecialchars_decode($goodsInfo['goods_name']);
    $goodsInfo['cname'] = htmlspecialchars_decode($goodsInfo['cname']);
    $goodsInfo['market_price'] = number_format($goodsInfo['market_price'], 2);
    $goodsInfo['shop_price'] = number_format($goodsInfo['shop_price'], 2);
    $goodsInfo['member_price'] = number_format($goodsInfo['member_price'], 2);
    if (!empty($goodsInfo['goods_desc'])) {
        $descArr = explode(",", $goodsInfo['goods_desc']);
        foreach ($descArr as &$info) {
            $info = explode(':', $info);
            $info = $baseUrl . $info[0];
        }
        $goodsInfo['goods_desc'] = $descArr;
    }
    $goodsInfo['keywords'] = empty($goodsInfo['keywords']) ? "" : str_replace(',', ' ', $goodsInfo['keywords']);
    return $goodsInfo;
}

/**
 * 获取banner或广告位
 *
 * @param int $type bannerType 0 banner 1 AD
 * @param boolean $isAll 是否获取所有状态（除删除）的数据
 * @param int $imgId 具体的列ID
 * @return void
 */
function getBanner($type = 0, $isAll = true, $imgId = null)
{
    // 查询字段
    $field = "img_id, img_src, nav_type, nav_id, location, sort, status";
    $isAll = $isAll ? [0, 1, 2] : [1];
    $limit = $isAll ? $limit = "" : $limit = 5;
    $banner = new Banner;
    if ($type == 0) {
        $res = $banner->where('img_type', 0)->where('status', 'in', $isAll)->field($field)->limit($limit)->order('sort desc')->select();
    } elseif ($type == 1) {
        $res = $banner->where('img_type', 1)->where('status', 'in', $isAll)->field($field)->select();
    } elseif ($type == 1 && !empty($navId)) {
        $res = $banner->where('img_type', 1)->where('img_id', $imgId)->where('status', 'in', $isAll)->field($field)->select();
    }
    if ($res) {
        $res = collection($res)->toArray();
        foreach ($res as &$info) {
            $info['img_src'] = config('static_path') . $info['img_src'];
        }
    } else {
        $res = null;
    }
    return $res;
}

/**
 * 获取系统优惠券列表
 *
 * @param int $status 当status传 'all' 时表明获取所有状态的coupon 0未上线1已上线2已暂停3已结束
 * @param int $pageNum 需要查询的页码 每页限定10个
 * @return obj 有coupon返回array 无则返回null
 */
function getCoupon($status = 0, $pageNum = null)
{
    $field = "coupon_id, coupon_sn, coupon_name, money, send_start_at, send_end_at, total_num, send_num, created_at, status, pause_at, condition";
    $status = $status == "all" ? [0, 1, 2, 3] : [$status];
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    $coupon = new Coupon;
    $couponList = $coupon->where('status', 'in', $status)->field($field)->limit($limit)->select();
    if (!$couponList || count($couponList) == 0) return null;
    $couponList = collection($couponList)->toArray();
    // 对couponList进行检测和验证
    $updateArr = [];
    $curTime = time();
    $leftCouponList = [];
    foreach ($couponList as $k => $v) {
         // 1 过期验证
        // 2 有限制发布总数并且当发布总数与领取人数相同时改变状态
        if (($v['status'] == 1 && $v['send_end_at'] < $curTime) || ($v['total_num'] != 0 && $v['total_num'] == $v['send_num'])) {
            $temp['coupon_id'] = $v['coupon_id'];
            $temp['status'] = 3;
            $updateArr[] = $temp;
            if ($status == 'all') $v['status'] = 3;
            else continue;
        }
        // 简单数据处理
        $v['send_start_at_conv'] = date('Y-m-d', $v['send_start_at']);
        $v['send_end_at_conv'] = date('Y-m-d', $v['send_end_at']);
        $v['created_at'] = date('Y-m-d H:i:s', $v['created_at']);
        if (!empty($v['pause_at'])) $v['pause_at_conv'] = date('Y-m-d H:i:s', $v['pause_at']);
        $leftCouponList[] = $v;
    }
    $couponList = $leftCouponList;
    if (count($updateArr) > 0) {
        $coupon->isUpdate()->saveAll($updateArr);
    }
    return count($couponList) > 0 ? $couponList : null;
}

/**
 * 获取系统问答
 * 若分页获取则每页限制10个问题
 *
 * @param boolean $isAll 是否获取全部状态的问答
 * @param int $pageNum 是否需要分页获取
 * @return obj 有则返回QA的array 无则返回null
 */
function getSysQA($isAll = true, $pageNum = null)
{
    $field = "question_id, question, answer, created_at, update_at, status";
    $status = $isAll ? [0, 1] : [1];
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : "";
    $question = new Question;
    $questionList = $question->where('status', 'in', $status)->field($field)->limit($limit)->select();
    if (!$questionList || count($questionList) == 0) {
        return null;
    }
    $questionList = collection($questionList)->toArray();
    // 简单处理
    foreach ($questionList as &$info) {
        $info['question'] = htmlspecialchars_decode($info['question']);
        $info['answer'] = htmlspecialchars_decode($info['answer']);
        $info['created_at'] = date('Y-m-d H:i:s', $info['created_at']);
        $info['update_at'] = empty($info['update_at']) ? '' : date('Y-m-d H:i:s', $info['update_at']);
    }
    return $questionList;
}

/**
 * 获取用户反馈
 * 如果有传用户ID 则必传pageNum 否则返回所有
 *
 * @param int $uid 用户id
 * @param int $pageNum 需要获取的页码 每页限定10个
 * @return void
 */
function getFeedback($uid = null, $pageNum = null)
{
    if (!empty($uid) && empty($pageNum)) {
        return 'Invaild Param';
    }
    $field = "idx, uid, message, img, reply, created_at, reply_at, reply_by, status";
    $feedback = new Feedback;
    if ($uid) {
        $feedbackList = $feedback->where('uid', $uid)->where('status', 'in', [0, 1])->field($field)->limit($pageNum * 10, 10)->select();
    } else {
        $feedbackList = $feedback->field($field)->select();
    }
    if (!$feedbackList || count($feedbackList) == 0) {
        return null;
    }
    $feedbackList = collection($feedbackList)->toArray();
    // 简单处理
    $baseUrl = "https://xnps.up.maikoo.cn/static";
    foreach ($feedbackList as &$info) {
        $info['img'] = $baseUrl . $info['img'];
        $info['message'] = htmlspecialchars_decode($info['message']);
        $info['reply'] = htmlspecialchars_decode($info['reply']);
        $info['reply_at'] = empty($info['reply_at']) ? '' : date('Y-m-d H:i:s', $info['reply_at']);
        $info['created_at'] = date('Y-m-d H:i:s', $info['created_at']);
    }
    return $feedbackList;
}

/**
 * 获取商城专栏详情
 * 
 * @param string $field 需要查询的字段
 * @param boolean $isAll 是否获取所有状态的专栏
 * @param int $status 获取指定状态的专栏
 * @return void
 */
function getColumn($field = null, $isAll = true, $status = null)
{
    if (!empty($status) && $isAll) {
        return "Param Failed";
    }
    $baseUrl = "https://xnps.up.maikoo.cn/static";
    $status = $isAll ? [0, 1, 2] : [$status];
    $field = isset($field) ? $field : "column_id, column_color, column_name, column_img, sort, created_at, status, is_top";
    $column = new Column;
    $columnList = $column->where('status', 'in', $status)->field($field)->order('sort desc')->select();
    if (!$columnList || count($columnList) == 0) {
        return null;
    }
    $columnList = collection($columnList)->toArray();
    // 简单处理
    foreach ($columnList as &$info) {
        if (isset($info['column_img'])) $info['column_img'] = $baseUrl . $info['column_img'];
    }
    return $columnList;
}

/**
 * 通过ID获取专栏详情
 *
 * @param int $columnId 专栏ID
 * @param boolean $isAll 是否获取所有状态的专栏信息
 * @param int $goodsLimit 是否限制获取专栏商品的数量
 * @return void
 */
function getColumnById($columnId, $isAll = true, $goodsLimit = null)
{
    if (empty($columnId)) {
        return "Invaild Param";
    }
    $baseUrl = "https://xnps.up.maikoo.cn/static";
    $columnStatus = $isAll ? [0, 1, 2] : [1, 2];
    $columnField = "column_id, column_color, column_name, column_img, sort, created_at, status, is_top";
    $column = new Column;
    $column_goods = new Column_goods;
    $columnInfo = $column->where('status', 'in', $columnStatus)->where('column_id', $columnId)->select();
    if (!$columnInfo || count($columnInfo) == 0) {
        return null;
    }
    $columnInfo = collection($columnInfo)->toArray();
    $columnInfo = $columnInfo[0];
    $columnInfo['column_img'] = $baseUrl . $columnInfo['column_img'];
    $columnInfo['column_name'] = htmlspecialchars_decode($columnInfo['column_name']);
    // 查询对应的商品详情
    $columnGoodsStatus = $isAll ? [0, 1] : [1];
    $columnGoodsNumLimit = isset($goodsLimit) ? $goodsLimit : 100;
    $columnGoodsList = $column_goods->alias('c')->join('mk_goods g', 'c.goods_id = g.goods_id', 'LEFT')->where('c.column_id', $columnId)->where('c.status', 'in', $columnGoodsStatus)->where('g.status', 2)->field('c.idx, c.goods_id, c.sort, c.status, g.goods_sn, g.goods_name, g.goods_img, g.market_price, g.shop_price, g.keywords')->order('c.sort desc')->limit($columnGoodsNumLimit)->select();
    if (!$columnGoodsList || count($columnGoodsList) == 0) {
        $columnGoodsList = [];
    } else {
        $columnGoodsList = collection($columnGoodsList)->toArray();
        foreach ($columnGoodsList as &$info) {
            $info['goods_name'] = htmlspecialchars_decode($info['goods_name']);
            $info['goods_img'] = $baseUrl . $info['goods_img'];
            $info['market_price'] = number_format($info['market_price'], 2);
            $info['shop_price'] = number_format($info['shop_price'], 2);
            $info['keyword'] = str_replace(',', ' ', $info['keywords']);
        }
    }
    $columnInfo['goods'] = $columnGoodsList;
    return $columnInfo;
}

/**
 * 获取搜索关键词
 *
 * @param boolean $isAll 是否查询所有状态
 * @return void
 */
function getSearchValue($isAll = true)
{
    $field = "idx, value, nav_type, nav_id, sort, created_at, status";
    $status = $isAll ? [0, 1] : [1];
    $search_kw = new Search_kw;
    $searchValueList = $search_kw->where('status', 'in', $status)->field($field)->order('sort desc')->select();
    if (!$searchValueList || count($searchValueList) == 0) {
        return null;
    }
    $searchValueList = collection($searchValueList)->toArray();
    foreach ($searchValueList as &$info) {
        $info['value'] = htmlspecialchars($info['value']);
    }
    return $searchValueList;
}

/**
 * 获取订单列表
 * 订单状态 1未付款 2待发货 3已发货 4待评价 5已完成 6已取消
 *
 * @param int $status 订单状态 0 表示全部
 * @param int $pageNum 需要查询的页数
 * @param int $uid 用户id
 * @param int $mch_id 商户ID
 * @return void
 */
function getOrder($status = 0, $pageNum = null, $uid = null, $mch_id = null, $orderSn = null)
{
    $order = new Order;
    $baseUrl = "https://xnps.up.maikoo.cn/static";
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    if (!is_array($status)) $status = $status == 0 ? [1, 2, 3, 4, 5, 6] : [$status];
    $orderField = 'order_id, order_sn, uid, mch_id, total_fee, logi_fee, coupon_fee, username, phone, address, message, pay_at, created_at, finish_at, cancel_at, status, mch_name, delivery_at, coupon_id, coupon_fee, complete_at';
    if (isset($uid)) {
        $orderList = $order->where('uid', $uid)->where('status', 'in', $status)->field($orderField)->limit($limit)->order('created_at desc')->select();
    } else if (isset($mch_id)) {
        $orderList = $order->where('mch_id', $mch_id)->where('status', 'in', $status)->field($orderField)->limit($limit)->order('created_at desc')->select();
    } else if (isset($orderSn)) {
        $orderList = $order->where('order_sn', $orderSn)->where('status', 'in', $status)->field($orderField)->order('created_at desc')->select();
        // 获取优惠券
        if ($orderList && $orderList[0]['coupon_id']) {
            $couponInfo = Db::name('coupon')->where('coupon_id', $orderList[0]['coupon_id'])->field('coupon_name, money, condition')->find();
            $orderList[0]['coupon_name'] = $couponInfo['coupon_name'];
            $orderList[0]['coupon_money'] = number_format($couponInfo['money'], 2);
            $orderList[0]['coupon_condition'] = number_format($couponInfo['condition'], 2);
        }
    } else {
        $orderList = $order->where('status', 'in', $status)->field($orderField)->limit($limit)->order('created_at desc')->select();
    }
    if (!$orderList || count($orderList) == 0) return null;
    $orderList = collection($orderList)->toArray();

    // 对订单进行检测
    $updateOrderArr = [];
    $curTime = time();
    $order_detail = new Order_detail;
    foreach ($orderList as &$info) {
        $temp = [];
        $temp['order_id'] = $info['order_id'];
        if ($info['status'] == 1 && $curTime - $info['created_at'] > 600) {
            $temp['status'] = 6;
            $temp['cancel_at'] = $curTime;
            $updateOrderArr[] = $temp;
            $info['status'] = 6;
            $info['cancel_at'] = $curTime;
        }
        if ($info['status'] == 4 && $curTime - $info['finish_at'] > 7 * 86400) {
            $temp['status'] = 5;
            $temp['complete_at'] = $curTime;
            $info['status'] = 5;
            $info['complete_at'] = $curTime;
            $updateOrderArr[] = $temp;
        }
        if ($info['status'] == 1) {
            $info['left_time'] = 600 - ($curTime - $info['created_at']);
        }
        $info['created_at_ori'] = $info['created_at'];
        $info['pay_at'] = !empty($info['pay_at']) ? date('Y-m-d H:i:s', $info['pay_at']) : '';
        $info['finish_at'] = !empty($info['finish_at']) ? date('Y-m-d H:i:s', $info['finish_at']) : '';
        $info['cancel_at'] = !empty($info['cancel_at']) ? date('Y-m-d H:i:s', $info['cancel_at']) : '';
        $info['created_at'] = !empty($info['created_at']) ? date('Y-m-d H:i:s', $info['created_at']) : '';
        $info['complete_at'] = !empty($info['complete_at']) ? date('Y-m-d H:i:s', $info['complete_at']) : '';
        // 对status做转化处理
        switch ($info['status']) {
            case 1:
                $info['status_conv'] = '待付款';
                break;
            case 2:
                $info['status_conv'] = '待发货';
                break;
            case 3:
                $info['status_conv'] = '已发货';
                break;
            case 4:
                $info['status_conv'] = '待评价';
                break;
            case 5:
                $info['status_conv'] = '已完成';
                break;
            case 6:
                $info['status_conv'] = '已取消';
                break;
        }
        // 查订单详情
        $detailField = "g.goods_name, g.goods_img, d.goods_id, d.quantity, d.fee";
        $detail = $order_detail->alias('d')->join('mk_goods g', 'd.goods_id = g.goods_id', 'LEFT')->where('d.order_id', $info['order_id'])->field($detailField)->select();
        $detail = collection($detail)->toArray();
        $info['quantity'] = 0;
        foreach ($detail as $k => $v) {
            $detail[$k]['goods_img'] = $baseUrl . $v['goods_img'];
            $info['quantity'] += $v['quantity'];
        }
        $info['detail'] = $detail;
    }
    if (count($updateOrderArr) > 0) {
        $order->isUpdate()->saveAll($updateOrderArr);
    }
    return $orderList;
}

/**
 * 获取系统分类
 *
 * @param boolean $isAll 是否获取所有状态
 * @return void
 */
function getCatagory($field = null, $isAll = true, $isIndex = true)
{
    $baseUrl = "https://xnps.up.maikoo.cn/static";
    $field = $field ? $field : "cat_id, parent_id, cname, sort, img, status, created_at";
    $status = $isAll ? [0, 1] : [1];
    if ($isAll) {
        $status = [0, 1];
        $index = [0, 1];
    } else {
        $status = [1];
        $index = $isIndex ? [1] : [0, 1];
    }
    $catagory = new Catagory;
    $catagoryList = $catagory->where('status', 'in', $status)->where('is_index', 'in', $index)->field($field)->order('sort desc')->select();
    if (!$catagoryList || count($catagoryList) == 0) {
        return null;
    }
    $catagoryList = collection($catagoryList)->toArray();
    // 简单处理
    foreach ($catagoryList as &$info) {
        if (isset($info['cname'])) $info['cname'] = htmlspecialchars_decode($info['cname']);
        if (isset($info['img'])) $info['img'] = $baseUrl . $info['img'];
    }
    return $catagoryList;
}

/**
 * 通过分类ID获取对应的商品列表
 *
 * @param int $catId 分类ID
 * @param boolean $isAll 是否获取当前分类下所有的商品
 * @return void
 */
function getCatagoryById($catId, $field = null, $isAll = true, $pageNum = null)
{
    if (empty($catId) || !is_int($catId)) {
        return "Invaild Param";
    }
    $baseUrl = "https://xnps.up.maikoo.cn/static";
    $status = $isAll ? [0, 1, 2, 3] : [2];
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : "";
    $field = $field ? $field : "goods_id, goods_sn, goods_name, goods_img, market_price, shop_price, keywords, sort, unit, created_at, status";
    $goods = new Goods;
    $goodsList = $goods->where('cat_id', $catId)->where('status', 'in', $status)->field($field)->limit($limit)->select();
    if (!$goodsList || count($goodsList) == 0) {
        return null;
    }
    $goodsList = collection($goodsList)->toArray();
    // 简单处理
    foreach ($goodsList as &$info) {
        if (isset($info['goods_img'])) $info['goods_img'] = $baseUrl . $info['goods_img'];
        if (isset($info['market_price'])) $info['market_price'] = number_format($info['market_price'], 2);
        if (isset($info['shop_price'])) $info['shop_price'] = number_format($info['shop_price'], 2);
        if (isset($info['keywords'])) $info['keywords'] = str_replace(',', ' ', htmlspecialchars_decode($info['keywords']));
        if (isset($info['unit'])) $info['unit'] = htmlspecialchars_decode($info['unit']);
        if (isset($info['goods_name'])) $info['goods_name'] = htmlspecialchars_decode($info['goods_name']);
    }
    return $goodsList;
}

/**
 * 生成指定的编号
 *
 * @param integer $type 需要生成编号的类别 0 商家编号 1 商品编号 2 订单编号 3 优惠券编号
 * @param integer $typeId 对应类别的ID 商品编号传分类ID 订单编号传用户手机号后四位
 * @return void
 */
function generateSn($type = 0, $typeId = null)
{
    $typeSn = "";
    // 获取通用的时间字段 180827
    $timeStr = substr(date('Ymd', time()), 2);
    // 0 商家编号
    if ($type == 0) {
        $typeSn = $timeStr[rand(0, 5)] . $timeStr . $typeId;
    } else if ($type == 1) {
        if (strlen($typeId) == 1) $typeId = '00' . $typeId;
        if (strlen($typeId) == 2) $typeId = '0' . $typeId;
        $typeSn = $timeStr . $typeId;
    } else if ($type == 2) {
        // 保留微妙数小数点后三位 8 + 3 + 1
        $microTime = explode('.', microtime());
        $microTime = substr($microTime[1], 0, 3);
        $typeSn = $timeStr . $microTime . $typeId;
    } else if ($type == 3) {
        $typeSn = $timeStr . rand(0, 9);
    }
    return $typeSn;
}

/**
 * 计算两点地理坐标之间的距离
 * @param  Decimal $longitude1 起点经度
 * @param  Decimal $latitude1  起点纬度
 * @param  Decimal $longitude2 终点经度 
 * @param  Decimal $latitude2  终点纬度
 * @param  Int     $unit       单位 1:米 2:公里
 * @param  Int     $decimal    精度 保留小数位数
 * @return Decimal
 */
function getDistance($longitude1, $latitude1, $longitude2, $latitude2, $unit = 2, $decimal = 2)
{
    $EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI = 3.1415926;
    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;

    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI / 180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $distance = $distance * $EARTH_RADIUS * 1000;
    if ($unit == 2) {
        $distance = $distance / 1000;
    }

    return round($distance, $decimal);
}

/**
 * getRange description
 * @param  float $lng    经度
 * @param  float $lat    纬度
 * @param  float $raidus 半径(米)
 * @return array $range  经纬度范围
 */
function getRange($lng, $lat, $raidus, $decimal = 6)
{
    $PI = 3.14159265;
    $latitude = $lat;
    $longitude = $lng;
    $degree = 40075016 / 360;
    $raidumketre = $raidus;
    $radiusLat = $raidumketre / $degree;
    $minLat = $latitude - $radiusLat;
    $maxLat = $latitude + $radiusLat;
    $mpdLng = $degree * cos($latitude * ($PI / 180));
    $radiusLng = $raidumketre / $mpdLng;
    $minLng = $longitude - $radiusLng;
    $maxLng = $longitude + $radiusLng;
    $range = array('minLng' => number_format($minLng, $decimal), 'maxLng' => number_format($maxLng, $decimal), 'minLat' => number_format($minLat, $decimal), 'maxLat' => number_format($maxLat, $decimal));
    return $range;
}