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
use think\Image;

use app\index\model\Goods;
use app\index\model\Banner;
use app\index\model\Userinfo;
use app\index\model\Promotion;
use app\index\model\Distribution;
use app\index\model\Distribution_log;
use app\index\model\Distribution_fee;
use app\index\model\Activity;
use app\index\model\Activity_user;
use app\index\model\Order;
use app\index\model\Order_detail;
use app\index\model\Logi;
use app\index\model\Clause;
use app\index\model\Goods_detail;
use app\index\model\Cart;
use app\index\model\Express;
use app\index\model\Activity_pride;
use app\index\model\User_rebate;

class Fangte extends Controller
{
    const APPID = "wx57beee95d7c48bbe";
    const APPSECRET = "774e5f55826cce1d828ab7faf14c3e09";
    const DS = DIRECTORY_SEPARATOR;
    // 微信支付相关
    const REPORT_LEVENL = 0;
    const KEY = "ls2805aeu2w0epzeawisc21f9wolmovo";
    const CURL_PROXY_HOST = "0.0.0.0";
    const CURLOPT_PROXYPORT = 0;

    /**
     * 根据关键词搜索商品信息
     *
     * @param Request $request
     * @return void
     */
    public function searchGoods(Request $request)
    {
        // 获取传递过来的查询数据
        $inputVal = htmlspecialchars($request->param('inputVal'));
        $goods = new Goods;
        $searchGoodsInfo = $goods->where('is_active', 1)->where('is_delete', 0)->where('name', 'like', "%" . $inputVal . "%")->limit(4)->field('goods_id, name, pic')->select();
        if ($searchGoodsInfo) {
            $res['goods'] = $searchGoodsInfo;
            $res['code'] = "200";
            $res['message'] = "search Success";
        } else {
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
    public function getShopInfo()
    {
        // 1 获取Banner和四个封面
        $banner = new Banner;
        $bannerList = $banner->where('is_active', 1)->where('is_delete', 0)->order('orderby desc')->field('banner_src, goods_id, type')->select();
        // 对拿到的Banner列表做处理
        $siteroot = "https://ft.up.maikoo.cn";
        $ticketBanner = array();
        $miniBanner = array();
        foreach ($bannerList as $k => $v) {
            if ($v['type'] == 0) {
                $temp['banner_src'] = $siteroot . $v['banner_src'];
                $temp['goods_id'] = $v['goods_id'];
                $miniBanner[] = $temp;
            }
            if ($v['type'] == 1) {
                $ticketBanner['jd'] = $siteroot . $v['banner_src'];
            }
            if ($v['type'] == 2) {
                $ticketBanner['cx'] = $siteroot . $v['banner_src'];
            }
            if ($v['type'] == 3) {
                $ticketBanner['cj'] = $siteroot . $v['banner_src'];
            }
            if ($v['type'] == 4) {
                $ticketBanner['xx'] = $siteroot . $v['banner_src'];
            }
        }

        // 2 获取前六个生活用品商品列表
        $goods = new Goods;
        $dailyList = $goods->where('catagory_id', 5)->where('is_delete', 0)->where('is_active', 1)->field('goods_id, name, pic')->order('orderby desc')->limit(6)->select();
        if ($dailyList && count($dailyList) > 0) {
            $dailyList = collection($dailyList)->toArray();
            foreach ($dailyList as &$info) {
                $info['pic'] = $siteroot . $info['pic'];
            }
        }

        $res['banner'] = count($miniBanner) > 0 ? $miniBanner : 'none';
        $res['ticket'] = count($ticketBanner) > 0 ? $ticketBanner : 'none';
        $res['daily'] = count($dailyList) > 0 ? $dailyList : 'none';
        return json_encode($res);
    }

    /**
     * 获取商品列表信息
     * 每次获取12个
     *
     * @param Request $request
     * @return json goodsList
     */
    public function getGoods(Request $request)
    {
        // 先直接接入数据库 每次获取12个
        $goodsCatId = intval($request->param('catId'));
        $pageNum = intval($request->param('pageNum'));

        if ($goodsCatId == 0) {
            $catIds = [0, 1, 2, 3, 4];
        } else {
            $catIds = [$goodsCatId];
        }

        $goods = new Goods;
        // 获取商品信息
        $goodsList = $goods->where('is_active', 1)->where('is_delete', 0)->where('catagory_id', 'in', $catIds)->field('goods_id, catagory_id, name, pic')->limit($pageNum * 10, 10)->order('orderby desc')->select();
        if ($goodsList) {
            // 对图片地址做处理
            foreach ($goodsList as $k => $v) {
                $v['pic'] = "https://ft.up.maikoo.cn" . $v['pic'];
            }
            $res['code'] = "200";
            $res['goods'] = $goodsList;
            $res['message'] = "Search Success";
        } else {
            $res['code'] = "201";
            $res['data'] = "No Goods";
        }
        return json_encode($res);
    }

    /**
     * 用户认证
     *
     * @param Request $request
     * @return void
     */
    public function authUser(Request $request)
    {
        $name = htmlspecialchars($request->param('name'));
        $identID = $request->param('identID');
        $telNum = $request->param('telNum');
        $userOpenid = $request->param('openid');

        // 先将其插入数据库
        $userinfo = new Userinfo;
        $update = $userinfo->where('user_openid', $userOpenid)->update(['name' => $name, 'tel_num' => $telNum, 'ident_id' => $identID, 'is_auth' => 1]);
        if ($update) {
            // 更新用户信息成功
            // 将用户信息更新至缓存
            $userAccountInfo = Cache::get('userAccountInfo');
            if ($userAccountInfo) {
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
            }
        } else {
            $res['code'] = "200";
            $res['message'] = "User Auth Falied";
        }
        return json_encode($res);
    }

    /**
     * 通过商品id查找对应商品
     *
     * @return void
     */
    public function getGoodsById()
    {
        if (!request()->isPost()) return json_encode(['error' => 400]);
        $goodsid = intval(request()->param('goodsid'));
        // 先直接去数据库找
        $goods = new Goods;
        $goodsInfo = $goods->where('is_active', 1)->where('goods_id', $goodsid)->where('is_delete', 0)->field('catagory_id, name, pic, intro, is_on_promotion, promotion_id, is_distri, dis_percent, parent_dis_percent, grand_dis_percent')->select();
        if (!$goodsInfo) {
            // 如果当前商品不存在
            $res['code'] = "401";
            $res['message'] = "Goods Not Exist";
            return json_encode($res);
        }
        $goodsInfo = collection($goodsInfo)->toArray();
        $goodsInfo = $goodsInfo[0];
        
        // 对goods表中数据做简单处理
        $goodsInfo['name'] = htmlspecialchars_decode($goodsInfo['name']);
        $goodsInfo['intro'] = htmlspecialchars_decode($goodsInfo['intro']);
        $goodsInfo['pic'] = "https://ft.up.maikoo.cn" . $goodsInfo['pic'];

        $goodsInfo['sellnum'] = 0;
        // dump($goodsInfo);die;
    
        // 如果当前商品有参与活动 获取对应活动信息
        // 先直接去数据库找
        if ($goodsInfo['is_on_promotion']) {
            // 判断当前活动是否有效
            $promotion = new Promotion;
            $promotionInfo = $promotion->where('promotion_id', $goodsInfo['promotion_id'])->where('is_active', 1)->field('count, name, end_time')->select();
            if ($promotionInfo && count($promotionInfo) > 0) {
                $promotionInfo = collection($promotionInfo)->toArray();
                // 判断活动是否过期
                $promotionInfo = $promotionInfo[0];
                if ($promotionInfo['end_time'] < time()) {
                    // 更新数据库
                    $promotion->update(['promotion_id' => $goodsInfo['promotion_id'], 'is_active' => 0]);
                    $goodsInfo['is_on_promotion'] = 0;
                } else {
                    $goodsInfo['promotion_count'] = number_format(($promotionInfo['count'] / 10), 1);
                    $goodsInfo['promotion_name'] = $promotionInfo['name'];
                }
            } else {
                $goodsInfo['is_on_promotion'] = 0;
            }
        }
        // 去goods_detail表查对应的数据
        $goods_detail = new Goods_detail;
        $goodsDetail = $goods_detail->where('goods_id', $goodsid)->where('is_delete', 0)->field('idx, goods_id, detail_name, market_price, shop_price, stock, sellnum, create_time, detail_intro, keywords')->select();
        if ($goodsDetail && count($goodsDetail) > 0) {
            $goodsDetail = collection($goodsDetail)->toArray();
                // 处理其中的一些数据
            foreach ($goodsDetail as $k => $v) {
                $goodsDetail[$k]['detail_intro'] = htmlspecialchars_decode($v['detail_intro']);
                $goodsDetail[$k]['detail_name'] = htmlspecialchars_decode($v['detail_name']);
                    // $goodsDetail[$k]['price'] = round(floatval(), 2);
                $goodsDetail[$k]['market_price'] = number_format($v['market_price'], 2);
                $goodsDetail[$k]['shop_price'] = number_format($v['shop_price'], 2);
                $goodsDetail[$k]['keywords'] = explode(',', $v['keywords']);
                $goodsInfo['sellnum'] += $v['sellnum'];
                if ($goodsInfo['is_on_promotion']) {
                    $goodsDetail[$k]['pro_price'] = number_format($v['shop_price'] * $goodsInfo['promotion_count'] * 0.1, 2);
                }
            }
        }
        $goodsInfo['detail'] = $goodsDetail ? $goodsDetail : [];
        $userid = request()->param('userid');
        $goodsInfo['user_id'] = $userid;
        $goodsInfo['goods_id'] = $goodsid;
        $res['code'] = "200";
        $res['message'] = "Goods Search Success";
        $res['goodsInfo'] = $goodsInfo;
        // 如果存在父级id就去更新用户信息
        $parentid = intval(request()->param('parentid'));
        if ($goodsInfo['is_distri'] && $parentid && $parentid != "no") {
            $this->updateUserDistri($userid, $goodsid, request()->param('openid'), $parentid);
        }
        return json_encode($res);
    }

    /**
     * 获取商品详情
     *
     * @return void
     */
    public function getGoodsSpecById(Request $request)
    {
        $goodsId = $request->param('goodsid');
        $goods = new Goods;
        $goodsSpec = $goods->where('goods_id', $goodsId)->field('spec, name')->select();
        $goodsSpec = collection($goodsSpec)->toArray();
        $goodsSpec = $goodsSpec[0];
        $goodsSpec['spec'] = htmlspecialchars_decode($goodsSpec['spec']);
        $res['code'] = 0;
        $res['msg'] = 'success';
        $res['data'] = $goodsSpec;
        return json_encode($res);
    }

    /**
     * 判断当前活动是否有效
     * 当活动有效时返回活动详情，活动无效时返回FALSE
     *
     * @param array $promotionID 促销活动ID
     * @return boolean $isPromotionEffective 是否有效
     */
    public function checkPromotion($promotionID)
    {
        // 先判断当前缓存是否存在
        $promotion = new Promotion;
        $promotionInfo = $this->getPromotionInfo();
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
                $invalidPromotion[] = $v;
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
        if (count($invalidPromotion) > 0) {
            foreach ($invalidPromotion as $k => $v) {
                $promotion->update(['is_active' => 0, 'promotion_id' => $v['promotion_id']]);
            }
            // 更新缓存
            $promotionInfo = $promotion->where('is_active', 1)->select();
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
     * @param string $openid   用户openid
     * @param int $parentid     用户父级ID
     * @return void
     */
    public function updateUserDistri($userid, $goodsid, $openid, $parentid)
    {

        // 如果当前用户自己打开自己的分享就不要管
        if ($parentid == $userid) {
            return;
        }
        $distribution = new Distribution;
        $distribution_log = new Distribution_log;
        // 查库
        $userDistriInfo = $distribution->where('goods_id', $goodsid)->field('idx, user_id, user_openid, parent_id, grand_id, goods_id')->select();
        // 有数据
        $curGrandId = null;
        if ($userDistriInfo && count($userDistriInfo) > 0) {
            $userDistriInfo = collection($userDistriInfo)->toArray();
            // dump($userDistriInfo);die;
            foreach ($userDistriInfo as $k => $v) {
                if ($v['user_id'] == $userid && $v['parent_id'] != 0) {
                    return;
                }
                if ($v['user_id'] == $parentid && $v['grand_id'] != $userid) {
                    foreach ($userDistriInfo as $ke => $va) {
                        if ($va['user_id'] == $v['parent_id'] && $va['parent_id'] != $userid) {
                            $curGrandId = $v['parent_id'];
                            break 1;
                        }
                    }
                }
            }
        }
        // 如果当前库中没有对应用户对应商品的分销信息 则直接插入
        $currentDistri['user_id'] = $userid;
        $currentDistri['user_openid'] = $openid;
        $currentDistri['goods_id'] = $goodsid;
        $currentDistri['parent_id'] = $parentid;
        $currentDistri['grand_id'] = $curGrandId ? $curGrandId : 0;
        $currentDistri['gen_grand_time'] = $curGrandId ? time() : 0;
        $currentDistri['gen_parent_time'] = time();
        // $insert = $distribution -> insert($currentDistri);
        $currentDistri['idx'] = Db::name('distribution')->insertGetId($currentDistri);
        $userDistriInfo[] = $currentDistri;
        // 如果分销表中数据不为空，则判断是否可以gen grand_id
        // 如果当前分销表的数据不为空
        // A -> B -> C -> D  不能有 C -> A 只能有 D -> A
        if (!$userDistriInfo || count($userDistriInfo) == 0) {
            return;
        }
        $updateGrandArr = [];
        $grandId = null;
        // dump($userDistriInfo);die;
        foreach ($userDistriInfo as $k => $v) {
            if ($v['parent_id'] == $userid && $v['grand_id'] == 0) {
                $temp['idx'] = $v['idx'];
                $temp['grand_id'] = $parentid;
                $temp['user_id'] = $v['user_id'];
                $updateGrandArr[] = $temp;
            }
        }
        // dump($updateGrandArr);die;
        // 已经将所有可能成为上级ID的数据列举出来了
        // 这一步做 资格审查
        $updateArr = [];
        if (count($updateGrandArr) > 0) {
            foreach ($updateGrandArr as $k => $v) {
                foreach ($userDistriInfo as $ke => $va) {
                    if ($va['user_id'] == $v['grand_id'] && $va['parent_id'] != $v['user_id']) {
                        $temp['idx'] = $v['idx'];
                        $temp['grand_id'] = $v['grand_id'];
                        $temp['gen_grand_time'] = time();
                        $updateArr[] = $temp;
                        break 1;
                    }
                }
            }
        }
        // dump($updateArr); die;
        if (count($updateArr) > 0) {
            $distribution->isUpdate()->saveAll($updateArr);
        }
        return;
    }

    /**
     * 将商品添加至购物车
     *
     * @param Request $request
     * @return void
     */
    public function addToCart(Request $request)
    {
        // 获取传递过来的商品信息
        $userOpenid = $request->param('openid');
        $goodsId = intval($request->param('goodsid'));
        $detailId = intval($request->param('detailid'));

        // 判断是否有购物车缓存
        // 购物车缓存结构如下
        // user_openid, goodsInfo
        // goodsInfo => goodsId, quantity, detailIdx
        $userCartInfo = Cache::get('userCartInfo');
        // 如果当前购物车中有该商品，则将对应商品的数量+1
        $isHaveCurrentGoods = false;
        // 当前库中是否有该用户的商品
        $isHaveCurrentUser = false;
        // 构建返回数组
        $res = array();
        if ($userCartInfo && count($userCartInfo) > 0) {
            foreach ($userCartInfo as $k => $v) {
                if ($userOpenid == $v['user_openid']) {
                    $isHaveCurrentUser = true;
                    foreach ($v['goodsInfo'] as &$info) {
                        if ($info['goods_id'] == $goodsId && $info['detail_id'] == $detailId) {
                            $isHaveCurrentGoods = true;
                            $info['quantity'] += 1;
                            $res['code'] = "200";
                            $res['message'] = "Goods quantity plus 1";
                            break 2;
                        }
                    }
                    // 如果当前用户下没有对应商品 则直接追加
                    if (!$isHaveCurrentGoods) {
                        $temp['goods_id'] = $goodsId;
                        $temp['quantity'] = 1;
                        $temp['detail_id'] = $detailId;
                        $userCartInfo[$k]['goodsInfo'][] = $temp;
                        $res['code'] = "200";
                        $res['message'] = "User add new item";
                    }
                    break 1;
                }
            }
        }
        // 如果缓存不存在或者当前购物车缓存中没有该商品
        if (!$userCartInfo || !$isHaveCurrentUser) {
            $userCartInfo = [];
            $temp = [];
            $temp['user_openid'] = $userOpenid;
            $detailTemp['goods_id'] = $goodsId;
            $detailTemp['quantity'] = 1;
            $detailTemp['detail_id'] = $detailId;
            $temp['goodsInfo'][] = $detailTemp;
            array_push($userCartInfo, $temp);
            $res['code'] = "200";
            $res['message'] = "Add Goods to Cart Success";
        }

        $res['data'] = $userCartInfo;
        // 更新缓存
        Cache::set('userCartInfo', $userCartInfo, 0);

        return json_encode($res);
    }

    /**
     * 判断订单是否要及时入库
     *
     * @return void
     */
    public function checkUserCart()
    {
        $userCartInfoExpire = Cache::get('userCartInfoExpire');
        $curTime = time();
        if ($curTime - $userCartInfoExpire > 0) {
            $userCartInfo = Cache::get('userCartInfo');
            // 构造CartInfo 并将CartInfo入库
            $cartInfo = [];
            foreach ($userCartInfo as $k => $v) {
                foreach ($v['goodsInfo'] as $ke => $va) {
                    $temp['user_openid'] = $v['user_openid'];
                    $temp['goods_id'] = $va['goods_id'];
                    $temp['quantity'] = $va['quantity'];
                    $temp['detail_id'] = $va['detail_id'];
                    $cartInfo[] = $temp;
                }
            }
            $cart = new Cart;
            $cart->saveAll($cartInfo);
        }
    }

    /**
     * 获取购物车信息
     * 需要去当前商品列表做判断，是否有变更
     *
     * @return void
     */
    public function getCartList(Request $request)
    {
        // 获取用户openid
        $userOpenid = $request->param('openid');
        $res['code'] = "400";
        // 非空返回
        if (!$userOpenid) {
            $res['msg'] = "Invaild UserOpenid";
            return json_encode($res);
        }
        $res['isHaveChange'] = false;

        $userCartInfo = Cache::get('userCartInfo');
        if (!$userCartInfo) {
            $res['code'] = "202";
            $res['message'] = "No Cart Exist";
            return json_encode($res);
        }

        // 是否有用户的购车
        $isHaveCart = false;
        // 当前用户的购物车信息
        $curUserCart = [];
        $goodsIds = [];
        $detailIds = [];
        // 判断当前商品信息是否有变动
        foreach ($userCartInfo as $k => $v) {
            if ($v['user_openid'] == $userOpenid) {
                $isHaveCart = true;
                foreach ($v['goodsInfo'] as $ke => $va) {
                    $goodsIds[] = $va['goods_id'];
                    $detailIds[] = $va['detail_id'];
                }
                $curUserCart = $v['goodsInfo'];
                break;
            }
        }
        // 如果没有购物车直接返回
        if (!$isHaveCart || count($curUserCart) == 0) {
            $res['code'] = "202";
            $res['msg'] = "No Cart";
            return json_encode($res);
        }

        // 从数据库中查找相关产品信息
        $goods = new Goods;
        $cartList = $goods->alias('g')->join('ft_goods_detail d', 'd.goods_id = g.goods_id', 'LEFT')->where('g.goods_id', 'in', $goodsIds)->where('d.idx', 'in', $detailIds)->where('g.is_active', 1)->where('g.is_delete', 0)->where('d.is_delete', 0)->field('g.catagory_id, g.name, g.goods_id, g.pic, g.is_on_promotion, g.is_distri, g.promotion_id, g.dis_percent, g.parent_dis_percent, g.grand_dis_percent, d.idx as detail_id, d.detail_name, d.market_price, d.shop_price, d.sellnum, d.stock, d.detail_intro, d.keywords')->select();
        if (!$cartList || count($cartList) == 0) {
            $res['code'] = "200";
            $res['msg'] = "Goods Has Been Changed";
            return json_encode($res);
        }
        $cartList = collection($cartList)->toArray();
        // 若购物车有变动 构造剩余购物车数据数组
        $leftCartList = [];
        $isHaveCartChange = false;
        $promotion = new Promotion;
        foreach ($curUserCart as $k => $v) {
            foreach ($cartList as $ke => $va) {
                if ($va['goods_id'] == $v['goods_id'] && $va['detail_id'] == $v['detail_id']) {
                    $va['quantity'] = $v['quantity'];
                    $curUserCart[$k] = $va;
                    break;
                }
            }
            if (isset($curUserCart[$k]['pic'])) $curUserCart[$k]['pic'] = "https://ft.up.maikoo.cn" . $curUserCart[$k]['pic'];
            if (isset($curUserCart[$k]['keywords'])) $curUserCart[$k]['keywords'] = str_replace(',', ' ', $curUserCart[$k]['keywords']);
            // 折扣信息处理
            if ($curUserCart[$k]['is_on_promotion'] == 1) {
                $promotionInfo = $promotion->where('promotion_id', $curUserCart[$k]['promotion_id'])->field('promotion_id, count, name')->where('is_active', 1)->select();
                if ($promotionInfo && count($promotionInfo) > 0) {
                    $promotionInfo = collection($promotionInfo)->toArray();
                    $promotionInfo = $promotionInfo[0];
                    $curUserCart[$k]['promotion_name'] = $promotionInfo['name'];
                    $curUserCart[$k]['promotion_count'] = number_format(($promotionInfo['count'] / 10), 1);
                    $curUserCart[$k]['pro_price'] = number_format($curUserCart[$k]['shop_price'] * $curUserCart[$k]['promotion_count'] * 0.1, 2);
                } else {
                    $curUserCart[$k]['is_on_promotion'] = 0;
                }
            }
            // dump($curUserCart);
            $curUserCart[$k]['goods_name'] = $curUserCart[$k]['name'] . '-' . $curUserCart[$k]['detail_name'];
            unset($curUserCart[$k]['name']);
            unset($curUserCart[$k]['detail_name']);
            if (!isset($curUserCart[$k]['pic'])) {
                unset($curUserCart[$k]);
                $isHaveCartChange = true;
            } else {
                $temp['goods_id'] = $v['goods_id'];
                $temp['detail_id'] = $v['detail_id'];
                $temp['quantity'] = $v['quantity'];
                $leftCartList[] = $temp;
            }
        }
        // 如果购物车有变动 则更新当前购物车信息
        if ($isHaveCartChange) {
            foreach ($userCartInfo as &$cart) {
                if ($cart['user_openid'] == $userOpenid) {
                    $cart['goodsInfo'] = $leftCartList;
                    break;
                }
            }
            Cache::set('userCartInfo', $userCartInfo);
        }
        $res['isHaveChange'] = $isHaveCartChange;
        // 如果当前用户的购物车信息有变动 就更新缓存
        $res['code'] = "200";
        $res['cartList'] = $curUserCart;
        return json_encode($res);
        // 如果购物车中有商品 则判断是否有变更 有则需要更新当前缓存
    }

    /**
     * 删除用户对应购物车的商品数据
     *
     * @param string $openid 用户openid
     * @param array $itemIds cart中的goodsid
     * @return void
     */
    public function deleteCartItem($openid, $item = null)
    {
        $userCartInfo = Cache::get('userCartInfo');
        if (!$userCartInfo) {
            return;
        }
        foreach ($userCartInfo as &$cart) {
            if ($cart['user_openid'] == $openid) {
                if (isset($item)) {
                    foreach ($item as $ke => $va) {
                        foreach ($cart['goodsInfo'] as $key => $val) {
                            if ($val['goods_id'] == $va['goods_id']) {
                                unset($cart['goodsInfo'][$key]);
                                break 1;
                            }
                        }
                    }
                }
                $cart['goodsInfo'] = [];
                break;
            }
        }
        Cache::set('userCartInfo', $userCartInfo, 0);
    }

    /**
     * 获取当前商品列表
     * 当商品存在是返回商品详情
     * 否则返回null
     * 
     * @param int $goodsid 商品id
     * @return array $shopGoodsInfo
     */
    public function checkShopGoods($goodsid)
    {

        $shopGoodsInfo = Cache::get('shopGoodsInfo');
        if ($shopGoodsInfo && $shopGoodsInfo == 0) {
            return null;
        }
        if (!$shopGoodsInfo) {
            $goods = new Goods;
            $shopGoodsInfo = $goods->where('is_delete', 0)->where('is_active', 1)->field('goods_id, name, pic, price, shop_price, is_on_promotion, promotion_id, is_distri, dis_percent, parent_dis_percent, grand_dis_percent')->select();
            if (!$shopGoodsInfo) {
                Cache::set('shopGoodsInfo', 0, 10);
                return null;
            }
            Cache::set('shopGoodsInfo', $shopGoodsInfo, 60);
        }

        foreach ($shopGoodsInfo as $k => $v) {
            if ($v['goods_id'] == $goodsid) {
                return $v;
            }
        }

        return null;
    }

    /**
     * 获取促销活动 缓存
     *
     * @return array $promotionInfo
     */
    public function getPromotionInfo()
    {
        $promotionInfo = Cache::get('promotionInfo');
        if (!$promotionInfo) {
            $promotion = new Promotion;
            $promotionInfo = $promotion->where('is_active', 1)->select();
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
    public function getPromotionList()
    {
        if (!request()->isPost()) return;
        $pageNum = intval(request()->param('pageNum'));
        // $num = intval($request->param('num'));
        $userid = request()->param('userid');
        // $userOpenid = $request -> param('openid');

        if (!$userid) {
            $res['code'] = "400";
            $res['message'] = "Invaild Userid";
            return json_encode($res);
        }

        $distribution_fee = new Distribution_fee;

        // 构造返回数据
        $userDisArr = [];
        $userAccountInfo = Cache::get('userAccountInfo');
        // dump($userAccountInfo);die;
        // 先直接从数据库获取
        $field = 'dis_fee_id, user_id, parent_id, parent_fee, create_time, is_success, grand_id, grand_fee';
        $disFeeList = $distribution_fee->whereOr('parent_id', $userid)->whereOr('grand_id', $userid)->where('is_success', 1)->field($field)->order('create_time desc')->limit($pageNum * 12, 12)->select();
        if ($disFeeList && count($disFeeList) > 0) {
            // 数据处理 构造返回数据
            $disFeeList = collection($disFeeList)->toArray();
            $userAccountInfo = Cache::get('userAccountInfo');
            foreach ($disFeeList as $k => $v) {
                $temp = [];
                // 获取商品信息
                // $temp['goods_name'] = $v['goods_name'];
                // 第一 获取上级和上上级用户信息
                foreach ($userAccountInfo as $ke => $va) {
                    if ($va['userID'] == $v['parent_id']) {
                        if ($userid == $va['userID']) {
                            $temp['upper_dis_fee'] = number_format($v['parent_fee'], 2);
                        } else {
                            $temp['upper_name'] = $va['userInfo']['nickName'];
                            $temp['upper_pic'] = $va['userInfo']['avatarUrl'];
                        }
                    }
                    if ($va['userID'] == $v['grand_id'] && $userid == $va['userID']) {
                        // $temp['upper_name'] = $va['userInfo']['nickName'];
                        // $temp['upper_pic'] = $va['userInfo']['avatarUrl'];
                        $temp['upper_dis_fee'] = number_format($v['grand_fee'], 2);
                        // $temp['upper_userID'] = $va['userID'];
                    }
                    if ($va['userID'] == $v['user_id']) {
                        $temp['buyer_name'] = $va['userInfo']['nickName'];
                        $temp['buyer_pic'] = $va['userInfo']['avatarUrl'];
                    }
                }
                $temp['create_time'] = $v['create_time'];
                $userDisArr[] = $temp;
            }
            $res['code'] = "0";
            $res['msg'] = 'success';
            $res['data'] = $userDisArr;
        } else {
            $res['code'] = "201";
            $res['msg'] = "nothing";
        }
        return json_encode($res);
    }


    /**
     * 获取活动详情
     *
     * @param Request $request
     * @return void
     */
    public function getActivityList(Request $request)
    {
        $userOpenid = $request->param('openid');
        $pageNum = $request->param('pageNum');
        // 获取活动
        $activity = new Activity;
        $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
        $activityList = $activity->where('is_active', 1)->where('is_delete', 0)->field('activity_id, name, brief, pic, state, start_time, end_time')->order('start_time desc')->limit($limit)->select();
        // 如果当前没有活动则直接返回
        if (!$activityList || count($activityList) == 0) {
            $res['code'] = "201";
            $res['msg'] = "No Activity";
            return json_encode($res);
        } else {
            $res['code'] = "200";
            $res['msg'] = "Get Activity Success";
        }
        $activityList = collection($activityList)->toArray();
        // 整理ActivityList 并判断其是否有更新
        $updateArr = [];
        $curTime = time();
        foreach ($activityList as &$info) {

            $info['pic'] = "https://ft.up.maikoo.cn" . $info['pic'];

            if ($info['end_time'] < $curTime) {
                $temp['activity_id'] = $info['activity_id'];
                $temp['state'] = 4;
                $temp['is_active'] = 0;
                $updateArr[] = $temp;
                unset($info);
            } else {
                $info['countDown'] = $info['end_time'] - time() - 5;
                $info['end_time_conv'] = date('Y-m-d H:i:s', $info['end_time']);
                $info['start_time_conv'] = date('Y-m-d H:i:s', $info['start_time']);
                switch ($info['state']) {
                    case 0:
                        $info['state_conv'] = "报名中";
                        break;
                    case 1:
                        $info['state_conv'] = "未开奖";
                        break;
                    case 2:
                        $info['state_conv'] = "已开奖";
                        break;
                    case 3:
                        $info['state_conv'] = "已暂停";
                        break;
                    case 4:
                        $info['state_conv'] = "已结束";
                        break;
                }
            }
        }
        if (count($activityList) > 10) {
            $res['isHaveMore'] = true;
        } else {
            $res['isHaveMore'] = false;
        }
        if (count($updateArr) > 0) {
            $activity->isUpdate()->saveAll($updateArr);
        }
        $res['activity'] = $activityList;
        return json_encode($res);
    }

    /**
     * 通过活动ID获取活动详情
     * 
     * @param int $activityID
     *
     * @return array $activity
     */
    public function getActivityById(Request $request)
    {
        $activityID = intval($request->param('activityID'));
        $userId = $request->param('userid');
        // 直接从数据库拉取消息
        $activityField = 'activity_id, name, brief, pic, detail, start_time, end_time, first_price_num, first_price, second_price_num, second_price, third_price, third_price_num, activity_poster, state, is_active';
        $activity = new Activity;
        $activityInfo = $activity->where('activity_id', $activityID)->field($activityField)->select();
        if (!$activityInfo || count($activityInfo) == 0) {
            $res['code'] = "400";
            $res['msg'] = "Activity Doesn't Exist";
            return json_encode($res);
        }
        $activityInfo = collection($activityInfo)->toArray();
        $activityInfo = $activityInfo[0];

        if (!empty($activityInfo['activity_poster'])) $activityInfo['activity_poster'] = 'https://ft.up.maikoo.cn/' . $activityInfo['activity_poster'];
        // dump($activityInfo);die;
        // 判断该活动是否过期
        if ($activityInfo['end_time'] < time()) {
            $activity->where('activity_id', $activityID)->update(['state' => 4, 'is_active' => 0]);
            $res['code'] = "401";
            $res['msg'] = "Activity Expired";
            return json_encode($res);
        }
        $activityInfo['countDown'] = $activityInfo['end_time'] - time() - 1;
        $activityInfo['end_time_conv'] = date('Y-m-d H:i:s', $activityInfo['end_time']);
        $activityInfo['start_time_conv'] = date('Y-m-d H:i:s', $activityInfo['start_time']);
        $activityInfo['brief'] = htmlspecialchars_decode($activityInfo['brief']);
        $activityInfo['detail'] = htmlspecialchars_decode($activityInfo['detail']);
        // 判断当前用户是否有参加当前活动
        $activity_user = new Activity_user;
        $curUserIsJoin = $activity_user->where('user_id', $userId)->where('activity_id', $activityID)->count();
        $activityInfo['isJoin'] = $curUserIsJoin == 1 ? true : false;
        // 判断当前用户是否已中奖
        $activity_pride = new Activity_pride;
        $userPride = $activity_pride->where('user_id', $userId)->where('activity_id', $activityID)->select();
        if (!$userPride || count($userPride) == 0) {
            $activityInfo['isWin'] = false;
        } else {
            $userPride = collection($userPride)->toArray();
            $userPride = $userPride[0];
            $activityInfo['isWin'] = true;
            $activityInfo['level'] = $userPride['level'];
            $activityInfo['level_price'] = htmlspecialchars_decode($userPride['level_price']);
            $activityInfo['win_time'] = $userPride['create_time'];
        }
        $res['code'] = "200";
        $res['activity'] = $activityInfo;
        $res['msg'] = "Get Activity Success";
        return json_encode($res);
    }

    /**
     * 用户参加当前活动
     *
     * @return array 是否参加成功
     */
    public function activitySingUp(Request $request)
    {
        $userOpenid = $request->param('openid');
        $userid = $request->param('userid');
        $activityID = $request->param('activityID');
        $userName = $request->param('name');
        $pic = $request->param('pic');

        $activity_user = new Activity_user;
        $insert = $activity_user->insert(['activity_id' => $activityID, 'user_openid' => $userOpenid, 'user_id' => $userid, 'join_time' => time(), 'user_name' => $userName, 'pic' => $pic]);
        if ($insert) {
            $res['code'] = "200";
            $res['msg'] = "Activity Join Success";
        } else {
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
    public function getUserActivity(Request $request)
    {
        $userid = $request->param('userid');
        $pageNum = intval($request->param('pageNum'));
        // 获取所有活动列表
        $activity_user = new Activity_user;
        $userActivity = $activity_user->alias('u')->join('ft_activity a', 'u.activity_id = a.activity_id', 'LEFT')->where('u.user_id', $userid)->field('u.idx, u.activity_id, u.join_time, a.name, a.pic')->limit($pageNum * 10, 10)->order('u.join_time desc')->select();
        if (!$userActivity || count($userActivity) == 0) {
            $res['code'] = "201";
            $res['msg'] = "No Activity Exist";
            return json_encode($res);
        }
        $userActivity = collection($userActivity)->toArray();
        // 查询该用户是否中奖
        $activity_pride = new Activity_pride;
        $userActWin = $activity_pride->where('user_id', $userid)->field('activity_id, level, level_price')->select();
        if (!$userActWin || count($userActWin) == 0) {
            $userActWin = null;
        } else {
            $userActWin = collection($userActWin)->toArray();
        }
        // 简单处理
        foreach ($userActivity as &$info) {
            $info['join_time'] = date('Y-m-d H:i:s', $info['join_time']);
            $info['name'] = htmlspecialchars_decode($info['name']);
            // $info['brief'] = htmlspecialchars_decode($info['brief']);
            $info['isWin'] = false;
            if ($userActWin) {
                foreach ($userActWin as $k => $v) {
                    if ($info['activity_id'] == $v['activity_id']) {
                        $info['isWin'] = true;
                        $info['win_level'] = $v['level'];
                        $info['win_price'] = htmlspecialchars_decode($v['level_price']);
                        break 1;
                    }
                }
            }
        }

        $res['isHaveMore'] = count($userActivity) > 10 ? true : false;
        $res['code'] = "200";
        $res['msg'] = "success";
        $res['activity'] = $userActivity;

        return json_encode($res);
    }

    /**
     * 获取用户订单列表
     * 每次获取12条
     * 1待付款 2待发货 3已发货 4已完成 5已取消 6 售后中 7 售后完成 8 申请退款
     *
     * @return void
     */
    public function getOrderList(Request $request)
    {
        $userOpenid = $request->param('openid');
        $userId = $request->param('userid');
        $pageNum = $request->param('pageNum');
        $orderStatus = $request->param('status');

        $order = new Order;
        $order_detail = new Order_detail;
        $orderStatus = $orderStatus == 0 ? [1, 2, 3, 4, 5, 6, 7, 8, 9] : [$orderStatus];

        // 获取订单
        $orderList = $order->where('user_openid', $userOpenid)->where('is_delete', 0)->where('status', 'in', $orderStatus)->field('order_id, user_openid, user_id, total_fee, express_fee, status, create_time, status, express_time, confirm_time, finish_as_time, onas_time, is_refound')->order('create_time desc')->limit($pageNum * 12, 12)->select();
        if (!$orderList) {
            $res['code'] = "201";
            $res['msg'] = "Current User Didn't Have Any Order";
            return json_encode($res);
        }
        $orderList = collection($orderList)->toArray();
        $orderList = $this->checkOrder($orderList, $userId);

        foreach ($orderList as $k => $v) {
            $detail = $order_detail->alias('d')->join('ft_goods g', 'd.goods_id = g.goods_id', 'LEFT')->join('ft_promotion p', 'd.promotion_id = p.promotion_id', 'LEFT')->join('catagory c', 'd.catagory_id = c.catagory_id', 'LEFT')->where('d.order_id', $v['order_id'])->field('d.idx, d.goods_id, d.detail_id, d.quantity, d.market_price, d.shop_price, g.name, g.pic, d.promotion_id, p.name as promotion_name, p.count as promotion_count, c.catagory_name')->select();
            // 获取商品总数
            $goodsTotalNum = 0;
            $detail = collection($detail)->toArray();
            foreach ($detail as $ke => $va) {
                // $detail[$ke]['price'] = number_format(($va['price'] / 100), 2);
                $goodsTotalNum += $va['quantity'];
                $detail[$ke]['pic'] = "https://ft.up.maikoo.cn" . $va['pic'];
                if (isset($va['promotion_count'])) {
                    $detail[$ke]['promotion_count'] = number_format($va['promotion_count'] / 10, 1);
                }
            }
            $orderList[$k]['detail'] = $detail;
            // $orderList[$k]['totalFee'] = number_format(($v['totalFee'] / 100), 2);
            switch ($v['status']) {
                case 1:
                    $orderList[$k]['status_conv'] = '待付款';
                    break;
                case 2:
                    $orderList[$k]['status_conv'] = '待发货';
                    break;
                case 3:
                    $orderList[$k]['status_conv'] = '待收货';
                    break;
                case 4:
                    $orderList[$k]['status_conv'] = '已完成';
                    break;
                case 5:
                    $orderList[$k]['status_conv'] = '已取消';
                    break;
                case 6:
                    $orderList[$k]['status_conv'] = '申请售后中';
                    break;
                case 7:
                    $orderList[$k]['status_conv'] = '售后完成';
                    break;
                case 8:
                    $orderList[$k]['status_conv'] = '申请退款中';
                    break;
                case 9:
                    $orderList[$k]['status_conv'] = '退款处理完成';
                    break;
            }
        }

        // $isHaveMore = count($orderList) > 12 ? true : false;

        $res['code'] = "200";
        $res['order'] = $orderList;
        // $res['isHaveMore'] = $isHaveMore;
        $res['msg'] = "Get OrderList Success";
        return json_encode($res);
    }

    /**
     * 检查订单状态
     * 1. 未付款订单15分钟内有效可进行二次付款
     * 2. 未收货订单七天内不收货则自动变为已收货
     *
     * @param array $orderList 订单列表
     * @param int $userId 用户ID
     * @return void
     */
    public function checkOrder($orderList, $userId)
    {
        // dump($orderList);die;
        $change = array();
        $currentTime = time();
        // 1. 未支付订单 超过15分钟未支付就会被自动取消
        foreach ($orderList as $k => $v) {
            $temp = [];
            if ($v['status'] == 1 && $currentTime - strtotime($v['create_time']) > 900) {
                $orderList[$k]['status'] = 5;
                $temp['status'] = 5;
                $temp['order_id'] = $v['order_id'];
                $temp['cancel_time'] = time();
                $change[] = $temp;
            }
            if ($v['status'] == 3 && $currentTime - $v['express_time'] > 86400 * 7) {
                $orderList[$k]['status'] = 4;
                $temp['status'] = 4;
                $temp['order_id'] = $v['order_id'];
                $temp['confirm_time'] = $v['express_time'] + 86400 * 7;
                // 订单完成就要去更新当前的分销信息
                $this->updateUserDisFee($v['order_id'], $userId);
                $change[] = $temp;
            }
        }
        if (count($change) > 0) {
            $order = new Order;
            $order->isUpdate()->saveAll($change);
        }

        return $orderList;
    }

    /**
     * 用户订单新增
     *
     * @return void
     */
    public function addOrder()
    {
        if (!request()->isPost() || !request()->param('openid')) {
            $res['code'] = "401";
            $res['msg'] = 'Invaild Request';
            return json_encode($res);
        }
        // 检测当前订单是否可以下单
        $goodsDetail = request()->param('goodsDetail/a');
        $searchGoodsIds = array();
        foreach ($goodsDetail as $k => $v) {
            $searchGoodsIds[] = $v['detail_id'];
        }
        // 商品库存检测
        $goods_detail = new Goods_detail;
        $goodsStock = $goods_detail->where('idx', 'in', $searchGoodsIds)->where('stock', '>', 0)->field('stock, goods_id, idx')->select();
        if ((!$goodsStock || count($goodsStock) == 0)) {
            $res['code'] = "403";
            $res['msg'] = 'No Goods Stock';
            return json_encode($res);
        }
        $goodsStock = collection($goodsStock)->toArray();
        foreach ($goodsStock as $k => $v) {
            foreach ($goodsDetail as $ke => $va) {
                if ($v['idx'] == $ke['detail_id'] && $v['stock'] < $va['quantity']) {
                    $res['code'] = "403";
                    $res['msg'] = 'No Goods Stock';
                    return json_encode($res);
                }
            }
        }

        // 数据处理
        $order = new Order;
        $order_detail = new Order_detail;
        // 订单号
        $orderInfo['order_id'] = $this->getTradeNo();
        $orderInfo['user_openid'] = request()->param('openid');
        $orderInfo['user_id'] = request()->param('userid');
        $orderInfo['user_name'] = request()->param('userName');
        $orderInfo['total_fee'] = request()->param('totalFee');
        $orderInfo['tel_num'] = request()->param('telNumber');
        $orderInfo['address'] = htmlspecialchars(request()->param('address'));
        $orderInfo['express_fee'] = request()->param('expressFee');
        $orderInfo['message'] = htmlspecialchars(request()->param('message'));
        $orderInfo['create_time'] = time();
        // 构造订单详情
        foreach ($goodsDetail as &$info) {
            $info['order_id'] = $orderInfo['order_id'];
            $info['create_time'] = time();
        }
        $orderDetail = $goodsDetail;
        // 将数据插入数据库中
        Db::startTrans();
        try {
            $insertOrder = Db::name('order')->insert($orderInfo);
            $insertOrderDetail = Db::name('order_detail')->insertAll($orderDetail);
            // 提交事务
            Db::commit();
            if (!$insertOrder || !$insertOrderDetail) {
                throw new \Exception("Insert Failed");
            } else {
                $res['code'] = "200";
                $res['order_id'] = $orderInfo['order_id'];
                $res['msg'] = 'success';
                return json_encode($res);
            }
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $res['code'] = "400";
            $res['msg'] = "Insert Order Failed";
            return json_encode($res);
        }
    }

    /**
     * 生成核销二维码
     *
     * @param string $orderId 订单号
     * @return void
     */
    public function generateOrderQRCode($orderId)
    {
        //post数据
        $postData = array('page' => 'pages/verify/verify', 'width' => 1080, 'scene' => 'orderId=' . $orderId);
        $postData = json_encode($postData);

        $accessToken = $this->getAccessToken();
        //请求QRcode
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $accessToken;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return ['errcode' => 401, 'errmsg' => '请求QRcode发生错误', 'error' => $error];
        }
        //错误返回,返回的是微信的错误码与错误信息
        if (!empty($data['errcode'])) {
            return $data;
        }

        $date = date('Ymd', time());
		//QRcode文件夹
        $targetDir = 'public' . DS . 'qrcode' . DS . $date . DS;
        
		//QRcode文件名
        $fileName = md5($data);
		//创建QRcode目录
        if (!is_dir(ROOT_PATH . $targetDir)) {
            mkdir($targetDir);
        }
		//QRcode文件路径
        $filePath = $targetDir . $fileName . '.png';
		//写入
        $result = file_put_contents($filePath, $data);
        if ($result) {
			// 处理qrcodePath字符
            $filePath = DS . $filePath;
            return $filePath;
        } else {
            return ['errcode' => 402, 'errmsg' => '图片写入失败'];
        }
    }

    /**
     * 更新商品库存
     *
     * @param array $deleteStockArr 存放的商品数组
     * @return void
     */
    public function deleteStock($deleteStockArr)
    {
        // 获取商品id
        $detailIds = [];
        foreach ($deleteStockArr as $k => $v) {
            $detailIds[] = $v['detail_id'];
        }
        // 获取原有商品数量
        $goods_detail = new Goods_detail;
        $oriGoodsNum = $goods_detail->where('idx', 'in', $detailIds)->field('idx, stock, sellnum')->select();
        $oriGoodsNum = collection($oriGoodsNum)->toArray();
        // 更新商品库存的数组
        $updateArr = [];
        foreach ($deleteStockArr as $ke => $va) {
            foreach ($oriGoodsNum as $k => $v) {
                if ($va['detail_id'] == $v['idx']) {
                    $temp['stock'] = $v['stock'] - $va['quantity'];
                    $temp['sellnum'] = $v['sellnum'] + $va['quantity'];
                    $temp['idx'] = $v['idx'];
                    $updateArr[] = $temp;
                    break 1;
                }
            }
        }
        $update = $goods->isUpdate()->saveAll($updateArr);
    }

    /**
     * 更新用户的rebate以及相关的分销信息
     * 
     * @param string $orderId 订单号
     * @param string $userId 用户ID
     * @return void
     */
    public function updateUserDisFee($orderId, $userId)
    {
        // 1 获取订单分销数据
        $order_detail = new Order_detail;
        $orderDisInfo = $order_detail->where('order_id', $orderId)->where('is_distri', 1)->field('quantity, shop_price, goods_id, detail_id, dis_percent, parent_dis_percent, grand_dis_percent')->select();
        if (!$orderDisInfo || count($orderDisInfo) == 0) return;
        $orderDisInfo = collection($orderDisInfo)->toArray();

        // 构造goodsIds
        $goodsIds = [];
        foreach ($orderDisInfo as $k => $v) {
            $goodsIds[] = $v['goods_id'];
        }

        // 2 判断当前用户在该分销里是否有对应商品的相关分销信息
        $distribution = new Distribution;
        $distributionList = $distribution->where('user_id', $userId)->where('goods_id', 'in', $goodsIds)->field('user_id, parent_id, grand_id, goods_id')->select();
        if (!$distributionList || count($distributionList) == 0) return;
        $distributionList = collection($distributionList)->toArray();

        $distriArr = [];

        // 有分销数据进行分销数据 整理合并 并入库
        foreach ($distributionList as $k => $v) {
            foreach ($orderDisInfo as $ke => $va) {
                $goodsDisFee = number_format($va['quantity'] * $va['shop_price'] * $va['dis_percent'] * 0.01, 2);
                $temp = [];
                if ($v['goods_id'] == $va['goods_id']) {
                    $temp['parent_id'] = $v['parent_id'];
                    $temp['grand_id'] = $v['grand_id'];
                    $temp['parent_fee'] = number_format($goodsDisFee * $va['parent_dis_percent'] * 0.01, 2);
                    $temp['parent_percent'] = $va['parent_dis_percent'];
                    $temp['grand_fee'] = $v['grand_id'] != 0 ? number_format($goodsDisFee * $va['grand_dis_percent'] * 0.01, 2) : 0;
                    $temp['grand_percent'] = $v['grand_id'] != 0 ? $va['grand_dis_percent'] : 0;
                    $temp['goods_id'] = $va['goods_id'];
                    $temp['detail_id'] = $va['detail_id'];
                    $temp['dis_percent'] = $va['dis_percent'];
                    $temp['order_id'] = $orderId;
                    $temp['user_id'] = $userId;
                    $temp['create_time'] = time();
                    $distriArr[] = $temp;
                }
            }
        }

        // 更新数据库的分销数据
        $distribution_fee = new Distribution_fee;
        $distribution_fee->isUpdate(false)->allowField(true)->saveAll($distriArr);

        // 3 更新指定用户的分销数据
        $updateArr = [];

        $userAccountInfo = Cache::get('userAccountInfo');
        foreach ($distriArr as $ke => $va) {
            foreach ($userAccountInfo as $k => $v) {
                // 重置temp 避免上面数据污染
                $temp = [];
                if ($va['parent_id'] && $v['userID'] == $va['parent_id']) {
                    $userAccountInfo[$k]['rebate'] += number_format($va['parent_fee'], 2);
                    $temp['rebate'] = number_format($va['parent_fee'], 2);
                    $temp['user_id'] = $v['userID'];
                    $updateArr[] = $temp;

                }
                if ($va['grand_id'] && $v['userID'] == $va['grand_id']) {
                    $userAccountInfo[$k]['rebate'] += number_format($va['grand_fee'], 2);
                    $temp['rebate'] = number_format($va['grand_fee'], 2);
                    $temp['user_id'] = $v['userID'];
                    $updateArr[] = $temp;
                }
            }
        }

        // 对 updateArr做重复处理
        $updateArrNotRepeat = [];
        foreach ($updateArr as $k => $v) {
            $isFind = false;
            foreach ($updateArrNotRepeat as &$info) {
                if ($info['user_id'] == $v['user_id']) {
                    $info['rebate'] += $v['rebate'];
                    $isFind = true;
                    break 1;
                }
            }

            if (!$isFind) {
                $userDis['user_id'] = $v['user_id'];
                $userDis['rebate'] = $v['rebate'];
                $updateArrNotRepeat[] = $userDis;
            }
        }
        // dump($updateArr);
        // dump($updateArrNotRepeat);

        Cache::set('userAccountInfo', $userAccountInfo, 0);
        // 数据库更新
        if (count($updateArrNotRepeat) > 0) {
            $userinfo = new Userinfo;
            $saveAll = $userinfo->isUpdate()->saveAll($updateArrNotRepeat);
        }
    }

    /**
     * 检测微信支付的结果
     *
     * @return void
     */
    public function checkWxPay()
    {
        if (!request()->isPost()) return;
        $orderId = request()->param('orderid');
        $prepayCache = $this->checkPrepay($orderId);
        if ($prepayCache && $prepayCache['isChecked']) {
            Db::name('order')->where('order_id', $orderId)->update(['status' => 2, 'pay_time' => time()]);
            // 更新商品库存
            $this->updateGoodsStock($orderId);
            $res['code'] = "200";
            $res['msg'] = "success";
        } else {
            $res['code'] = "400";
            $res['msg'] = "success";
        }
        return json_encode($res);
    }

    /**
     * 获取订单预支付缓存信息
     *
     * @return void
     */
    public function checkPrepay($orderId = null)
    {
        $prepayCache = Cache::get('prepayCache');
        if (!$prepayCache) {
            return null;
        }
        if ($orderId && $prepayCache) {
            $isExpire = null;
            foreach ($prepayCache as $k => $v) {
                if (isset($v['orderid']) && $v['orderid'] == $orderId) {
                    if ($v['order_expire_time'] < time()) {
                        $isExpire = $k;
                        break;
                    } else {
                        return $v;
                    }
                }
            }
            if (isset($isExpire)) {
                array_splice($prepayCache, $isExpire, 1);
                Cache::set('prepayCache', $prepayCache, 0);
            }
            return null;
        }
        return $prepayCache;
    }
    /**
     * 更新商品库存
     *
     * @return void
     */
    public function updateGoodsStock($orderId)
    {
        $order_detail = new Order_detail;
        // 先获取订单详情
        $orderDetail = $order_detail->where('order_id', $orderId)->field('detail_id, goods_id, quantity')->select();
        $orderDetail = collection($orderDetail)->toArray();
        // 获取商品库存
        $detailIds = [];
        foreach ($orderDetail as $k => $v) {
            $detailIds[] = $v['detail_id'];
        }
        $goods_detail = new Goods_detail;
        $goodsDetail = $goods_detail->where('idx', 'in', $detailIds)->field('stock, sellnum, goods_id, idx')->select();
        $goodsDetail = collection($goodsDetail)->toArray();

        foreach ($goodsDetail as &$info) {
            foreach ($orderDetail as $k => $v) {
                if ($info['idx'] == $v['detail_id'] && $info['goods_id'] == $v['goods_id']) {
                    $info['sellnum'] = $info['sellnum'] + $v['quantity'];
                    $info['stock'] = $info['stock'] - $v['quantity'];
                    break 1;
                }
            }
        }

        $update = $goods_detail->isUpdate()->saveAll($goodsDetail);
        // dump($update);
    }

    /**
     * 用户完成付款操作
     *
     * @return void
     */
    public function finishPay()
    {
        if (!request()->isPost()) return;
        $orderID = request()->param('orderid');
        $openid = request()->param('openid');
        if (!$orderID || !$openid) {
            return "Invail Param";
        }
        // 更新订单状态
        $order = new Order;
        $update = $order->where('order_id', $orderID)->update(['state' => 2, 'pay_time' => time()]);
        if ($update) {
            // 删掉支付缓存操作
            $res['code'] = "200";
            $res['msg'] = "success";
        } else {
            $res['code'] = "400";
            $res['msg'] = "failed";
        }
        return json_encode($res);
    }

    /**
     * 用户确认收货
     *
     * @return void
     */
    public function userConfirmOrder()
    {
        if (!request()->isPost()) return;
        $userId = request()->param('userid');
        $orderId = request()->param('orderid');
        $openid = request()->param('openid');

        $order = new Order;
        // 先判断是否有被核销
        $orderStatus = $order->where('order_id', $orderId)->value('status');
        // 更新order表
        if ($orderStatus != 3) {
            $res['code'] = "201";
            $res['msg'] = 'Order Status Has Been Changed';
        } else {
            $update = $order->where('order_id', $orderId)->update(['status' => 4, 'confirm_time' => time()]);
            // 更新对应分销信息
            $this->updateUserDisFee($orderId, $userId);
            if ($update) {
                $res['code'] = "200";
                $res['msg'] = "success";
            } else {
                $res['code'] = "400";
                $res['msg'] = "failed";
            }
        }

        return json_encode($res);
    }

    /**
     * 用户申请售后
     *
     * @return void
     */
    public function orderApplyAS()
    {
        if (!request()->isPost()) return;
        $userId = request()->param('userid');
        $orderId = request()->param('orderid');

        // 更改订单状态
        $order = new Order;
        $update = $order->where('order_id', $orderId)->update(['status' => 6, 'onas_time' => time()]);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "success";
        } else {
            $res['code'] = "400";
            $res['msg'] = "failed";
        }
        return json_encode($res);
    }

    /**
     * 用户申请售后
     *
     * @return void
     */
    public function orderRefund()
    {
        if (!request()->isPost()) return;
        $userId = request()->param('userid');
        $orderId = request()->param('orderid');

        // 更改订单状态
        $order = new Order;
        $update = $order->where('order_id', $orderId)->update(['status' => 8, 'apply_refound_time' => time()]);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "success";
        } else {
            $res['code'] = "400";
            $res['msg'] = "failed";
        }
        return json_encode($res);
    }

    /**
     * 删除预支付订单请求中对应订单号的缓存
     *
     * @param int $orderID
     * @return void
     */
    public function clearPrepayCache($orderID)
    {
        if (!$orderID || !is_int($orderID)) {
            return;
        }
        $prepayCache = Cache::get('prepayCache');
        if (!$prepayCache) {
            return;
        }
        $currentOrderKey = "";
        foreach ($prepayCache as $k => $v) {
            if ($v['orderid'] == $orderId) {
                $currentOrderKey = $k;
                break;
            }
        }
        if ($currentOrderKey) {
            array_splice($prepayCache, $currentOrderKey, 1);
            Cache::set('prepayCache', $prepayCache, 0);
        }
    }

    /**
     * 用户发起生成核验二维码
     *
     * @return void
     */
    public function genVerify()
    {
        if (!request()->isPost()) return;
        $userId = request()->param('userid');
        $orderId = request()->param('orderid');
        $verifyPath = $this->generateOrderQRCode($orderId);
        // 如果生成成功就更新数据库
        if ($verifyPath) {
            $order = new Order;
            $update = $order->where('order_id', $orderId)->update(['verify_url' => $verifyPath]);
            if ($update) {
                $res['code'] = "200";
                $res['msg'] = "success";
                $res['verifyUrl'] = "https://ft.up.maikoo.cn" . $verifyPath;
            } else {
                $res['code'] = "400";
                $res['msg'] = "fail";
            }
        } else {
            $res['code'] = "400";
            $res['msg'] = "fail";
        }
        return json_encode($res);
    }

    /**
     * 获取需要核验的订单信息
     *
     * @return void
     */
    public function getVerifyOrder()
    {
        if (!request()->isPost()) return;
        $orderId = request()->param('orderid');
        if (!$orderId) {
            $res['code'] = "400";
            $res['msg'] = "Invaild Orderid";
            return json_encode($res);
        }

        // 判断当前订单是否可以被核销
        $order = new Order;
        $orderInfo = $order->where('order_id', $orderId)->field('status, order_id, is_verify')->find();
        if ($orderInfo['status'] != 3 || $orderInfo['is_verify'] == 1) {
            $res['code'] = "201";
            $res['msg'] = "Current Order Has Been Verified";
            return json_encode($res);
        }

        // 查询订单详情
        $order_detail = new Order_detail;
        $orderDetail = $order_detail->alias('d')->join('ft_goods g', 'd.goods_id = g.goods_id', 'LEFT')->where('d.order_id', $orderId)->field('d.idx, d.goods_id, d.detail_id, d.quantity, d.market_price, d.shop_price, g.name, g.pic')->select();
        $orderDetail = collection($orderDetail)->toArray();
        // $orderDetail = $orderDetail[0];
        foreach ($orderDetail as $k => $v) {
            $orderDetail[$k]['pic'] = "https://ft.up.maikoo.cn" . $v['pic'];
        }
        $res['code'] = "200";
        $res['data'] = $orderDetail;
        $res['msg'] = "Get Current OrderDetail Info Success";
        return json_encode($res);
    }

    /**
     * 用户发起提现申请
     *
     * @return void
     */
    public function userGetRebate()
    {
        if (!request()->isPost()) return;
        $userId = request()->param('userid');
        $openid = request()->param('openid');
        $rebate = request()->param('rebate');
        // 向rebate表写入数据
        $user_rebate = new User_rebate;
        $insert = $user_rebate->insert(['rebate' => $rebate, 'user_id' => $userId, 'created_at' => time()]);
        if ($insert) {
            // 将用户缓存中的rebate重置为0
            $userInfo = Cache::get('userAccountInfo');
            foreach ($userInfo as $k => $v) {
                if ($v['userID'] == $userId) {
                    $userInfo[$k]['rebate'] = 0.00;
                    break 1;
                }
            }
            Cache::set('userAccountInfo', $userInfo, 0);

            $res['code'] = "200";
            $res['msg'] = "Success";
        } else {
            $res['code'] = "400";
            $res['msg'] = "Failed";
        }
        return json_encode($res);
    }

    /**
     * 确认核销
     *
     * @return void
     */
    public function confirmVerify()
    {
        if (!request()->isPost()) return;
        $orderId = request()->param('orderid');
        
        // 更新订单表
        $order = new Order;
        $orderInfo = $order->where('order_id', $orderId)->field('status, order_id, is_verify')->find();
        if ($orderInfo['status'] != 3 || $orderInfo['is_verify'] == 1) {
            $res['code'] = "201";
            $res['msg'] = "Current Order Has Been Verified";
            return json_encode($res);
        }

        $update = $order->where('order_id', $orderId)->update(['status' => 4, 'verify_time' => time()]);
        if ($update) {
            $buyerId = Db::name('order')->where('order_id', $orderId)->value('user_id');
            // 订单核销
            $this->updateUserDisFee($orderId, $buyerId);
            $res['code'] = "200";
            $res['msg'] = "success";
        } else {
            $res['code'] = "400";
            $res['msg'] = "fail";
        }
        return json_encode($res);
    }

    /**
     * 通过orderID获取订单详情
     *@param int $orderid
     * 
     * @return void
     */
    public function getOrderById(Request $request)
    {
        $orderId = $request->param('orderid');
        if (!$orderId) {
            $res['code'] = "400";
            $res['msg'] = "Invaild Orderid";
            return json_encode($res);
        }
        $order = new Order;
        $orderField = "order_id, user_openid, user_id, total_fee, express_fee, status, create_time, promotion_id, pay_time, user_name, total_fee, address, tel_num, express_time, verify_url, is_verify, is_refound";
        $orderInfo = $order->where('order_id', $orderId)->where('is_delete', 0)->field($orderField)->find();
        // 如果订单被删除或订单不存在 返回201
        if (!$orderInfo) {
            $res['code'] = "201";
            $res['msg'] = "Current Order Is Been Delete Or Not Exist";
            return json_encode($res);
        }

        $currentTime = time();
        // 订单状态判断
        if ($orderInfo['status'] == 1 && $currentTime - strtotime($orderInfo['create_time']) > 900) {
            $orderInfo['status'] = 5;
            $temp['status'] = 5;
            $temp['cancel_time'] = time();
            $order->where('order_id', $orderId)->update($temp);
        }
        if ($orderInfo['status'] == 3 && $currentTime - $orderInfo['express_time'] > 86400 * 7) {
            $orderInfo['status'] = 4;
            $temp['status'] = 4;
            $temp['confirm_time'] = $orderInfo['express_time'] + 86400 * 7;
            $order->where('order_id', $orderId)->update($temp);
            // 订单完成就要去更新当前的分销信息
            $this->updateUserDisFee($orderId, $userId);
        }

        // 简单的数据处理
        $orderInfo['pay_time'] = $orderInfo['pay_time'] == 0 ? '' : date('Y-m-d H:i:s', $orderInfo['pay_time']);
        $orderInfo['verify_url'] = !empty($orderInfo['verify_url']) ? "https://ft.up.maikoo.cn" . $orderInfo['verify_url'] : '';
        
        // 订单状态转换
        switch ($orderInfo['status']) {
            case 1:
                $orderInfo['status_conv'] = '待付款';
                break;
            case 2:
                $orderInfo['status_conv'] = '待发货';
                break;
            case 3:
                $orderInfo['status_conv'] = '待收货';
                break;
            case 4:
                $orderInfo['status_conv'] = '已完成';
                break;
            case 5:
                $orderInfo['status_conv'] = '已取消';
                break;
            case 6:
                $orderInfo['status_conv'] = '售后中';
                break;
            case 7:
                $orderInfo['status_conv'] = '售后完成';
                break;
            case 8:
                $orderInfo['status_conv'] = '申请退款中';
                break;
            case 9:
                $orderInfo['status_conv'] = '退款处理完成';
                break;
        }

        // 查询订单详情
        $promotion = new Promotion;
        $order_detail = new Order_detail;
        $orderDetail = $order_detail->alias('d')->join('ft_goods g', 'd.goods_id = g.goods_id', 'LEFT')->join('ft_catagory c', 'd.catagory_id = c.catagory_id', 'LEFT')->where('d.order_id', $orderId)->field('d.idx, d.goods_id, d.detail_id, d.quantity, d.market_price, d.shop_price, d.promotion_id, g.name, g.pic, c.catagory_name')->select();
        $orderDetail = collection($orderDetail)->toArray();
        // $orderDetail = $orderDetail[0];
        foreach ($orderDetail as $k => $v) {
            $orderDetail[$k]['pic'] = "https://ft.up.maikoo.cn" . $v['pic'];
            if ($v['promotion_id'] != 0) {
                $promotionInfo = $promotion->where('promotion_id', $v['promotion_id'])->field('count, name')->select();
                if ($promotionInfo && count($promotionInfo) > 0) {
                    $promotionInfo = collection($promotionInfo)->toArray();
                    $promotionInfo = $promotionInfo[0];
                    $orderDetail[$k]['promotion_count'] = number_format($promotionInfo['count'] / 10, 1);
                    $orderDetail[$k]['promotion_name'] = $promotionInfo['name'];
                }
            }

        }
        $orderInfo['detail'] = $orderDetail;
        $res['code'] = "200";
        $res['data'] = $orderInfo;
        $res['msg'] = "Get Current Order Info Success";
        return json_encode($res);
    }

    /**
     * 用户购物车界面卸载时 onUnload方法 进行用户购物车的数据更新
     *
     * @param Request $request
     * @return void
     */
    public function updateUserCart(Request $request)
    {
        $userCartInfo = Cache::get('userCartInfo');
        $userOpenid = $request->param('openid');
        // 直接覆盖
        $goodsInfo = $request->param('goodsInfo/a');
        if (!$userCartInfo) {
            return;
        }
        foreach ($userCartInfo as $k => $v) {
            if ($v['user_openid'] == $userOpenid) {
                $userCartInfo[$k]['goodsInfo'] = $goodsInfo;
            }
        }
        Cache::set('userCartInfo', $userCartInfo, 0);
    }

    /**
     * 产生订单号
     * @return string 订单号 生成规则为 0323 + timestamp后二位 + microtime前三位(小数点后)
     */
    public function getTradeNo()
    {
        $out_trade_no = "";
        $micorTime = microtime();
        $micorTime = explode('.', $micorTime);
        $micorTime = substr($micorTime[1], 0, 3);
        $out_trade_no = date('md', time()) . substr(strval(time()), -3, -1) . $micorTime;
        // $out_trade_no .= substr(time(), -4);
        return $out_trade_no;
    }

    /**
     * 用户删除订单
     *
     * @param Request $request
     * @return void
     */
    public function deleteOrder(Request $request)
    {
        $orderid = $request->param('orderid');
        if (!$orderid) {
            $res['code'] = "400";
            $res['msg'] = "Invaild Orderid";
            return json_encode($res);
        }
        $order = new Order;
        $update = $order->update(['order_id' => $orderid, 'is_delete' => 1, 'delete_time' => time()]);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "Delete Order Success";
        } else {
            $res['code'] = "400";
            $res['msg'] = "Delete Order Failed";
        }
        return json_encode($res);
    }

    /**
     * 用户取消订单
     *
     * @param Request $request
     * @return boolean
     */
    public function cancelOrder(Request $request)
    {
        $orderid = $request->param('orderid');
        if (!$orderid) {
            $res['code'] = "400";
            $res['msg'] = "Invaild Orderid";
            return json_encode($res);
        }
        $order = new Order;
        $update = $order->where('order_id', $orderid)->update(['status' => 5, 'cancel_time' => time()]);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "Delete Order Success";
        } else {
            $res['code'] = "400";
            $res['msg'] = "Delete Order Failed";
        }
        return json_encode($res);
    }

    public function test()
    {
        $userInfo = Cache::get('userAccountInfo');
        foreach ($userInfo as $k => $v) {
            if ($v['userID'] == 114) {
                $userInfo[$k]['rebate'] = 0;
                break;
            }
        }
        Cache::set('userAccountInfo', $userInfo);
        dump($userInfo);
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
    public function getAccessToken()
    {
        $accessToken = Cache::get('accessToken');
        if (!$accessToken) {
            $appid = 'wx57beee95d7c48bbe';
            $appsecret = '774e5f55826cce1d828ab7faf14c3e09';
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;

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
    public function sendTempletMessage($postData)
    {

        $accessToken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $accessToken;
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
        $info = get_object_vars($info);
        return $info;
    }

    /**
     * 获取用户协议条款
     *
     * @return void
     */
    public function getCaluse()
    {
        $clause = new Clause;
        $clauseInfo = $clause->where('idx', 1)->find();
        $res["clause"] = htmlspecialchars_decode($clauseInfo['clause']);
        $res["msg"] = "success";
        $res["code"] = "200";
        return json_encode($res);
    }

    /**
     * 生成方特活动海报
     *
     * @return void
     */
    public function generateActivityPoster($imgDir, $qrcodePath, $activityInfo)
    {
        $assetDir = 'public' . DS;
        // 字体对应的路径
        $fontPath = $assetDir . 'fzkt.ttf';

        // dump($imgDir); dump($qrcodePath); dump($activityInfo);die;
        // 打开baseImage 纯白底色 300 * 600        
        $baseImagePath = $assetDir . 'base.png';
        $image = \think\Image::open($baseImagePath);

        $finalName = md5(implode($activityInfo));
        $finailImagePath = $imgDir . $finalName . '.png';
        
        // 在左下角区域插入二维码 区域 100 * 100 二维码大小为 80 * 80
        // $qrImageCropPath = $imgDir . 'qr-crop.png';
        // $imageQr = \think\Image::open('.' . $qrcodePath);
        // $imageQr->thumb(80, 80)->save($qrImageCropPath);

        // 将活动封面 生成250 * 100 的缩略图 丙插入在baseimage 区域 300 * 150 
        // $bannerImagePath = $imgDir . 'banner.jpg';
        $imageBanner = \think\Image::open('.' . $activityInfo['pic']);
        $imageBannerWidth = $imageBanner->width();
        $offsetX = (1000 - $imageBannerWidth) / 2;
        // $imageBannerHeight = $imageBanner->height();
        $image->water('.' . $activityInfo['pic'], [$offsetX, 50]);

        // 将活动名称 插入在 活动图下方 区域 300 * 50
        $image->text($activityInfo['name'], $fontPath, 50, '#00000000', Image::WATER_NORTH, [0, 550]);

        // 将活动时间插入在活动名称下方
        $image->text(date('Y-m-d H:i', $activityInfo['start_time']) . ' - ' . date('Y-m-d H:i', $activityInfo['end_time']), $fontPath, 35, '#00000000', Image::WATER_NORTH, [0, 700]);

        // 将活动奖项插入在 活动名称下方
        if (!empty($activityInfo['first_price_num'])) $image->text('一等奖' . '   ' . $activityInfo['first_price_num'] . '人   ' . $activityInfo['first_price'], $fontPath, 30, '#00000000', Image::WATER_NORTH, [0, 800]);
        if (!empty($activityInfo['second_price_num'])) $image->text('二等奖' . '   ' . $activityInfo['second_price_num'] . '人   ' . $activityInfo['second_price'], $fontPath, 30, '#00000000', Image::WATER_NORTH, [0, 900]);
        if (!empty($activityInfo['third_price_num'])) $image->text('三等奖' . '   ' . $activityInfo['third_price_num'] . '人   ' . $activityInfo['third_price'], $fontPath, 30, '#00000000', Image::WATER_NORTH, [0, 1000]);

        // 将活动简介插入在奖项下方
        if (strlen($activityInfo['brief']) > 25) {
            $brief_first = mb_substr($activityInfo['brief'], 24);
            $brief_second = mb_substr($activityInfo['brief'], 30, mb_strlen($activityInfo['brief']) - 1);
            $image->text($brief_first, $fontPath, 30, '#00000000', Image::WATER_NORTH, [0, 1150]);
            $image->text($brief_second, $fontPath, 30, '#00000000', Image::WATER_NORTH, [0, 1250]);
        } else {
            $image->text($activityInfo['brief'], $fontPath, 30, '#00000000', Image::WATER_NORTH, [0, 1200]);
        }

        // 水印横线
        $image->text('- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -', $fontPath, 30, '#00000000', Image::WATER_NORTHWEST, [10, 1400]);

        $image->water('.' . $qrcodePath, [10, 1450]);

        // 将小程序的名称放置在右下角
        $image->text('AQ大玩家', $fontPath, 40, '#00000000', Image::WATER_NORTHWEST, [560, 1550]);
        $image->text('更多精彩等你来', $fontPath, 40, '#00000000', Image::WATER_NORTHWEST, [560, 1700]);

        $image->save($finailImagePath);
        
        // drop 无用的文件 1 裁剪后的banner 2. 缩减后的qrcode
        // @unlink($qrImageCropPath);

        return $finailImagePath;
    }

    /**
     * 获取活动的小程序二维码
     *
     * @return void
     */
    public function getActivityQRCode($activityID)
    {

        // 查询activityInfo
        $activityInfo = Db::name('activity')->where('activity_id', $activityID)->field('name, brief, first_price, second_price, third_price, first_price_num, second_price_num, third_price_num, pic, start_time, end_time')->find();
        if (!$activityInfo) return false;

        $postData = array('page' => 'pages/activitydetail/activitydetail', 'width' => 480, 'scene' => $activityID);
        $postData = json_encode($postData);

        $accessToken = $this->getAccessToken();
        //请求QRcode
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $accessToken;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return ['errcode' => 401, 'errmsg' => '请求QRcode发生错误', 'error' => $error];
        }
        //错误返回,返回的是微信的错误码与错误信息
        if (!empty($data['errcode'])) return $data;

        $date = date('Ymd', time());
		//QRcode文件夹
        $targetDir = 'public' . DS . 'actposter' . DS;
        
		//QRcode文件名
        $fileName = md5($data);
		//创建QRcode目录
        if (!is_dir(ROOT_PATH . $targetDir)) mkdir($targetDir);
		//QRcode文件路径
        $filePath = $targetDir . $fileName . '.png';
		//写入
        $result = file_put_contents($filePath, $data);

        if (!$result) return ['errcode' => 402, 'errmsg' => '图片写入失败'];

        $filePath = DS . $filePath;

        $activityPosterPath = $this->generateActivityPoster($targetDir, $filePath, $activityInfo);

        if ($activityPosterPath) Db::name('activity')->where('activity_id', $activityID)->update(['activity_poster' => $activityPosterPath, 'qrcode' => $filePath]);

        return $activityPosterPath;
    }
}
