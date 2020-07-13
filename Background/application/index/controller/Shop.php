<?php



/**

 * 打印店小程序后台处理相关

 * @author Locked

 * createtime 2018-03-06

 */



namespace app\index\controller;



use think\Controller;

use think\Request;

use think\Cache;

use think\Db;

use think\Session;

use think\File;



use app\index\model\Userinfo;

use app\index\model\Bannerlist;

use think\migration\db\Column;

use app\index\model\Column;

use app\index\model\Catagory;

use app\index\model\Minipro;



class Shop extends Controller{

    

    const APPID = "wx57beee95d7c48bbe";

    const APPSECRET = "774e5f55826cce1d828ab7faf14c3e09";

    const DS = DIRECTORY_SEPARATOR;



    /**

     * 根据关键词搜索商品信息

     *

     * @param Request $request

     * @return void

     */

    public function searchGoods(Request $request){

        // 获取传递过来的查询数据

        $inputVal = htmlspecialchars($request -> param('inputVal'));

        $goods = new Goods;

        $searchGoodsInfo = $goods -> where('is_active', 1) -> where('is_delete', 0) -> where('name', 'like', "%".$inputVal."%") -> limit(4) -> field('goods_id, name, pic') -> select();

        if ($searchGoodsInfo) {

            $res['goods'] = $searchGoodsInfo;

            $res['code'] = "200";

            $res['message'] = "search Success";

        }else{

            $res['code'] = "401";

            $res['message'] = "no Goods has been found";

        }

        return json_encode($res);

    }



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

        $siteroot = "https:/ft.up.maikoo.cn";

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

