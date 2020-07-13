<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;

use app\index\model\Column;
use app\index\model\Coupon;
use app\index\model\User_coupon;
use app\index\model\System_setting;

class Store extends Controller
{

    /**
     * 获取商城首页列表数据
     *
     * @return void
     */
    public function getStoreIndex()
    {
        // 构造返回数据
        $indexData = [];
        // 1 获取banner
        $banner = getBanner(0, false);
        if (!$banner) {
            $temp['img_id'] = 0;
            $temp['img_src'] = "";
            $banner[] = $temp;
        }
        $indexData['banner'] = $banner;
        $indexData['top'] = [];
        $indexData['column'] = [];
        // 2 获取 首页置顶的专栏和其它专栏信息
        $columnField = "column_id, is_top";
        $columnIdList = getColumn($columnField, false, 2);
        $columnList = [];
        if ($columnIdList) {
            foreach ($columnIdList as $k => $v) {
                $temp = getColumnById($v['column_id'], false, $v['is_top'] == 1 ? 6 : 3);
                if ($temp) {
                    if ($temp['is_top'] == 1) {
                        $indexData['top'] = $temp;
                    } else if (!empty($temp['goods'])) {
                        $columnList[] = $temp;
                    }
                }
            }
            $indexData['column'] = $columnList;
        }
        $indexData['column'] = $columnList;
        // 3 获取十个子分类
        $catagoryField = "cat_id, cname, img";
        $catList = getCatagory($catagoryField, false, true);
        if (!$catList) {
            $catList = [];
        }
        $indexData['cat'] = $catList;
        return objReturn(0, 'success', $indexData);
    }

    /**
     * 获取小程序设置
     *
     * @return void
     */
    public function getSystemSetting()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $uid = intval(request()->param('uid'));
        if (empty($uid)) return objReturn(401, 'Invaild Param');
        $system_setting = new System_setting;
        $systemSetting = $system_setting->where('idx', 2)->field('mini_name, mini_color, logi_fee, logi_free_fee, service_phone, mch_distance, is_layer_show, layer_img, layer_nav_type, layer_nav_id, share_text, member_fee, member_period')->select();
        $systemSetting = collection($systemSetting)->toArray();
        $systemSetting = $systemSetting[0];
        if ($systemSetting['is_layer_show']) $systemSetting['layer_img'] = config('STATIC_SITE_PATH') . $systemSetting['layer_img'];
        return objReturn(0, 'success', $systemSetting);
    }

    /**
     * 获取用户卡券列表
     *  
     * @param int $uid 用户ID
     * @param int $pageNum 需要获取的页码
     * @param int $status 需要获取的对应状态的status 0 未使用 1 已使用 3 已过期
     * @return void
     */
    public function getUserCoupon()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $uid = intval(request()->param('uid'));
        $pageNum = intval(request()->param('pageNum'));
        $status = intval(request()->param('status'));
        if (empty($uid)) return objReturn(401, 'Invaild Param');
        $user_coupon = new User_coupon;
        // 根据传过来的status分别获取不同的数据
        if ($status == 0) {
            $couponList = getCoupon(1, $pageNum);
        } else if ($status == 1) {
            $couponList = $user_coupon->alias('u')->join('sm_coupon c', 'u.coupon_id = c.coupon_id', 'LEFT')->where('u.uid', $uid)->field('u.use_at, c.coupon_id, c.coupon_sn, c.coupon_name, c.money, c.send_start_at, c.send_end_at, c.condition')->limit($pageNum * 10, 10)->select();
        } else if ($status == 2) {
            $couponList = getCoupon(3, $pageNum);
        }
        if (!$couponList) {
            return objReturn(0, 'No Coupon');
        }
        if ($status == 1) {
            $couponList = collection($couponList)->toArray();
            foreach ($couponList as &$info) {
                if (!empty($info['use_at'])) $info['use_at'] = date('Y-m-d H:i:s', $info['use_at']);
                $info['coupon_name'] = htmlspecialchars_decode($info['coupon_name']);
                $info['is_used'] = true;
            }
        } else if ($status == 0 || $status == 3) {
            // 判断当前卡券列表中是否有用户已使用过的卡券
            $userUserCouponIds = [];
            foreach ($couponList as $k => $v) {
                $userUserCouponIds[] = $v['coupon_id'];
            }
            $userUserCoupon = $user_coupon->where('coupon_id', 'in', $userUserCouponIds)->field('coupon_id, use_at')->select();
            if ($userUserCoupon && count($userUserCoupon) > 0) {
                $userUserCoupon = collection($userUserCoupon)->toArray();
                foreach ($userUserCoupon as $k => $v) {
                    foreach ($couponList as $ke => $va) {
                        if ($v['coupon_id'] == $va['coupon_id']) {
                            unset($couponList[$ke]);
                            break 1;
                        }
                    }
                }
            }
        }
        
        return objReturn(0, 'success', $couponList);
    }
}