        if ($dailyList && sizeof($dailyList) > 0) {

            foreach ($dailyList as $k => $v) {

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

        $res['catagory'] = $miniCatagory;

        $res['culumn'] = $miniColumn;

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

    public function getMiniPro($miniId = null, $catagoryId = null, $limit = 2){

        

        $siteroot = 'https://minipro.up.maikoo.cn';

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

                    $v['create_time_convert'] = date('Y-m-d H:i:s', $v['create_time']);

                    $v['avatarUrl'] = $siteroot . $v['avatarUrl'];

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

                    $v['create_time_convert'] = date('Y-m-d H:i:s', $v['create_time']);

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

     * 获取商品列表信息

     * 每次获取12个

     *

     * @param Request $request

     * @return json goodsList

     */

    public function getGoods(Request $request){

        // 先直接接入数据库 每次获取12个

        $goodsCatId = intval($request -> param('catId'));

        $goodsPage = intval($request -> param('page'));

        $searchStart = $goodsPage * 12;

        $searchEnd = $searchStart + 11;

        $goods = new Goods;

        // 获取商品总数

        $goodsCount = Cache::get('shopGoodsCount');

        if (!$goodsCount) {

            $goodsCount = $goods -> where('is_active', 1) -> where('is_delete', 0) -> field('catagory_id') -> select();

            $goodsCountArr = array();

            foreach ($goodsCount as $k => $v) {

                $goodsCountArr[]= $v['catagory_id'];

            }

            // 计算每个分类的总数

            $goodsCount = array_count_values($goodsCountArr);

            Cache::set('shopGoodsCount', $goodsCount, 0);

        }

        // 获取当前分类的商品总数

        $currentCatCount = 0;

        foreach ($goodsCount as $k => $v) {

            if ($goodsCatId == $k) {

                $currentCatCount = $v;

            }

        }

        // 如果当前分类没有商品

        if ($currentCatCount == 0) {

            $res['code'] = "201";

            $res['message'] = "No Goods";

            return json_encode($res);

        }

        // 判断商品的获取范围

        if ($searchEnd >= $currentCatCount) {

            $searchEnd = $currentCatCount;

            $res['isEnd'] = true;

        }else{

            $res['isEnd'] = false;

        }

        // 获取商品信息

        $goodsList = $goods -> where('is_active', 1) -> where('is_delete', 0) -> where('catagory_id', $goodsCatId) -> field('goods_id, catagory_id, name, price, shop_price, pic, spec, promotion_id') -> limit($searchStart, $searchEnd) -> order('orderby desc') -> select();

        if ($goodsList) {

            // 对图片地址做处理

            foreach ($goodsList as $k => $v) {

                $v['pic'] = "https://ft.up.maikoo.cn" . $v['pic'];

            }

            $res['code'] = "200";

            $res['goods'] = $goodsList;

            $res['message'] = "Search Success";

        }

        return json_encode($res);

    }





    public function authUser(Request $request){



        $name = htmlspecialchars($request -> param('name'));

        $identID = $request -> param('identID');

        $telNum = $request -> param('telNum'); 

        $userOpenid = $request -> param('openid');



        // 先将其插入数据库

        $userinfo = new Userinfo;

        $update = $userinfo -> where('user_openid', $userOpenid) -> update(['name' => $name, 'telNum' => $telNum, 'identID' => $identID, 'is_auth' => 1]);

        if ($update) {

            // 更新用户信息成功

            // 将用户信息更新至缓存

            $userAccountInfo = Cache::get('userAccountInfo');

            foreach ($userAccountInfo as $k => $v) {

                if ($v['user_openid'] == $userOpenid) {

                    $userAccountInfo[$k]['isAuth'] = true;

                    $userAccountInfo[$k]['identID'] = $identID;

                    $userAccountInfo[$k]['telNum'] = $telNum;

                    $userAccountInfo[$k]['userName'] = $name;

                    break 1;

                }

            }

            // 更新缓存

            Cache::set('userAccountInfo', $userAccountInfo, 0);

            $res['code'] = "200";

            $res['message'] = "User Auth Success";

        }else{

            $res['code'] = "200";

            $res['message'] = "User Auth Falied";

        }

        return json_encode($res);

    }



    /**

     * 通过商品id查找对应商品

     *

     * @param Request $request

     * @return void

     */

    public function getGoodsById(Request $request){

        $goodsid = intval($request -> param('goodsid'));

        // 先直接去数据库找

        $goods = new Goods;

        $goodsInfo = $goods -> where('is_active', 1) -> where('goods_id', $goodsid) -> where('is_delete', 0) -> field('name, price, shop_price, pic, spec, is_on_promotion, promotion_id, is_distri, dis_percent, parent_dis_percent, grand_dis_percent') -> find();

        if (!$goodsInfo) {

            // 如果当前商品不存在

            $res['code'] = "401";

            $res['message'] = "Goods Not Exist";

            return json_encode($res);

        }

        // 如果当前商品有参与活动 获取对应活动信息

        // 先直接去数据库找

        if ($goodsInfo['is_on_promotion']) {

            // 判断当前活动是否有效

            // dump($this -> checkPromotion($goodsInfo['promotion_id'])); die;

            if ($promotionInfo = $this -> checkPromotion($goodsInfo['promotion_id'])) {

                $goodsInfo['promotion'] = $promotionInfo;

            }else {

                // 当前活动已失效 更新对应数据库

                $goodsInfo['is_on_promotion'] = 0;

                // $goodsInfo['promotion_id'] = null;

                $goods -> update(['goods_id' => $goodsid, 'is_on_promotion' => 0]);

                // 移除本地商品缓存

                Cache::rm('shopGoodsInfo');

            }

        }

        $userid = $request -> param('userid');

        $goodsInfo['user_id'] = $userid;

        $goodsInfo['goods_id'] = $goodsid;

        $res['code'] = "200";

        $res['message'] = "Goods Search Success";

        $res['goodsInfo'] = $goodsInfo;

        // 如果存在父级id就去更新用户信息

        $parentid = intval($request -> param('parentid'));

        if ($parentid != "no") {

            $this -> updateUserDistri($userid, $goodsid, $request -> param('openid'), $parentid);

        }

        return json_encode($res);

    }



    /**

     * 判断当前活动是否有效

     * 当活动有效时返回活动详情，活动无效时返回FALSE

     *

     * @param array $promotionID 促销活动ID

     * @return boolean $isPromotionEffective 是否有效

     */

    public function checkPromotion($promotionID){

        // 先判断当前缓存是否存在

        $promotion = new Promotion;

        $promotionInfo = $this -> getPromotionInfo();

        // dump($promotionInfo); die;

        $currentTime = time();

        // 保存失效的活动信息

        $invalidPromotion = array();

        // 当前活动是否有效

        $isPromotionEffect = false;

        foreach ($promotionInfo as $k => $v) {

            // 先判断当前活动的有效期

            if (intval($currentTime) > intval($v['end_time'])) {

                $v['is_active'] = 0;

                $invalidPromotion []= $v;

            }

            if ($v['promotion_id'] == $promotionID) {

                // 如果查询到当前活动存在则去判断是否有效

                if (!intval($v['is_active']) || ($v['last_paused_time'] && !$v['last_continue_time'])) {

                    break 1;

                }

                $isPromotionEffect = $v;

            }

        }

        // 如果有失效的活动要去更新缓存和数据库

        if (sizeof($invalidPromotion) > 0) {

            foreach ($invalidPromotion as $k => $v) {

                $promotion -> update(['is_active' => 0, 'promotion_id' => $v['promotion_id']]);

            }

            // 更新缓存

            $promotionInfo = $promotion -> where('is_active', 1) -> select();

            Cache::set('promotionInfo', $promotionInfo, 10);

        }

        return $isPromotionEffect;

    }



    /**

     * 更新用户分销信息

     * 使用userid和openid双重认证

     *

     * @param int $userid       用户ID

     * @param int $goodsid      商品ID

     * @param varchar $openid   用户openid

     * @param int $parentid     用户父级ID

     * @return void

     */

    public function updateUserDistri($userid, $goodsid, $openid, $parentid){

        $distribution = new Distribution;

        $distribution_log = new Distribution_log;

        $userDistriInfo = Cache::get('userDistriInfo');

        if (!$userDistriInfo) {

            $userDistriInfo = $distribution -> field('user_id, user_openid, parent_id, grand_id, goods_id') -> select();

            if (!$userDistriInfo) {

                $userDistriInfo = Cache::set('userDistriInfo', $userDistriInfo, 0);

            }else{

                $userDistriInfo = array();

            }

        }

        // 存储当前分销表信息

        $currentDistri = array();

        $currentDistri['user_id'] = $userid;

        $currentDistri['user_openid'] = $openid;

        $currentDistri['goods_id'] = $goodsid;

        // 如果当前分销表里没有数据

        if (sizeof($userDistriInfo) == 0) {

            $currentDistri['parent_id'] = $parentid;

            $currentDistri['grand_id'] = null;

            $currentDistri['gen_parent_time'] = $currentDistri['gen_grand_time'] = time();

            $userDistriInfo []= $currentDistri;

            // 更新缓存

            $userDistriInfo = Cache::set('userDistriInfo', $userDistriInfo, 0);

            // 将当前数据插入数据库

            $insert = $distribution -> save($currentDistri);

            $insertResult = $insert ? 'Success' : 'Failed';

            $distribution_log -> insert(['goods_id' => $goodsid, 'user_id' => $userid, 'user_openid' => $openid, 'parent_id' => $parentid, 'create_time' => time(), 'log_info' => "$userid add Parent $insertResult"]);

            return;

        }



        // 如果当前分销表的数据不为空

        // A -> B -> C -> D  不能有 C -> A 只能有 D -> A

        $isCanBecomeSon = true;                 // 当前传递的父级ID是否可以成为当前用户ID的父级

        $currentUserPosition = null;            // 当前用户在分销缓存中的位置

        $currentParentPosition = null;          // 当前父级用户在分销缓存中的位置

        $fatherKeyArr = array();                   // 用户更新数据库的array

        foreach ($userDistriInfo as $k => $v) {

            if ($goodsid == $v['goods_id']) {

                // 判断当前用户id是否在此缓存中存在 如存在则获取对应位置

                if (!$currentUserPosition && $userid == $v['user_id']) {

                    $currentUserPosition = $k;

                }

                // 如果当前ID为缓存中某个ID的父级

                // 那么需要去判断对应的userid是否有爷级ID 如果有则直接跳过 如果没有 则需进一步判断

                if ($v['parent_id'] == $userid && !$v['grand_id']) {

                   $fatherKeyArr []= $v;

                }

                // 判断当前父级ID是否在此缓存中

                if (!$currentParentPosition && $v['user_id'] == $parentid) {

                    $currentParentPosition = $k;

                }

            }

        }



        // 判断当前ID是否在此缓存中

        if (!$currentUserPosition) {

            // 如果不在 更新父级ID

            $currentDistri['parent_id'] = $parentid;

            $currentDistri['gen_parent_time'] = time();

        }else{

            // 如果在 并且当前传递过来的父级元素也在

            $currentDistri['grand_id'] = $parentid;

            $currentDistri['gen_grand_time'] = time();

        }

        // 更新数据

        $distribution -> save($currentDistri);



        // 判断是否有可称为爷级元素的

        // 做第二次循环 携带的父级ID是否能够成为已经是别人父级ID的当前ID对应的子级ID的爷级ID

        if (sizeof($fatherKeyArr) > 0) {

            $updateArr = array();

            foreach ($fatherKeyArr as $k => $v) {

                foreach ($userDistriInfo as $ke => $va) {

                    if ($v['goods_id'] == $va['goods_id'] && $v['user_id'] == $va['user_id']) {

                        if (!$va['grand_id'] || $va['grand_id'] != $parentid) {

                            $tempArr = array();

                            $tempArr['grand_id'] = $parentid;

                            $tempArr['user_id'] = $va['user_id'];

                            $tempArr['gen_parent_time'] = time();

                            $updateArr []= $tempArr;

                            break 1;

                        }

                    }

                }

            }

            // 如果有需要更新的数据

            if (sizeof($fatherKeyArr) > 0) {

                $distribution -> saveAll($updateArr);

            }

        }

        return;

    }



    /**

     * 将商品添加至购物车

     *

     * @param Request $request

     * @return void

     */

    public function addToCart(Request $request){

        

        // 获取传递过来的商品信息

        $goodsInfo = $request -> param('goodsDetail/a');

        $userOpenid = $request -> param('openid');



        // 可增加判断当前商品是否存在

        // 判断是否有购物车缓存

        $userCartInfo = Cache::get('userCartInfo');

        // dump($userCartInfo);die;

        // 如果当前购物车中有该商品，则将对应商品的数量+1

        $isHaveCurrentGoods = false;

        // 构建返回数组

        $res = array();

        if ($userCartInfo && sizeof($userCartInfo) > 0) {

            foreach ($userCartInfo as $k => $v) {

                if ($userOpenid == $v['user_openid'] && $goodsid == $v['goodsInfo']['goodsid']) {

                    $isHaveCurrentGoods = true;

                    $v['goodsInfo']['quantity'] += 1;

                    $res['code'] = "201";

                    $res['message'] = "Current Goods'num plus 1";

                    break 1;

                }

            }

        }

        // 如果缓存不存在或者当前购物车缓存中没有该商品

        if (!$userCartInfo || !$isHaveCurrentGoods) {

            $currentUserCart = array();

            $currentUserCart['goodsInfo'] = $goodsInfo;

            $currentUserCart['user_openid'] = $userOpenid;

            $userCartInfo []= $currentUserCart;

            $res['code'] = "200";

            $res['message'] = "Add Goods to Cart Success";

        }



        // 更新缓存

        Cache::set('userCartInfo', $userCartInfo, 0);

        return json_encode($res);

    }



    /**

     * 获取购物车信息

     * 需要去当前商品列表做判断，是否有变更

     *

     * @return void

     */

    public function getCartList(Request $request){

        // 获取用户openid

        $userOpenid = $request -> param('openid');



        // 非空返回

        if(!$userOpenid){

            $res['code'] = "400";

            $res['msg'] = "Invail UserOpenid";

            return json_encode($res);

        }

        $res['isHaveChange'] = false;

        $res['code'] = "400";



        $userCartInfo = Cache::get('userCartInfo');

        if (!$userCartInfo) {

            $res['code'] = "401";

            $res['message'] = "No Cart Exist";

            return json_encode($res);

        }



        // dump($userCartInfo); die;



        // 判断当前商品缓存是否存在

        $shopGoodsInfo = $this -> getShopGoodsInfo();



        // dump($shopGoodsInfo); die;



        // 是否有商品变动

        $isHaveCartChange = false;

        // 当前用户的购物车

        $currentUserCart = array();

        // 判断当前商品信息是否有变动

        foreach ($userCartInfo as $k => $v) {

            if ($v['user_openid'] = $userOpenid) {

                $isHaveCart = true;

                foreach ($shopGoodsInfo as $ke => $va) {

                    if($va != $v){

                        $isHaveCartChange = true;

                        $userCartInfo['$k'] = $va;

                    }

                }

                $currentUserCart []= $userCartInfo[$k];

            }

        }

        // 如果当前用户的购物车信息有变动 就更新缓存

        if ($isHaveCartChange) {

            Cache::set('userCartInfo', $userCartInfo, 0);

        }

        $res['isHaveChange'] = $isHaveCartChange;

        if (sizeof($currentUserCart) > 0) {

            $res['code'] = "200";

            $res['cartList'] = $currentUserCart;

        }

        return json_encode($res);

        // 如果购物车中有商品 则判断是否有变更 有则需要更新当前缓存

    }



    /**

     * 获取当前商品列表 缓存

     *

     * @return array $shopGoodsInfo

     */

    public function getShopGoodsInfo(){

        $promotion = new Promotion;

        $shopGoodsInfo = Cache::get('shopGoodsInfo');

        if (!$shopGoodsInfo) {

            $goods = new Goods;

            $shopGoodsInfo = $goods -> where('is_delete', 0) -> where('is_active', 1) -> field('goods_id, name, pic, price, shop_price,is_on_promotion, promotion_id, is_distri, dis_percent, parent_dis_percent, grand_dis_percent') -> select();

            $promotionInfo = $this -> getPromotionInfo();

            foreach ($shopGoodsInfo as $k => $v) {

                if ($v['is_on_promotion']) {

                    foreach ($promotionInfo as $ke => $va) {

                        if ($v['promotion_id'] == $va['promotion_id']) {

                            $shopGoodsInfo[$k]['promotion'] = $v;

                        }

                    }

                }

            }

            Cache::set('shopGoodsInfo', $shopGoodsInfo, 60);

        }



        return $shopGoodsInfo;

    }



    /**

     * 获取促销活动 缓存

     *

     * @return array $promotionInfo

     */

    public function getPromotionInfo(){

        $promotionInfo = Cache::get('promotionInfo');

        if (!$promotionInfo) {

            $promotion = new Promotion;

            $promotionInfo = $promotion -> where('is_active', 1) -> select();

            Cache::set('promotionInfo', $promotionInfo, 10);

        }

        return $promotionInfo;

    }



    /**

     * 获取用户的推介列表

     * 每次获取12条数据

     * 

     * @param int $pageNum  页数

     * @param int $num  获取数量

     * @return void

     */

    public function getPromotionList(Request $request){

        $pageNum = intval($request -> param('pageNum'));

        $num = intval($request -> param('num'));

        $userid = $request -> param('userid');

        $userOpenid = $request -> param('openid');



        // 临时测试

        $shopGoodsInfo = Cache::get('shopGoodsInfo');

        $userCartInfo['user_openid'] = $userOpenid;

        $userCartInfo['goodsInfo'] = $shopGoodsInfo[0];

        Cache::set('userCartInfo', $userCartInfo, 0);

    



        $distribution_fee = new Distribution_fee;



        // 判断页码

        $searchStart = $pageNum * $num;

        $searchEnd = $pageNum == 0 ? $num : ($pageNum + 1) * $num - 1;

        if ($distribution_count = $distribution_fee -> count() < $searchEnd) {

            $searchEnd = $distribution_count > 1 ? 1 : $distribution_count - 1;

            $res['isHaveMore'] = false;

        }else{

            $res['isHaveMore'] = true;

        }

        $res['end'] = $searchEnd;

        $res['start'] = $searchStart;



        // 先直接从数据库获取

        $distributionFeeList = $distribution_fee -> where('user_id', $userid) -> where('user_openid', $userOpenid) -> field('dis_fee_id, dis_fee, user_id, user_openid, user_pic, user_nickname, parent_id, parent_pic, parent_nickname, create_time, goods_id, goods_name') -> order('create_time desc') -> limit("$searchStart, $num") -> select();

        if ($distributionFeeList) {

            // 数据处理

            // dump($distributionFeeList); die;

            foreach ($distributionFeeList as $k => $v) {

                $v['create_time_convert'] = date('Y-m-d H:i:s', $v['create_time']);

                // $v['parent_pic'] = "https://ft.up.maikoo.cn" . $v['parent_pic'];

                // $v['user_pic'] = "https://ft.up.maikoo.cn" . $v['user_pic'];

                $v['dis_fee'] = round(floatval($v['dis_fee'] / 100), 2);

            }

            $res['code'] = "200";

            $res['distribution'] = $distributionFeeList;

            $res['message'] = "Search Success";

        }else{

            $res['code'] = "400";

            $res['message'] = "Network Error";

        }

        return json_encode($res);

    }





    /**

     * 获取活动详情

     *

     * @param Request $request

     * @return void

     */

    public function getActivityList(Request $request){



        $userOpenid = $request -> param('openid'); 

        // 获取活动缓存

        $activityList = $this -> getActivityInfo();

        // 如果当前没有活动则直接返回

        if(sizeof($activityList) == 0){

            $res['code'] = "201";

            $res['msg'] = "No Activity";

            return json_encode($res);

        }



        // 整理ActivityList

        foreach ($activityList as $k => $v) {

            $activityList[$k]['countDown'] = $v['end_time'] - time();

        }



        $res['code'] = "200";

        $res['activity'] = $activityList;

        $res['msg'] = "Get Activity Success";



        $activityUserList = $this -> getActivityUser();

        // 如果该用户参与活动情况为0 直接返回

        if(sizeof($activityUserList) == 0){

            return json_encode($res);

        }

        // 判断当前用户是否有参与该活动

        foreach ($activityUserList as $k => $v) {

            foreach ($activityList as $ke => $va) {

                if ($v['activity_id'] == $va['activity_id']) {

                    $activityList[$ke]['is_join'] = true;

                    break 1;

                }

            }

        }



        $res['activity'] = $activityList;

        return json_encode($res);

        // 判断是否有过期活动，有就需要去更新数据库

    }



    public function getActivityUser($activityID = null){

        $activityUser = Cache::get('activityUser');

        if (!$activityUser) {

            $activity_user = new Activity_user;

            $activityUser = $activity_user -> field('activity_id, user_openid, user_id') -> select();

            Cache::set('activityUser', $activityUser, 30);

        }

        // 如果有传递activityID 代表要获取某个对应活动的人数

        if ($activityID) {

            $current = array();

            foreach ($activityUser as $k => $v) {

                $v['activity_id'] == $activityID;

                $current []= $v;

            }

            if(sizeof($current) == 0){

                return null;

            }else{

                return $current;

            }

        }

        return $activityUser;

    }



    /**

     * 获取活动信息

     *

     * @return array $activityList

     */

    public function getActivityInfo($activityID = null){

        $activity = new Activity;

        $activityList = Cache::get('activityList');

        // 判断当前列表是否需要检查

        $isNeedCheck = true;

        if (!$activityList) {

            $isNeedCheck = false;

            $activityList = $activity -> where('is_active', 1) -> field('activity_id, name, brief, detail, pic, start_time, end_time') -> order('start_time desc') -> select();

            Cache::set('activityList', $activityList, 0);

        }

        if (!$isNeedCheck) {

            return $activityList;

        }

        $expiredActivity = array();

        $showActivity = array();

        // 判断是否有活动过期

        foreach ($activityList as $k => $v) {

            if ($v['end_time'] < time()) {

                $tempArr['is_active'] = 0;

                $tempArr['activity_id'] = $v['activity_id'];

                $expiredActivity []= $tempArr;

                // $activityList[$k]['is_active'] = 0;

            }else{

                $v['pic'] = "https://ft.up.maikoo.cn" . $v['pic'];

                $showActivity []= $v;

            }

        }

        $activityList = $showActivity;

        // 如果有过期活动则更新数据库

        if (sizeof($expiredActivity) > 0) {

            $activity -> saveAll($expiredActivity);

            // 更新活动缓存

            Cache::set('activityList', $activityList, 0);

        }

        // 如果有传递activitID 则是获取该ID对应的活动

        if ($activityID) {

            foreach ($activityList as $k => $v) {

                if ($v['activity_id'] == $activityID) {

                    return $v;

                }

            }

            return null;

        }



        return $activityList;

    }



    /**

     * 通过活动ID获取活动详情

     * 

     * @param int $activityID

     *

     * @return array $activity

     */

    public function getActivityByActivityID(Request $request){



        $activityID = intval($request -> param('activityID'));

        $userOpenid = $request -> param('openid');

        // 直接从数据库拉取消息

        $activity = new Activity;

        $activityInfo = $activity -> where('activity_id', $activityID) -> find();

        if($activityInfo){

            $res['code'] = "200";

            $res['msg'] = "Get Activity Success";

            // 对activity做处理

            if (intval($activityInfo['end_time']) - intval(time()) > 0) {

                $activityInfo['is_active'] = 0;

                $activity -> update(['is_active' => 0, 'activity_id' => $activityInfo['activity_id']]);

                $res['code'] = "201";

                $res['msg'] = "Activity Expired";

            }

            if ($activityInfo['is_active']) {

                // 如果当前活动有效 则计算倒计时

                $activityInfo['countDown'] = intval($activityInfo['end_time']) -> intval(time());

            }

            // 判断当前用户是否参与了当前活动

            $res['isJoin'] = $this -> checkUserInActivity($activityID, $userOpenid);

            $res['activity'] = $activityInfo;

        }else{

            $res['code'] = "400";

            $res['msg'] = "Activity Doesn't Exist";

        }

        return json_encode($res);

    }



    /**

     * 判断当前用户是否参加了对应的活动

     *

     * @param int $activityID

     * @param string $userOpenid

     * @param int $userid

     * @return Boolean $isJoin

     */

    public function checkUserInActivity($activityID, $userOpenid = null, $userid = null){

        $isJoin = false;

        $activityList = $this -> getActivityUser($activityID);

        // 如果当前活动没有参与用户

        if (!$activityList) {

            return $isJoin;

        }

        // 如果传递过来的是openid （默认）

        if ($userOpenid) {

            foreach ($activityList as $k => $v) {

                if ($v['user_openid'] == $userOpenid && $v['activity_id'] == $activityID) {

                    $isJoin = true;

                }

            }

        }

        // 如果传递过来的是userid

        if ($userid) {

            foreach ($activityList as $k => $v) {

                if ($v['user_id'] == $userid && $v['activity_id'] == $activityID) {

                    $isJoin = true;

                }

            }

        }

        return $isJoin;

    }



    /**

     * 用户参加当前活动

     *

     * @return array 是否参加成功

     */

    public function activitySingUp(Request $request){



        $userOpenid = $request -> param('openid');

        $userid = $request -> param('userid');

        $activityID = $request -> param('activityID');

        $userName = $request -> param('name');

        

        // 判断当前活动是否失效

        $activityList = $this -> getActivityInfo($activityID);

        if (!$activityList) {

            // 当前活动已失效

            $res['code'] = "201";

            $res['msg'] = "当前活动已失效";

            return json_encode($res);

        }

        // 当前活动未失效

        $activity_user = new Activity_user;

        $insert = $activity_user -> insert(['activity_id' => $activityID, 'user_openid' => $userOpenid, 'user_id' => $userid, 'join_time' => time(), 'user_name' => $userName]);

        if ($insert) {

            $res['code'] = "200";

            $res['msg'] = "Activity Join Success";

        }else{

            $res['code'] = "400";

            $res['msg'] = "NetWork Error";

        }

        return json_encode($res);

    }



    /**

     * 获取用户参加的活动列表

     *

     * @return void

     */

    public function getUserActivity(Request $request){

        $suerOpenid = $request -> param('openid');

        // 获取所有活动列表

        $activity = new Activity;

        $activityList = $activity -> field('activity_id, name, brief, detail, pic, is_active') -> order('end_time desc') -> select();

        // 判断当前是否有活动

        if (!$activityList) {

            $res['code'] = "201";

            $res['msg'] = "No Activity Exist";

            return json_encode($res);

        }

        $userActivity = array();

        foreach ($activityList as $k => $v) {

            $v['isJoin'] = $this -> checkUserInActivity($v['activity_id'], $suerOpenid);

            if ($v['isJoin']) {

                $userActivity []= $v;

            }

        }

        if (sizeof($userActivity) == 0) {

            $res['code'] = "202";

            $res['msg'] = "Current User Didn't Join Any Activity";

        }else{

            $res['code'] = "200";

            $res['activity'] = $userActivity;

            $res['msg'] = "Get User Activity Success";

        }

        return json_encode($res);

    }



    /**

     * 获取用户订单列表

     * 每次获取12条

     *

     * @return void

     */

    public function getOrderList(Request $request){

        $userOpenid = $request -> param('openid');

        $pageNum = $request -> param('pageNum');

        $totalNum = $request -> param('totalNum');

        $action = $request -> param('action');

        $isHaveMore = true;

        $order = new Order;

        $order_detail = new Order_detail;



        $count = $order -> where('user_openid', $userOpenid) -> where('is_delete', 0) -> count();

        if (!$totalNum) {

            $totalNum = $count;

        }else{

            if (intval($totalNum) == $count && $action == "refresh") {

                $res['code'] = "203";

                $res['msg'] = "No New Order";

                return json_encode($res);

            }

        }

        $searchStart = $pageNum * 15;

        if ($searchStart + 15 > $totalNum) {

            $isHaveMore = false;

        }

        // 获取订单

        $orderList = $order -> where('user_openid', $userOpenid) -> where('is_delete', 0) -> field('order_id, user_openid, user_id, totalFee, express_co, express_num, express_fee, state') -> order('create_time desc') -> limit($searchStart, 12) -> select();

        if (!$orderList) {

            $res['code'] = "201";

            $res['isHaveMore'] = $isHaveMore;

            $res['msg'] = "Current User Didn't Have Any Order";

            return json_encode($res);

        }

        foreach ($orderList as $k => $v) {

            // 对时间做处理

            $v['create_time_convert'] = date('Y-m-d H:i:s', $v['create_time']);

            // 对顶单状态做处理

            if ($v['state'] == 0) {

                $v['state_convert'] = '待付款';

            }else if ($v['state'] == 1) {

                $v['state_convert'] = '待发货';

            }else if ($v['state'] == 2) {

                $v['state_convert'] = '已发货';

            }else if ($v['state'] == 3) {

                $v['state_convert'] = '待评价';

            }else if ($v['state'] == 4) {

                $v['state_convert'] = '已完成';

            }else if ($v['state'] == 5) {

                $v['state_convert'] = '已取消';

            }else if ($v['state'] == 6) {

                $v['state_convert'] = '售后申请';

            }else if ($v['state'] == 7) {

                $v['state_convert'] = '正在退款';

            }else if ($v['state'] == 8) {

                $v['state_convert'] = '退款成功';

            }

            // $v['pay_time_convert'] = date('Y-m-d H:i:s', $v['pay_time']);

            // 获取订单详情

            $v['detail'] = $order_detail -> where('order_id', $v['order_id']) -> field('order_id, goods_id, name, pic, quantity, price, is_on_promotion, promotion_name, promotion_count') -> select();

            // 获取商品总数

            $v['goodsTotalNum'] = 0;

            if ($v['detail']) {

                foreach ($v['detail'] as $ke => $va) {

                    $v['goodsTotalNum'] += $va['quantity'];

                }

            }

        }

        

        $res['code'] = "200";

        $res['order'] = $orderList;

        $res['isHaveMore'] = $isHaveMore;

        $res['totalNum'] = $totalNum;

        $res['msg'] = "Get OrderList Success";

        return json_encode($res);

    }



    /**

     * 用户订单新增

     *

     * @return void

     */

    public function addOrder(Request $request){

        $reqestData = $request -> param('reqestData/a');

        // 非空返回

        if(!$reqestData){

            $res['code'] = "401";

            $res['msg'] = 'Invaild Request';

            return json_encode($res);

        }

        // 数据处理

        $order = new Order;

        $order_detail = new Order_detail;

        // 订单号

        $orderInfo['order_id'] = $reqestData['orderid'] ? $reqestData['orderid'] : $this -> getTradeNo();

        // 订单状态

        $orderState = 0; // 默认为0

        if($reqestData['is_pay'] && $reqestData['is_ticket'] && !$reqestData['address']){

            // 如果是票券类 已付款 不邮寄 那就是待使用   使用过后就变成待消费 => state = 3

            $orderState = 7;

        }else if($reqestData['is_pay'] && $reqestData['address']){

            // 如果是票券类 已付款 并且需要邮寄 那就是待发货

            $orderState = 1;

        }

        $orderInfo['state'] = $orderState;

        $orderInfo['user_openid'] = $reqestData['openid'];

        $orderInfo['user_id'] = $reqestData['userid'];

        $orderInfo['user_pic'] = $reqestData['pic'];

        $orderInfo['user_name'] = $reqestData['userName'];

        $orderInfo['totalFee'] = $reqestData['totalFee'];

        $orderInfo['totalDisFee'] = $reqestData['totalDisFee'];

        $orderInfo['pay_time'] = intval($reqestData['totalFee']) == 0 ? time() + 5 : 0;

        $orderInfo['telNum'] = $reqestData['telNumber'];

        $orderInfo['address'] = $reqestData['address'];

        $orderInfo['express_co_id'] = $reqestData['express_co_id'];

        $orderInfo['express_co'] = $reqestData['express_co'];

        $orderInfo['express_fee'] = $reqestData['express_fee'];

        $orderInfo['message'] = htmlspecialchars($reqestData['message']);

        $orderInfo['create_time'] = time();

        



        // 构造订单详情



    }



    /**

     * 用户购物车界面卸载时 onUnload方法 进行用户购物车的数据更新

     *

     * @param Request $request

     * @return void

     */

    public function updateUserCart(Request $request){

        $userCartInfo = Cache::get('userCartInfo');

        $userOpenid = $request -> param('openid');

        // 直接覆盖

        foreach ($userCartInfo as $k => $v) {

            if ($v['user_openid'] == $userOpenid) {

                $v['goodsInfo'] = $request -> param('goodsInfo/a');

                break;

            }

        }

    }



    /**

     * 产生订单号

     * @return string 订单号 生成规则为 0323 + timestamp后二位 + microtime前三位(小数点后)

     */

    public function getTradeNo(){

        $out_trade_no = "";

        $micorTime = microtime();

        $micorTime = explode('.', $micorTime);

        $micorTime = substr($micorTime[1], 0, 3);

        $out_trade_no = date('md', time()) . substr(strval(time()), -3, -1) . $micorTime;

        // $out_trade_no .= substr(time(), -4);

        return $out_trade_no;

    }







    public function test(){

        // $goods = new Goods;

        // $goodsList = $goods-> where('is_active', 1) -> where('is_delete', 0) -> field('catagory_id') -> select();

        // dump($goodsList);

        Cache::clear();

        // echo date('Y年m月d日 H:i:s', intval(time()));

        // echo time();

        // Cache::rm('userCartInfo');

        // echo time();

    }







    /**

     * 图片上传

     * @return [type] [description]

     */

    public function imageUpload(){

        // 获取表单上传文件

        $request = Request::instance();

        $file = $request -> file('file');

        // 当前打印类别判断 single mulit ident

        // $state = $request -> pram('state');

        // $siteroot = "https://print.up.maikoo.cn";

        // $fileName = md5($file['info']['tmp_name']);

        $targetDir = "." . DS . 'public' . DS . 'uploads';

        $save = $file -> move($targetDir);

        if ($save) {

            $res['code'] = "200";

            $res['fileName'] = $save -> getSaveName();

            $res['message'] = "success";

        }else{

            $res['code'] = "400";

            $res['message'] = "NETWORK ERROR";

        }

        return json_encode($res);

    }





    /**

     * 获取店家的QRCode

     * 用户可添加店家进行特殊打印文件的发送

     * @return json $res 用户的二维码链接地址

     */

    public function getAdminWxQrCode(){

        $qrCode = Cache::get('qrCode');

        // $qrCode = "";

        if (!$qrCode) {

            $qrCode = Db::name('admin') -> where('idx', 1) -> find();

            $qrCode = $qrCode['qrcode'];

            // $res['src'] = $qrCode['qrcode'];

            Cache::set('qrCode', $qrCode, 86400);

        }

        $res['src'] = $qrCode;

        $res['code'] = "200";

        $res['message'] = "success";

        return json_encode($res);

    }





    /**

     * 设置用户订单

     * @return json 下单状态

     */

    public function setUserPrint(){

        $request = Request::instance();

        $userOpenid = $request -> param('openid');

        $printlist = new Printlist;

        // 首先判断用户是否有未完成订单 如果有 则必须先完成当前订单

        // $isHaveNoPay = $printlist -> where('user_openid', $userOpenid) -> where('state', 0) -> field('state') -> find();

        // if ($isHaveNoPay) {

        //     $res['code'] = "401";

        //     $res['message'] = "用户有未完成订单";

        //     return json_encode($res);

        // }

        $totalFee = $request -> param('totalFee');

        $state = $request -> param('state');

        $userPrint['user_openid'] = $userOpenid;

        $userOrderId = $request -> param('order_id');

        $userPrint['order_id'] = $userOrderId;

        $userPrint['money'] = $totalFee;

        $userPrint['quantity'] = $request -> param('count');

        $userPrint['track_co_no'] = $request -> param('logi_co');

        $userPrint['format'] = $request -> param('format');

        $userPrint['unit_price'] = $request -> param('unitPrice');

        $userPrint['type'] = $state;

        // 打印类型判定 

        // 如果是single、ident就把path放到path 如果是mulit就把dir放到path

        // if ($state != 'mulit') {

            // $userPrint['imgPath'] = $request -> param('dir');

        $userPrint['img_path'] = $request -> param('path');

        // }

        // 地址判定

        $userAddress = htmlspecialchars($request -> param('address'));

        if($userAddress){

            $userPrint['isdelivery'] = 1;

        }



        // 用户备注

        $userRemark = $request -> param('message') ? $request -> param('message') : '无';

        $userRemark = htmlspecialchars($userRemark);

        $userPrint['remark'] = $userRemark;



        // 支付状态判定

        // 如果支付成功则直接删除对应缓存(删不删都无所谓，已经设置了定时删除)

        $is_pay = $request -> param('is_pay');

        // 打印的图片共有张数

        $totalNum = $request -> param('totalNum'); 

            

        // 订单配送状态

        $logiState = $userAddress ? "需要配送" : "上门自取";

        // 订单状态

        if ($state == 'single') {

            $printState = '单张打印';

        }else if ($state == 'mulit') {

            $printState = '多张打印';

        }else if ($state == 'ident') {

            $printState = '证件照打印';

        }



        $formId = $request -> param('formid');



        // foreach ($formId as $k => $v) {

            // Db::name('formid') -> insert(['formid' => $formId, 'openid' => $userOpenid, 'createtime' => date('Y-m-d H:i:s', time())]);

        // }

        if ($is_pay) {

            $userPrint['pay_time'] = time();

            // Cache::rm($userOrderId);

            // 获取prepay_id用于模板消息发送

            $orderInfo = Cache::get($userOrderId);

            $prepay_id = $orderInfo['prepay_id'];



            // 构造模板消息进行订单发送

            // 对应模板消息名称为 - 下单成功通知 - 发送给下单客户

            $post_data = array(

                'touser'            =>      $userOpenid,

                'template_id'       =>      'f_kLDqFES5nZQ5W710UxXLUKQY04PeOTcZbu-4vpf3I',

                'page'              =>      '/pages/orderdetail/orderdetail?orderid='.$userOrderId,

                'form_id'           =>      $formId,

                'data'              =>      array(

                                                'keyword1'  =>  array('value' => $userOrderId),                         //订单编号

                                                'keyword2'  =>  array('value' => date('Y-m-d H:i:s', time())),          //下单时间

                                                'keyword3'  =>  array('value' => $totalFee),                            //订单金额

                                                'keyword4'  =>  array('value' => $printState.' 共'.$totalNum.'张'),     //订单内容

                                                'keyword5'  =>  array('value' => '支付成功'),                           //订单状态

                                                'keyword6'  =>  array('value' => $logiState),                           //配送方式

                                                'keyword7'  =>  array('value' => '13906051853'),                        //客服电话

                                                'keyword8'  =>  array('value' => '福建省厦门市湖里区江顺里237号之68'),           //商户地址

                                                'keyword9'  =>  array('value' => '订单备注：'.$userRemark)              //温馨提示

                                            )

            );

            

            $post_data = json_encode($post_data);

            // 执行模板消息发送

            $createData = $this -> sendTempletMessage($post_data);



        }else{

            // 订单未支付

            // 构造模板消息进行订单发送

            // 对应模板消息名称为 - 待支付提醒 - 发送给下单客户

            

            $post_data = array(

                'touser'            =>      $userOpenid,

                'template_id'       =>      'ulPBxNbqzdc86Z9EtVGyMk6WXYXLCw6RjVCUR1vOEls',

                'page'              =>      'pages/orderdetail/orderdetail?orderid='.$userOrderId,

                'form_id'           =>      $formId,

                'data'              =>      array(

                                                'keyword1'  =>  array('value' => $userOrderId),                         //订单号

                                                'keyword2'  =>  array('value' => date('Y-m-d H:i:s', time())),          //下单时间

                                                'keyword3'  =>  array('value' => $totalFee.'元'),                       //订单价格

                                                'keyword4'  =>  array('value' => '等待支付'),                           //订单状态

                                                'keyword5'  =>  array('value' => $printState.' 共'.$totalNum.'张'),     //商品名称

                                                'keyword6'  =>  array('value' => '有问题请拨打客服电话13906051853')     //温馨提示

                                            )

            );

            

            $post_data = json_encode($post_data);

            // 执行模板消息发送

            $createData = $this -> sendTempletMessage($post_data);

            

        }



        // 订单状态判定

        if ($userAddress && $is_pay) {

            $userPrint['state'] = 2;

        }else if($is_pay){

            $userPrint['state'] = 1;

        }else if(!$is_pay){

            $userPrint['state'] = 0;

        }

        

        $userPrint['address'] = $userAddress;

        $userPrint['phone'] = $request -> param('telNumber');

        $userPrint['name'] = $request -> param('userName');

        $couponId = $request -> param('coupon_id');



        $userPrint['format_idx'] = $request -> param('formatIdx');



        $userPrint['coupon_id'] = $couponId ? $couponId : 0;

        // 如果有卡券信息 需要去更新用户卡券表

        if ($couponId) {

            $couponIdx = $request -> param('coupon_idx');

            $user_coupon = new User_coupon;

            $user_coupon -> where('user_openid', $userOpenid) -> where('idx', $couponIdx) -> update(['is_used' => 1, 'use_time' => time()]);

            Cache::rm('coupon');

        }



        $userPrint['createtime'] = time();

        // 订单状态判定

        $insert = Db::name('printlist') -> insert($userPrint);

        if($insert){

            $res['code'] = "200";

            $res['message'] = "success";

            $res['templateData'] = $createData;

            // 插入用户订单成功后，将之前缓存的订单列表删除

            Cache::rm($userOrderId);

        }else{

            $res['code'] = "400";

            $res['message'] = "NETWORK ERROR";

        }



        return json_encode($res);

    }





    /**

     * ---------------------------------------------------------

     * ---------------------------------------------------------

     *                        推送消息发送

     * ---------------------------------------------------------

     * ---------------------------------------------------------

     */

    

    /**

     * 获取微信accesstoken 并返回

     * 利用TP5 Cache类去维护accessToken减少后台交互，提升使用速度

     * @return string asscessToken

     */

    public function getAccessToken(){

        $accessToken = Cache::get('accessToken');

        if (!$accessToken) {

            $appid = 'wx06a3684282ae583e';

            $appsecret = 'ec46f43c22e8e8efc5311fd23f12c1ec';

            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;

            

            $info = file_get_contents($url);

            $info = json_decode($info);

            $info = get_object_vars($info);

            

            $accessToken = $info['access_token'];

            // $expirs_in = $info['expires_in'] - 100;

            // 将accessToken的有效期设置为3600s（一般情况下有效期7200s）

            Cache::set('accessToken', $accessToken, 6800);

        }



        return $accessToken;

    }



    /*

     *  发送模板消息

     */

    public function sendTempletMessage($postData){



        $accessToken = $this -> getAccessToken();

        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$accessToken;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HEADER, 0);

        // 这句话很重要 因为是SSL加密协议

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $output = curl_exec($ch);

        curl_close($ch);

        $info = json_decode($output);

        $info =  get_object_vars($info);

        return $info;

    }





    

    







}

