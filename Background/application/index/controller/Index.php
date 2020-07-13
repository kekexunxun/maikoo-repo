<?php
namespace app\index\controller;

use app\index\model\Activity;
use app\index\model\Activity_pride;
use app\index\model\Activity_user;
use app\index\model\Admin;
use app\index\model\Banner;
use app\index\model\Catagory;
use app\index\model\Clause;
use app\index\model\Distribution_fee;
use app\index\model\Goods;
use app\index\model\Goods_detail;
use app\index\model\Invite_code;
use app\index\model\Menu;
use app\index\model\Order;
use app\index\model\Order_detail;
use app\index\model\Power;
use app\index\model\Promotion;
use app\index\model\System_setting;
use app\index\model\Userinfo;
use app\index\model\User_rebate;
use \think\Cache;
use \think\Controller;
use \think\File;
use \think\Request;
use \think\Session;

class Index extends Controller
{
    /**
     * @return 登录界面
     */
    public function login()
    {
        // Session::delete('loginname');
        Session::clear();
        return $this->fetch();
    }

    /**
     * @return 退出登录界面
     */
    public function logout()
    {
        Session::delete('loginname');
        $url = 'http://ft.up.maikoo.cn/login';
        $this->redirect($url);
    }

    /**
     * @return 确认登录
     */
    public function checkLogin(Request $request)
    {
        $admin = new admin();
        $name = $request->param('username');
        $password = $request->param('password');
        // 最高管理员
        if ($name == 'admin') {
            $username = $name;
            // $res = $admin -> where('username',$username) ->find();
            $res = $admin->where('password', $password)->where('username', $name)->where('is_active', 1)->find();
            if ($res) {
                // 存登录名到全局session
                Session::set('loginname', $username);
                $ary = array(
                    'code' => 200,
                    'msg' => '登录成功！',
                );
            } else {
                $ary = array(
                    'code' => 10002,
                    'msg' => '密码错误！',
                );
            }
        } else {
            $username = intval($request->param('username'));
            $password = $request->param('password');
            $res = $admin->where('tel_num', $username)->find();
            if (!$res) {
                $ary = array(
                    'code' => 10001,
                    'msg' => '账号不存在！',
                );
            } else {
                $res = $admin->where('tel_num', $username)->where('is_active', 0)->find();
                if ($res) {
                    $ary = array(
                        'code' => 10003,
                        'msg' => '账号未启用！',
                    );
                } else {
                    $res = $admin->where('password', $password)->find();
                    if ($res) {
                        // 存登录名到全局session
                        Session::set('loginname', $username);
                        $ary = array(
                            'code' => 200,
                            'msg' => '登录成功！',
                        );
                    } else {
                        $ary = array(
                            'code' => 10002,
                            'msg' => '密码错误！',
                        );
                    }
                }
            }
        }
        return json($ary);
    }

    /**
     * @return 主页界面
     */
    public function index()
    {
        // 判断是否存在session
        if (!Session::has('loginname')) {
            header("Location: http://ft.up.maikoo.cn/login");
        } else {
            $telNum = Session::get('loginname');
            $admin = new Admin();
            if ($telNum == "admin") {
                $user_id = $admin->where('username', $telNum)->where('is_delete', 0)->value('user_id');
            } else {
                // 先查找管理员id
                $user_id = $admin->where('tel_num', $telNum)->where('is_delete', 0)->value('user_id');
            }
            // 存user_id到session
            Session::set('userId', $user_id);
            // 根据id找菜单的id
            $power = new Power();
            $menulist = $power->field('menu_id')->where('user_id', $user_id)->select();
            // 用户名与用户信息
            $username = $admin->where('user_id', $user_id)->where('is_delete', 0)->value('username');
            $this->assign("username", $username);
            $this->assign("menulist", $menulist);
            $this->assign('admin_id', $user_id);
            return $this->fetch();
        }
    }

    /**
     * @return 我的桌面界面
     */
    public function welcome()
    {
        $order = new Order();
        $userinfo = new Userinfo();
        $distribution_fee = new Distribution_fee();
        $user_rebate = new User_rebate();
        // 今日平台收入与今日已完成订单
        $starttime = strtotime(date("Y-m-d 00:00:00")); //当天时间戳
        $endtime = strtotime(date('Y-m-d', strtotime('+1 days'))); //当天结束
        $todaytotal = $order->where('confirm_time', 'between', [$starttime, $endtime])->where('status', 'in', [4, 7])->Sum('total_fee');
        $this->assign('todaytotal', $todaytotal);

        $todayorder = $order->field('order_id')->where('confirm_time', 'between', [$starttime, $endtime])->where('status', 'in', [4, 7])->where('is_delete', 0)->count();
        $this->assign('todayorder', $todayorder);
        // 昨日平台收入与昨日已完成订单
        $starttime2 = strtotime(date('Y-m-d', strtotime('-1 days'))); //昨天时间戳
        $endtime2 = strtotime(date("Y-m-d 00:00:00"));
        $yestotal = $order->where('confirm_time', 'between', [$starttime2, $endtime2])->where('status', 'in', [4, 7])->Sum('total_fee');
        $this->assign('yestotal', $yestotal);
        $yesorder = $order->field('order_id')->where('confirm_time', 'between', [$starttime2, $endtime2])->where('status', 'in', [4, 7])->where('is_delete', 0)->count();
        $this->assign('yesorder', $yesorder);
        // 会员信息-今日新增
        $todayuser = $userinfo->field('user_id')->where('create_time', 'between', [$starttime, $endtime])->count();
        $this->assign('todayuser', $todayuser);
        // 会员信息-昨日新增
        $yesuser = $userinfo->field('user_id')->where('create_time', 'between', [$starttime2, $endtime2])->count();
        $this->assign('yesuser', $yesuser);
        $starttime3 = strtotime(date('Y-m-01 00:00:00'));
        $endtime3 = strtotime(date('Y-m-d H:i:s'));
        // 会员信息-本月新增
        $monthuser = $userinfo->field('user_id')->where('create_time', 'between', [$starttime3, $endtime3])->count();
        $this->assign('monthuser', $monthuser);
        // 会员信息-会员总数
        $totaluser = $userinfo->field('user_id')->count();
        $this->assign('totaluser', $totaluser);
        // 今日新增分佣与昨日分佣
        $distirData = $distribution_fee->field('parent_id,parent_fee,grand_id,grand_fee,create_time')->where('is_success', 1)->select();
        $todaydistri = 0;
        $yesdistri = 0;
        // 非空判断
        if ($distirData && count($distirData) != 0) {
            $distirData = collection($distirData)->toArray();
            foreach ($distirData as $key => $value) {
                if ($value['create_time'] >= $starttime && $value['create_time'] < $endtime) {
                    if ($value['parent_id'] != 0) {
                        $temp1 += $value['parent_fee'];
                    }
                    if ($value['grand_id'] != 0) {
                        $temp2 += $value['grand_fee'];
                    }
                    $todaydistri = $temp1 + $temp2;
                }
                if ($value['create_time'] >= $starttime2 && $value['create_time'] < $endtime2) {
                    if ($value['parent_id'] != 0) {
                        $temp3 += $value['parent_fee'];
                    }
                    if ($value['grand_id'] != 0) {
                        $temp4 += $value['grand_fee'];
                    }
                    $yesdistri = $temp3 + $temp4;
                }
            }
        }
        $this->assign('todaydistri', $todaydistri);
        $this->assign('yesdistri', $yesdistri);
        // 今日新增退款与昨日新增退款
        // $refundData =

        // 非空判断
        // 提现与待提现人数
        $rebateData = $user_rebate->field('idx,status')->select();
        $rebate = 0;
        $rebatenum = 0;

        if ($rebateData && count($rebateData) != 0) {
            foreach ($rebateData as $key => $value) {
                if ($value['status'] = 0) {
                    $rebate += 1;
                }
                if ($value['status'] = 1) {
                    $rebatenum += 1;
                }
            }
        }
        $this->assign('rebate', $rebate);
        $this->assign('rebatenum', $rebatenum);
        // 有效订单与无效订单
        $orderData = $order->field('status')->select();
        $validorder = 0;
        $unvalidorder = 0;
        if ($orderData && count($orderData) != 0) {
            foreach ($orderData as $key => $value) {
                if ($value['status'] = 4 || $value['status'] = 6) {
                    $validorder += 1;
                } else {
                    $unvalidorder += 1;
                }
            }
        }
        $this->assign('validorder', $validorder);
        $this->assign('unvalidorder', $unvalidorder);
        return $this->fetch();
    }

    /**
     * 修改管理员密码功能
     *@return ary 修改结果
     */
    public function pwdUpdate(Request $request)
    {
        $adminId = intval($request->param('admin_id'));
        $oriPwd = $request->param('password');
        $newPwd = $request->param('password1');
        $admin = new Admin;
        $pwd = $admin->where('user_id', $adminId)->value('password');
        if ($oriPwd != $pwd) {
            $res['code'] = "300";
            $res['msg'] = "初始密码错误！";
        } else {
            $where['password'] = $newPwd;
            // 调用公共函数，参数true为更新
            $update = $admin->where('user_id', $adminId)->update($where);
            if ($update) {
                $res['code'] = "200";
                $res['msg'] = "修改成功！";
            } else {
                $res['code'] = "300";
                $res['msg'] = "修改失败！";
            }
        }
        return json($res);
    }

    /**
     * @return 用户信息界面
     */
    public function memberlist()
    {
        $userinfo = new Userinfo();
        $data = $userinfo->field('user_id')->select();
        $this->assign('userinfo', $data);
        return $this->fetch();
    }

    /**
     * memberDetail 用户详情
     * @return
     */
    public function memberDetail()
    {
        // 查询缓存的用户信息
        $userInfo = Cache::get("userAccountInfo");
        // dump($userInfo);die;
        $data = [];
        foreach ($userInfo as $key => $value) {
            // if($value['userID'] == 187) {
            //     $userInfo[$key]['userInfo']['avatarUrl'] = $value['userInfo']['avatar_url'];
            //     unset($userInfo[$key]['userInfo']['avatar_url']);
            // }
            // dump($key);
            $temp = [];
            $temp['userID'] = $value['userID'];
            $temp['inviteCode'] = $value['inviteCode'];
            $temp['nickName'] = !empty($value['userInfo']) ? $value['userInfo']['nickName'] : '';
            $temp['avatarUrl'] = !empty($value['userInfo']) ? $value['userInfo']['avatarUrl'] : '';
            $temp['gender'] = !empty($value['userInfo']) ? $value['userInfo']['gender'] : '';
            $temp['is_auth'] = $value['isAuth'];
            $temp['name'] = isset($value['userName']) ? $value['userName'] : '';
            $temp['rebate'] = number_format($value['rebate'], 2);
            $temp['user_openid'] = $value['user_openid'];
            $temp['identID'] = isset($value['identID']) ? $value['identID'] : '';
            $temp['tel_num'] = isset($value['telNum']) ? $value['telNum'] : '';
            $data[] = $temp;
        }
        // Cache::set('userAccountInfo', $userInfo);
        return json($data);
    }

    /**
     * 退款处理
     *
     * @return void
     */
    public function refound(Request $request)
    {
        $orderId = $request->param('order_id');
        $isRefound = $request->param('isRefound');
        $where['status'] = 9;
        $where['is_refound'] = $isRefound;
        $where['accept_refound_time'] = time();
        $order = new Order;
        $result = $order->where('order_id', $orderId)->update($where);
        if ($result) {
            $res['code'] = "200";
            $res['msg'] = "处理成功！";
        } else {
            $res['code'] = "300";
            $res['msg'] = "更改失败！";
        }
        return json($res);
    }

    /**
     * 用户分销详情
     * @return ary 返回数组
     */
    public function rebateinfo()
    {
        $request = Request::instance();
        $userid = intval($request->param('user_id'));
        // 调用Fangte控制器的getPromotionList函数
        // $fangte = new Fangte;
        // $rebateInfo = $fangte ->getPromotionList();
        $distribution_fee = new Distribution_fee;
        $userDisArr = array();
        $userAccountInfo = Cache::get('userAccountInfo');
        // 先直接从数据库获取
        $distributionFeeList = $distribution_fee->whereOr('parent_id', $userid)->whereOr('grand_id', $userid)->field('dis_fee_id, user_id, parent_id, parent_fee, create_time, goods_id, detail_id, is_success, grand_id, grand_fee')->order('create_time desc')->select();
        $distributionFeeList = collection($distributionFeeList)->toArray();
        // dump($distributionFeeList);die;
        $goods = new Goods;
        $goodsInfo = $goods->alias('a')->join('goods_detail w', 'a.goods_id = w.goods_id', 'LEFT')->field('a.goods_id,a.name as goods_name,w.idx as detail_id,w.detail_name')->where('a.is_delete', 0)->select();
        if ($goodsInfo && count($goodsInfo) > 0) {
            $goodsInfo = collection($goodsInfo)->toArray();
            foreach ($distributionFeeList as $key => $value) {
                foreach ($goodsInfo as $k => $v) {
                    if ($value['goods_id'] == $v['goods_id'] && $value['detail_id'] == $v['detail_id']) {
                        $distributionFeeList[$key]['goods_name'] = $v['goods_name'] . '-' . $v['detail_name'];
                    }
                }
            }
        }
        // 数据重构
        if ($distributionFeeList && count($distributionFeeList) > 0) {
            // 数据处理 构造返回数据
            foreach ($distributionFeeList as $k => $v) {
                // if ($v['is_success'] == 1) {
                //     continue;
                // }
                $temp['create_time_conv'] = $v['create_time'];
                $temp['dis_fee_id'] = $v['dis_fee_id'];
                $temp['buyer_id'] = $v['user_id'];
                if ($v['parent_id'] != $userid) {
                    $temp['parent_id'] = $v['parent_id'];
                }
                if ($v['parent_id'] == $userid) {
                    $temp['dis_fee'] = $v['parent_fee'];
                } else if ($v['grand_id'] == $userid) {
                    $temp['dis_fee'] = $v['grand_fee'];
                }
                $temp['goods_id'] = $v['goods_id'];
                $temp['grand_id'] = $v['grand_id'];
                $temp['goods_name'] = '';
                if (isset($v['goods_name'])) {
                    $temp['goods_name'] = $v['goods_name'];
                }
                foreach ($userAccountInfo as $ke => $va) {
                    if ($va['userID'] == $v['parent_id']) {
                        $temp['parent_nickName'] = $va['userInfo']['nickName'];
                        $temp['parent_avatarUrl'] = $va['userInfo']['avatarUrl'];
                    }
                    if ($va['userID'] == $userid) {
                        $temp['user_nickName'] = $va['userInfo']['nickName'];
                        $temp['user_avatarUrl'] = $va['userInfo']['avatarUrl'];
                    }
                    if ($va['userID'] == $v['user_id']) {
                        $temp['buyer_nickName'] = $va['userInfo']['nickName'];
                        $temp['buyer_avatarUrl'] = $va['userInfo']['avatarUrl'];
                    }
                    if ($va['userID'] == $v['grand_id']) {
                        $temp['grand_nickName'] = $va['userInfo']['nickName'];
                        $temp['grand_avatarUrl'] = $va['userInfo']['avatarUrl'];
                    }
                }
                $userDisArr[] = $temp;
            }
        }
        $this->assign('rebateData', $userDisArr);
        return $this->fetch();
    }

    /**
     * @return 商城统计 - 商品统计页面
     */
    public function goodssales()
    {
        $goods = new Goods();
        $orderdetail = new Order_detail();
        $goodslist = $goods->alias('g')->join('ft_catagory c', 'g.catagory_id = c.catagory_id', 'LEFT')->field('g.goods_id, g.name, g.pic, g.is_active, c.catagory_name')->where('g.is_delete', 0)->select();
        $data = [];
        if ($goodslist) {
            $goodslist = collection($goodslist)->toArray();
            foreach ($goodslist as $ke => $va) {
                $goodslist[$ke]['salesnum'] = $orderdetail->alias('od')->join('order o', 'od.order_id = o.order_id', 'LEFT')->where('od.goods_id', $va['goods_id'])->where('o.status', 'in', [4, 6, 7])->Sum('od.quantity');
            }
            $data = $goodslist;
        }

        $this->assign('goodslist', $data);
        return $this->fetch();
    }

    /**
     * @return 商城统计 - 图表页面
     */
    public function goodssaleschart()
    {
        return $this->fetch();
    }
    /**
     * salesChart 销量图表
     * @return  json salesChart 销量图表
     */
    public function salesChart(Request $request)
    {
        $select = $request->param('select');
        $goods_id = intval($request->param('goods_id'));
        $goods = new Goods;
        // 获取当前商品信息
        $goodsInfo = $goods->alias('g')->join('ft_catagory c', 'g.catagory_id = c.catagory_id', 'LEFT')->field('g.name, c.catagory_name')->find();

        $orderdetail = new Order_detail();
        if ($select == 'day') {
            $today = strtotime('today'); //当天时间戳
            $ary = [];
            for ($i = 14; $i >= 0; $i--) {
                $starttime = $today - $i * 86400;
                $endtime = $starttime + 86400;
                //给要关联的表取别名,并让两个值关联
                $totalOrder = $orderdetail->alias('od')->join('order o', 'od.order_id = o.order_id', 'LEFT')->where('od.goods_id', $goods_id)->where('o.status', 'in', [4, 6, 7])->where('o.create_time', 'between', [$starttime, $endtime])->Sum('od.quantity');
                $ary[] = $totalOrder;
            }
            $data = $ary;
        } else if ($select == 'month') {
            //当月时间戳时间戳和结束时间戳
            // 获取当月天数
            $days = date("t");
            $ary = [];
            $thisMonthEndDay = mktime(23, 59, 59, date('m'), $days) + 86400;
            for ($i = $days; $i >= 1; $i--) {
                $endtime = $thisMonthEndDay - $i * 86400;
                $starttime = $endtime - 86400;
                //给要关联的表取别名,并让两个值关联
                $totalOrder = $orderdetail->alias('od')->join('order o', 'od.order_id = o.order_id', 'LEFT')->where('od.goods_id', $goods_id)->where('o.status', 'in', [4, 6, 7])->where('o.create_time', 'between', [$starttime, $endtime])->Sum('od.quantity');
                $ary[] = $totalOrder;
            }
            $data = $ary;
        } else if ($select == 'season') {
            $ary = [];
            $starttime1 = strtotime(date('Y-01-01 00:00:00'));
            $endtime1 = strtotime(date("Y-03-31 23:59:59"));
            //给要关联的表取别名,并让两个值关联
            $totalOrder = $orderdetail->alias('od')->join('order o', 'od.order_id = o.order_id', 'LEFT')->where('od.goods_id', $goods_id)->where('o.status', 'in', [4, 6, 7])->where('o.create_time', 'between', [$starttime1, $endtime1])->Sum('od.quantity');
            $ary[] = $totalOrder;
            $starttime2 = strtotime(date('Y-04-01 00:00:00'));
            $endtime2 = strtotime(date("Y-06-30 23:59:59"));
            //给要关联的表取别名,并让两个值关联
            $totalOrder = $orderdetail->alias('od')->join('order o', 'od.order_id = o.order_id', 'LEFT')->where('od.goods_id', $goods_id)->where('o.status', 'in', [4, 6, 7])->where('o.create_time', 'between', [$starttime2, $endtime2])->Sum('od.quantity');
            $ary[] = $totalOrder;
            $starttime3 = strtotime(date('Y-07-01 00:00:00'));
            $endtime3 = strtotime(date("Y-09-30 23:59:59"));
            //给要关联的表取别名,并让两个值关联
            $totalOrder = $orderdetail->alias('od')->join('order o', 'od.order_id = o.order_id', 'LEFT')->where('od.goods_id', $goods_id)->where('o.status', 'in', [4, 6, 7])->where('o.create_time', 'between', [$starttime3, $endtime3])->Sum('od.quantity');
            $ary[] = $totalOrder;
            $starttime4 = strtotime(date('Y-10-01 00:00:00'));
            $endtime4 = strtotime(date("Y-12-31 23:59:59"));
            //给要关联的表取别名,并让两个值关联
            $totalOrder = $orderdetail->alias('od')->join('order o', 'od.order_id = o.order_id', 'LEFT')->where('od.goods_id', $goods_id)->where('o.status', 'in', [4, 6, 7])->where('o.create_time', 'between', [$starttime4, $endtime4])->Sum('od.quantity');
            $ary[] = $totalOrder;
            $data = $ary;
        } else if ($select == 'year') {
            $ary = [];
            // 十二个月
            for ($i = 1; $i <= 12; $i++) {
                $starttime = mktime(0, 0, 0, $i, 1);
                $endtime = $starttime + 86400 * date('t');
                $totalOrder = $orderdetail->alias('od')->join('order o', 'od.order_id = o.order_id', 'LEFT')->where('od.goods_id', $goods_id)->where('o.status', 'in', [4, 6, 7])->where('o.create_time', 'between', [$starttime, $endtime])->Sum('od.quantity');
                $ary[] = $totalOrder;
            }
            $data = $ary;
        }
        $data['data'] = $data;
        $data['goodsInfo'] = $goodsInfo;
        return json($data);
    }

    /**
     * @return 管理员管理界面
     */
    public function memberadmin()
    {
        $admin = new admin();
        $num = $admin->field('admin_id')->where('is_delete', 0)->count();
        $this->assign('num', $num);
        return $this->fetch();
    }
    /**
     * adminList 管理员列表
     * @return  json List 列表
     */
    public function adminList()
    {
        $admin = new admin();
        $res = $admin->field('user_id,account_type,create_time,is_active,username,tel_num')->where('account_type', '<>', 3)->where('is_delete', 0)->select();
        return json($res);
    }

    /**
     * changeAdmin 启用
     * @return  json changeAdmin 启用结果
     */
    public function changeAdmin(Request $request)
    {
        $admin = new admin();
        $user_id = intval($request->param('id'));
        $re["is_active"] = 1;
        $re["last_continue_time"] = time();
        $update = $admin->where('user_id', $user_id)->update($re);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "已启用";
        } else {
            $res['code'] = "300";
            $res['msg'] = "启用失败";
        }
        return json($res);
    }

    /**
     * stopAdmin 停用
     * @return  json stopAdmin 停用结果
     */
    public function stopAdmin(Request $request)
    {
        $admin = new admin();
        $user_id = intval($request->param('id'));
        $re["is_active"] = 0;
        $re["last_stop_time"] = time();
        $update = $admin->where('user_id', $user_id)->update($re);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "已停用";
        } else {
            $res['code'] = "300";
            $res['msg'] = "停用失败";
        }
        return json($res);
    }

    /**
     * delAdmin 删除管理员
     * @return  json delAdmin 删除结果
     */
    public function delAdmin(Request $request)
    {
        $admin = new admin();
        $user_id = intval($request->param('id'));
        $re["delete_time"] = time();
        $re["is_delete"] = 1;
        $update = $admin->where('user_id', $user_id)->update($re);
        if ($update) {
            $accountType = $admin->where('user_id', $user_id)->value('account_type');
            // 票卷核销员
            if ($accountType == 1) {
                // 取用户信息缓存
                $userAccountInfo = Cache::get('userAccountInfo');
                foreach ($userAccountInfo as &$user) {
                    if ($user['userID'] == $user_id) {
                        $user['isAdmin'] = false;
                        break 1;
                    }
                }
                Cache::set('userAccountInfo', $userAccountInfo);
            }
            $res['code'] = "200";
            $res["msg"] = "删除成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "删除失败";
        }
        return json($res);
    }

    /**
     * @return 管理员添加界面
     */
    public function memberadminadd()
    {
        $userinfo = new Userinfo();
        $data = $userinfo->field('user_id,nickName,name,tel_num')->select();
        $this->assign('userinfo', $data);
        return $this->fetch();
    }

    /**
     * addAdmin 添加管理员
     * @return  json addAdmin 添加结果
     */
    public function addAdmin(Request $request)
    {
        $admin = new admin();
        $userinfo = new Userinfo();
        $user_id = intval($request->param('userid'));
        $exist = $admin->where('user_id', $user_id)->where('is_delete', 0)->find();
        // dump($exist);die;
        if (!$exist) {
            $addadmin['user_id'] = $user_id;
            $addadmin['username'] = $userinfo->where('user_id', $user_id)->value('name');
            $addadmin['tel_num'] = $userinfo->where('user_id', $user_id)->value('tel_num');
            if ($request->param('password') != '') {
                $addadmin['password'] = md5($request->param('password'));
            }
            $addadmin['account_type'] = intval($request->param('admintype'));
            $addadmin['is_active'] = intval($request->param('adminactive'));
            $addadmin['create_time'] = time();
            $insert = $admin->insert($addadmin);
            if ($insert) {
                $res['code'] = "200";
                $res["msg"] = "添加成功";
                // 票卷核销员
                if ($addadmin['account_type'] == 1) {
                    // 取用户信息缓存
                    $userAccountInfo = Cache::get('userAccountInfo');
                    foreach ($userAccountInfo as &$user) {
                        if ($user['userID'] == $user_id) {
                            $user['isAdmin'] = true;
                            break 1;
                        }
                    }
                    Cache::set('userAccountInfo', $userAccountInfo);
                }
            } else {
                $res['code'] = "300";
                $res["msg"] = "添加失败";
            }
        } else {
            $res['code'] = "300";
            $res["msg"] = "添加失败，已经存在此管理员账号！";
        }
        return json($res);
    }

    /**
     * selectAdmin 筛选管理员
     * @return  json selectAdmin 筛选结果
     */
    public function selectAdmin(Request $request)
    {
        $typename = $request->param('nodeName');
        $admin = new admin();
        if ($typename == "票券核销员") {
            $account_type = 1;
        } else if ($typename == "系统管理员") {
            $account_type = 2;
        }
        $res = $admin->field('user_id,account_type,create_time,is_active,username,tel_num')->where('account_type', $account_type)->where('is_delete', 0)->select();
        return json($res);
    }

    /**
     * @return 权限管理页面
     */
    public function memberpower()
    {
        $admin = new admin();
        $num = $admin->where('account_type', 2)->count();
        $this->assign('num', $num);
        return $this->fetch();
    }

    /**
     * powerList 权限页面
     * @return  json powerList 返回值
     */
    public function powerList()
    {
        $admin = new admin();
        $res = $admin->field('user_id,account_type,tel_num,create_time,is_active,username')->where('account_type', 2)->where('is_delete', 0)->select();
        return json($res);
    }

    /**
     * power 原先的权限
     * @return  json power 返回值
     */
    public function power(Request $request)
    {
        // 获取用户id
        $user_id = $request->param('id');
        // 根据用户id确定原先的权限
        $menu = new Menu();
        $menulist = $menu->field('menu_id,parent_id,menu_name')->where('is_admin', 0)->select();
        $menuary = array();
        foreach ($menulist as $key => $value) {
            $ary = array(
                'id' => $value['menu_id'],
                'pId' => $value['parent_id'],
                'name' => $value['menu_name'],
                'open' => true,
                'checked' => false,
            );
            // 勾选已有权限
            // if ($value['name'],$uid['uid'],$relation='or') {
            //     $ary['checked']=true;
            // }
            array_push($menuary, $ary);
        }
        return json($menuary);
    }

    /**
     * 原先的权限信息
     * @param  Request $request 参数
     * @return ary              返回值
     */
    public function prePower(Request $request)
    {
        $user_id = $request->param('id');
        // menu表数据
        $menu = new Menu;
        $menuList = $menu->field('menu_id,parent_id,menu_name')->where('is_admin', 0)->select();
        $menuList = collection($menuList)->toArray();
        // power表数据
        $power = new Power;
        $powerList = $power->field('power_id,user_id,menu_id')->where('user_id', $user_id)->select();
        $powerList = collection($powerList)->toArray();
        // 构造ztree数据
        $menuAry = array();
        foreach ($menuList as $key => $value) {
            $ary['id'] = $value['menu_id'];
            $ary['pId'] = $value['parent_id'];
            $ary['name'] = $value['menu_name'];
            $ary['open'] = true;
            $ary['checked'] = false;
            foreach ($powerList as $k => $v) {
                if ($value['menu_id'] == $v['menu_id']) {
                    $ary['checked'] = true;
                    break 1;
                }
            }
            $menuAry[] = $ary;
        }
        return json($menuAry);
    }

    /**
     * selectPower 权限分配
     * @return  json selectPower 返回值
     */
    public function selectPower(Request $request)
    {
        $idx = intval($request->param('id'));
        $menuid = $request->param('menuid');
        // 对字符串处理转为数组
        $menuary = explode(',', $menuid, -1);
        // dump($menuary);die;
        $power = new Power();
        // 先删除所有权限
        $res = $power->where('user_id', $idx)->delete();
        $update = array();
        for ($i = 0; $i < count($menuary); $i++) {
            $ary = array();
            $ary['user_id'] = $idx;
            $ary['menu_id'] = $menuary[$i];
            $update[] = $ary;
        }
        // 再更新权限信息
        $result = $power->saveAll($update);
        if ($result) {
            $powerInfo = array(
                'errno' => 200,
                'msg' => '保存成功！',
            );
        } else {
            $powerInfo = array(
                'errno' => 300,
                'msg' => '保存失败！',
            );
        }
        return json($powerInfo);
    }

    /**
     * passwordUpdate 修改管理员密码
     * @return  json passwordUpdate 结果
     */
    public function passwordUpdate(Request $request)
    {
        $user_id = intval($request->param('user_id'));
        $password = md5(intval($request->param('password')));
        $re['password'] = md5(intval($request->param('password2')));
        $admin = new Admin();
        $pwd = $admin->where('user_id', $user_id)->value('password');
        if ($password != $pwd) {
            $res['code'] = "300";
            $res['msg'] = "初始密码错误！";
        } else {
            $result = $admin->where('user_id', $user_id)->update($re);
            if ($request) {
                $res['code'] = "200";
                $res['msg'] = "修改成功！";
            } else {
                $res['code'] = "300";
                $res['msg'] = "修改失败！";
            }
        }
        return json($res);
    }

    /**
     * @return 商城统计-用户分销统计界面
     */
    public function distributiondata()
    {
        $userinfo = new Userinfo();
        //给要关联的表取别名,并让两个值关联
        $data = $userinfo->alias('a')->join('distribution_fee w', 'a.user_id = w.user_id', 'left')->field('a.user_id,a.nickName,a.tel_num')->select();
        $this->assign('userinfo', $data);
        $distribution_fee = new Distribution_fee();
        $num = $distribution_fee->field('dis_fee_id')->count();
        $this->assign('num', $num);
        return $this->fetch();
    }

    /**
     * getDistribution 获取用户分销统计数据
     * @return  json getDistribution 结果
     */
    public function getDistributionData()
    {
        $distribution_fee = new Distribution_fee();
        $data = $distribution_fee->field('user_id,user_nickname,user_pic,goods_id,goods_name,create_time,parent_id,parent_percent,parent_fee,grand_id,grand_percent,grand_fee')->select();
        // $data = array();
        // foreach ($data as $k => $v) {
        //     if ($v['user_id'] == $userID || $v['grand_id'] == $userID ||) {
        //         $da []= $v;
        //     }
        // }
        return json($data);
    }

    /**
     * @return 商城统计-分销统计图表界面
     */
    public function distributionchart()
    {
        return $this->fetch();
    }

    /**
     * chartData 获取图表数据
     * @return  json chartData 结果
     */
    public function chartData(Request $request)
    {
        $select = $request->param('select');
        $order = new Order();
        if ($select == 'day') {
            $date = strtotime(date("Y-m-d 00:00:00")); //当天时间戳
            $ary = array();
            for ($i = 14; $i >= 0; $i--) {
                $starttime = $date - $i * 86400;
                $endtime = ($date + 86400) - $i * 86400;
                $totalDisFee = $order->where('create_time', 'between', [$starttime, $endtime])->where('is_delete', 0)->Sum('totalDisFee');
                array_push($ary, $totalDisFee);
            }
            $data = $ary;
        } else if ($select == 'month') {
            // $date = strtotime(date("Y-m-d 00:00:00")); //当天时间戳
            //当月时间戳时间戳和结束时间戳
            // $beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
            $endThismonth = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
            // 获取当月天数
            $days = date("t");
            $ary = array();
            for ($i = $days; $i >= 1; $i--) {
                $starttime = $endThismonth - $i * 86400;
                $endtime = ($endThismonth + 86400) - $i * 86400;
                $totalDisFee = $order->where('create_time', 'between', [$starttime, $endtime])->where('is_delete', 0)->Sum('totalDisFee');
                array_push($ary, $totalDisFee);
            }
            $data = $ary;
        } else if ($select == 'season') {
            $ary = array();
            $starttime1 = strtotime(date('Y-01-01 00:00:00'));
            $endtime1 = strtotime(date("Y-03-31 23:59:59"));
            $totalDisFee1 = $order->where('create_time', 'between', [$starttime1, $endtime1])->where('is_delete', 0)->Sum('totalDisFee');
            array_push($ary, $totalDisFee1);
            $starttime2 = strtotime(date('Y-04-01 00:00:00'));
            $endtime2 = strtotime(date("Y-06-30 23:59:59"));
            $totalDisFee2 = $order->where('create_time', 'between', [$starttime2, $endtime2])->where('is_delete', 0)->Sum('totalDisFee');
            array_push($ary, $totalDisFee2);
            $starttime3 = strtotime(date('Y-07-01 00:00:00'));
            $endtime3 = strtotime(date("Y-09-30 23:59:59"));
            $totalDisFee3 = $order->where('create_time', 'between', [$starttime3, $endtime3])->where('is_delete', 0)->Sum('totalDisFee');
            array_push($ary, $totalDisFee3);
            $starttime4 = strtotime(date('Y-10-01 00:00:00'));
            $endtime4 = strtotime(date("Y-12-31 23:59:59"));
            $totalDisFee4 = $order->where('create_time', 'between', [$starttime4, $endtime4])->where('is_delete', 0)->Sum('totalDisFee');
            array_push($ary, $totalDisFee4);
            $data = $ary;
        } else if ($select == 'year') {
            $ary = array();
            $nowtime = time();
            $nowyear = strtotime(date('Y-01-01')); //当前年份
            for ($i = -4; $i <= -1; $i++) {
                $starttime = strtotime("$i year", $nowyear);
                $count = $i + 1;
                $endtime = strtotime("$count year", $nowyear);
                $totalDisFee = $order->where('create_time', 'between', [$starttime, $endtime])->where('is_delete', 0)->Sum('totalDisFee');
                array_push($ary, $totalDisFee);
            }
            $totalDisFee2 = $order->where('create_time', 'between', [$nowyear, $nowtime])->where('is_delete', 0)->Sum('totalDisFee');
            array_push($ary, $totalDisFee2);
            $data = $ary;
        }
        return json($data);
    }
    /**
     * @return 商城统计-用户统计界面
     */
    public function userstatistics()
    {
        return $this->fetch();
    }
    /**
     * userCount 获取用户新增数据
     * @return  json userCount 结果
     */
    public function userCount(Request $request)
    {
        $userinfo = new Userinfo();
        $select = $request->param('select');
        if ($select == 'day') {
            $date = strtotime(date("Y-m-d 00:00:00")); //当天时间戳
            $ary = array();
            for ($i = 14; $i >= 0; $i--) {
                $starttime = $date - $i * 86400;
                $endtime = ($date + 86400) - $i * 86400;
                $total = $userinfo->field('idx')->where('create_time', 'between', [$starttime, $endtime])->count();
                array_push($ary, $total);
            }
            $data = $ary;
        } else if ($select == 'month') {
            //当月时间戳时间戳和结束时间戳
            // $beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
            $endThismonth = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
            // 获取当月天数
            $days = date("t");
            $ary = array();
            for ($i = $days; $i >= 1; $i--) {
                $starttime = $endThismonth - $i * 86400;
                $endtime = ($endThismonth + 86400) - $i * 86400;
                $total = $userinfo->field('idx')->where('create_time', 'between', [$starttime, $endtime])->count();
                array_push($ary, $total);
            }
            $data = $ary;
        }
        if ($select == 'season') {
            $ary = array();
            $starttime1 = strtotime(date('Y-01-01 00:00:00'));
            $endtime1 = strtotime(date("Y-03-31 23:59:59"));
            $total1 = $userinfo->field('idx')->where('create_time', 'between', [$starttime1, $endtime1])->count();
            array_push($ary, $total1);
            $starttime2 = strtotime(date('Y-04-01 00:00:00'));
            $endtime2 = strtotime(date("Y-06-30 23:59:59"));
            $total2 = $userinfo->field('idx')->where('create_time', 'between', [$starttime2, $endtime2])->count();
            array_push($ary, $total2);
            $starttime3 = strtotime(date('Y-07-01 00:00:00'));
            $endtime3 = strtotime(date("Y-09-30 23:59:59"));
            $total3 = $userinfo->field('idx')->where('create_time', 'between', [$starttime3, $endtime3])->count();
            array_push($ary, $total3);
            $starttime4 = strtotime(date('Y-10-01 00:00:00'));
            $endtime4 = strtotime(date("Y-12-31 23:59:59"));
            $total4 = $userinfo->field('idx')->where('create_time', 'between', [$starttime4, $endtime4])->count();
            array_push($ary, $total4);
            $data = $ary;
        } else if ($select == 'year') {
            $ary = array();
            $nowtime = time();
            $nowyear = strtotime(date('Y-01-01')); //当前年份
            for ($i = -4; $i <= -1; $i++) {
                $starttime = strtotime("$i year", $nowyear);
                $count = $i + 1;
                $endtime = strtotime("$count year", $nowyear);
                $total = $userinfo->field('idx')->where('create_time', 'between', [$starttime, $endtime])->count();
                array_push($ary, $total);
            }
            $total2 = $userinfo->field('idx')->where('create_time', 'between', [$nowyear, $nowtime])->count();
            array_push($ary, $total2);
            $data = $ary;
        }
        return json($data);
    }

    /**
     * @return 商城统计-邀请码统计界面
     */
    public function invitechart()
    {
        return $this->fetch();
    }
    /**
     * inviteData 获取用户新增数据
     * @return  json inviteData 结果
     */
    public function inviteData(Request $request)
    {
        $invite_code = new Invite_code();
        $select = $request->param('select');
        $userinfo = new Userinfo();
        $today = strtotime('today') + 86400 - 1;
        if ($select == 'day') {
            // 获取过去15天的日期数据 此数据可用作前端时间显示
            $dateArr = array();
            for ($j = 0; $j < 15; $j++) {
                $dateArr[] = (date('Y-m-d', $today - $j * 86400));
            }
            // 用sort  与下面对应
            // 不能使用krsort krsort不会重写键 无效
            sort($dateArr);
            // dump($dateArr); die;
            // 先获取当前邀请码总数
            $totalCount = $invite_code->field('code_id, invite_code')->where('is_active', 'in', [1, 2])->select();
            // 注意数据为空时的返回
            if (!$totalCount) {
                return;
            }
            // 新建默认tempArr数组，数据全部初始化为0 有多少个code_id就有多少条Array
            $defaultArray = array();
            for ($j = 0; $j < count($totalCount); $j++) {
                $tempArr = [];
                for ($m = 0; $m < 15; $m++) {
                    $tempArr[$m] = 0;
                }
                $defaultArray[$j] = $tempArr;
            }
            // 数据处理、判断和填充
            for ($i = 14; $i >= 0; $i--) {
                // 构造当前时间去查询数据 直接获取上面构造时间即可
                $startTime = strtotime($dateArr[$i]);
                // 生成查询结束时间
                $endTime = $startTime + 86400;
                // 数据查询
                $total = $userinfo->field('invite_code_id')->where('create_time', 'between', [$startTime, $endTime])->select();
                // 对查询数据进行判断
                if ($total && count($total) > 0) {
                    // 如果当前有数据就去判断并填充
                    foreach ($total as $k => $v) {
                        foreach ($totalCount as $ke => $va) {
                            if ($va['code_id'] == $v['invite_code_id']) {
                                $defaultArray[$ke][$i] += 1;
                                // break 1;
                            }
                        }
                    }
                }
            }
            // 新建数据返回数组
            $data = array();
            // 处理默认数据，构造返回数组
            foreach ($totalCount as $k => $v) {
                $temp['name'] = $v['invite_code'];
                $temp['data'] = $defaultArray[$k];
                $data[] = $temp;
            }
            // dump($data);die;
        } else if ($select == 'month') {
            //当月开始时间戳
            $beginThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
            //当月结束时间戳
            $endThismonth = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
            // 获取当月天数
            $days = date("t");
            // 先获取当前邀请码总数
            $totalCount = $invite_code->field('code_id, invite_code')->where('is_active', 'in', [1, 2])->select();
            // 注意数据为空时的返回
            if (!$totalCount) {
                return;
            }
            // 新建默认tempArr数组，数据全部初始化为0 有多少个code_id就有多少条Array
            $defaultArray = array();
            for ($j = 0; $j < count($totalCount); $j++) {
                $tempArr = [];
                for ($m = 0; $m < $days; $m++) {
                    $tempArr[$m] = 0;
                }
                $defaultArray[$j] = $tempArr;
            }
            // dump($defaultArray);die;
            // // 数据处理、判断和填充
            for ($i = $days; $i >= 1; $i--) {
                // 起始时间
                $startTime = $endThismonth - $i * 86400;
                // 结束时间
                $endTime = ($endThismonth + 86400) - $i * 86400;
                // 数据查询
                $total = $userinfo->field('invite_code_id')->where('create_time', 'between', [$startTime, $endTime])->select();
                // 对查询数据进行判断
                if ($totalCount && count($totalCount) > 0) {
                    // 如果当前有数据就去判断并填充
                    foreach ($total as $k => $v) {
                        foreach ($totalCount as $ke => $va) {
                            if ($va['code_id'] == $v['invite_code_id']) {
                                $defaultArray[$ke][$i] += 1;
                            }
                        }
                    }
                }
            }
            // 新建数据返回数组
            $data = array();
            // 处理默认数据，构造返回数组
            foreach ($totalCount as $k => $v) {
                $temp['name'] = $v['invite_code'];
                $temp['data'] = $defaultArray[$k];
                $data[] = $temp;
            }
        }
        if ($select == 'season') {
            $seasonDate = array(
                0 => ['starttime' => strtotime(date('Y-01-01 00:00:00')), 'endtime' => strtotime(date("Y-03-31 23:59:59"))],
                1 => ['starttime' => strtotime(date('Y-04-01 00:00:00')), 'endtime' => strtotime(date('Y-06-30 23:59:59'))],
                2 => ['starttime' => strtotime(date('Y-07-01 00:00:00')), 'endtime' => strtotime(date("Y-09-30 23:59:59"))],
                3 => ['starttime' => strtotime(date('Y-10-01 00:00:00')), 'endtime' => strtotime(date("Y-12-31 23:59:59"))]
            );
            // 先获取当前邀请码总数
            $totalCount = $invite_code->field('code_id, invite_code')->where('is_active', 'in', [1, 2])->select();
            // 注意数据为空时的返回
            if (!$totalCount) {
                return;
            }
            // 新建默认tempArr数组，数据全部初始化为0 有多少个code_id就有多少条Array
            $defaultArray = array();
            for ($j = 0; $j < count($totalCount); $j++) {
                $tempArr = [];
                for ($m = 0; $m < 4; $m++) {
                    $tempArr[$m] = 0;
                }
                $defaultArray[$j] = $tempArr;
            }
            // // 数据处理、判断和填充
            for ($i = 4; $i >= 1; $i--) {
                // 数据查询
                $total = $userinfo->field('invite_code_id')->where('create_time', 'between', [$seasonDate[$i - 1]['starttime'], $seasonDate[$i - 1]['endtime']])->select();
                // 对查询数据进行判断
                if ($totalCount && count($totalCount) > 0) {
                    // 如果当前有数据就去判断并填充
                    foreach ($total as $k => $v) {
                        foreach ($totalCount as $ke => $va) {
                            if ($va['code_id'] == $v['invite_code_id']) {
                                $defaultArray[$ke][$i - 1] += 1;
                            }
                        }
                    }
                }
            }
            // 新建数据返回数组
            $data = array();
            // 处理默认数据，构造返回数组
            foreach ($totalCount as $k => $v) {
                $temp['name'] = $v['invite_code'];
                $temp['data'] = $defaultArray[$k];
                $data[] = $temp;
            }
        } else if ($select == 'year') {
            $nowyear = date('Y', time()); //当前年份
            // 先获取当前邀请码总数
            $totalCount = $invite_code->field('code_id, invite_code')->where('is_active', 'in', [1, 2])->select();
            // 注意数据为空时的返回
            if (!$totalCount) {
                return;
            }
            // 新建默认tempArr数组，数据全部初始化为0 有多少个code_id就有多少条Array
            $defaultArray = array();
            for ($j = 0; $j < count($totalCount); $j++) {
                $tempArr = [];
                for ($m = 0; $m < 4; $m++) {
                    $tempArr[$m] = 0;
                }
                $defaultArray[$j] = $tempArr;
            }
            // // 数据处理、判断和填充
            for ($i = 3; $i >= 0; $i--) {
                $startTime = mktime(0, 0, 0, 1, 1, $nowyear);
                $endTime = mktime(23, 59, 59, 12, 31, $nowyear);
                // 数据查询
                $total = $userinfo->field('invite_code_id')->where('create_time', 'between', [$startTime, $endTime])->select();
                // 对查询数据进行判断
                if ($total && count($total) > 0) {
                    // 如果当前有数据就去判断并填充
                    foreach ($total as $k => $v) {
                        foreach ($totalCount as $ke => $va) {
                            if ($va['code_id'] == $v['invite_code_id']) {
                                $defaultArray[$ke][$i] += 1;
                            }
                        }
                    }
                }
                $nowyear--;
            }
            // 新建数据返回数组
            $data = array();
            // 处理默认数据，构造返回数组
            foreach ($totalCount as $k => $v) {
                $temp['name'] = $v['invite_code'];
                $temp['data'] = $defaultArray[$k];
                $data[] = $temp;
            }
        }
        return json($data);
    }
    /**
     * @return banner 界面
     */
    public function bannerlist()
    {
        $banner = new Banner();
        $data = $banner->where('is_delete', 0)->select();
        if (!$data) {
            $data["banner_id"] = 0;
        }
        $this->assign('banner', $data);
        return $this->fetch();
    }

    /**
     * @return 添加banner 界面
     */
    public function banneradd()
    {
        $banner = new Banner();
        $goods = new Goods();
        $goods = $goods->select();
        $data = $banner->where('banner_id', 4)->find();
        $this->assign('goods', $goods);
        $this->assign('banner', $data['banner_src']);
        return $this->fetch();
    }

    /**
     * 获取当前banner id
     * @return json banner 启用结果
     */
    public function bannerstart(Request $request)
    {
        $banner = new Banner();
        if ($request->isPost()) {
            $id = $request->param('id');
            $update = $banner->where('banner_id', $id)->update(['is_active' => '1']);
            if ($update) {
                $res['code'] = "200";
                $res["msg"] = "启用成功";
            } else {
                $res['code'] = "300";
                $res["msg"] = "启用失败";
            }
            return json($res);
        }
    }

    /**
     * 获取当前banner id
     * @return json banner 停用结果
     */
    public function bannerstop(Request $request)
    {
        $banner = new Banner();
        $id = $request->param('id');
        $update = $banner->where('banner_id', $id)->update(['is_active' => '0']);
        if ($update) {
            $res['code'] = "200";
            $res["msg"] = "停用成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "停用失败";
        }
        return json($res);
    }

    /**
     * 获取当前banner id
     * @return json 删除banner结果
     */
    public function bannerdel(Request $request)
    {
        $banner = new Banner();
        $id = $request->param('id');
        $update = $banner->where('banner_id', $id)->update(['is_delete' => '1']);
        if ($update) {
            $res['code'] = "200";
            $res["msg"] = "删除成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "删除失败";
        }
        return json($res);
    }
    /**
     * 获取bannner图片
     * @return json 图片地址
     */
    public function getBanner(Request $request)
    {
        $banner = Cache::get('banner');
        if (!$banner) {
            $bannerInfo = new Banner;
            $bannerList = $bannerInfo->find();
            $banner = $bannerList['banner'];
            Cache::set('banner', $banner, 0);
        }
        //获取bamner
        $res['banner'] = $banner;
        return json_encode($res);
    }

    /**
     * banner 修改
     * @return json 修改结果
     */
    public function miniBannerChange(Request $request)
    {
        $banner = new Banner();
        $add['goods_id'] = intval($request->param('banner_link'));
        $add['is_active'] = intval($request->param('banner_active'));
        $add['orderby'] = intval($request->param('banner_order'));
        $add['type'] = intval($request->param('banner_catagory'));
        if (!in_array($add['type'], [1, 2, 3, 4])) $add['type'] = 0;
        $file = request()->file('file');
        // $add['create_time'] = time();
        // 移动到框架应用根目录/public/banner/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'banner');
        if ($info) {
            $str = $info->getSaveName();
            $bannersrc = "/public/banner/" . $str;
            $add['banner_src'] = $bannersrc;
            $banner->insert($add);
            $res["code"] = 200;
            $res["src"] = $bannersrc;
            $res["msg"] = "success";
        } else {
            $res["code"] = 400;
            $res["msg"] = "error";
        }
        return json_encode($res);
    }

    /**
     * @return 显示邀请码界面
     */
    public function invitelist()
    {
        $invite_code = new Invite_code();
        // 将缓存中的邀请码信息写入数据库
        $inviteCodeArr = Cache::get('inviteCodeArr');
        $updateArr = [];
        $curTime = time();
        if ($inviteCodeArr) {
            foreach ($inviteCodeArr as $k => $v) {
                $temp['code_id'] = $v['code_id'];
                $temp['code_active_num'] = $v['code_active_num'];
                $temp['is_active'] = $v['code_active_num'] == $v['code_total_num'] ? 0 : 1;
                $updateArr[] = $temp;
            }
        }
        $invite_code = new Invite_code;
        $update = $invite_code->isUpdate()->saveAll($updateArr);
        if ($update) {
            Cache::rm('inviteCodeArr');
        }
        $data = $invite_code->order("create_time desc")->select();
        $this->assign('invite', $data);
        return $this->fetch();
    }
    /**
     * 获取当前邀请码id
     * @return json 启用邀请码结果
     */
    public function inviteuse(Request $request)
    {
        $invite_code = new Invite_code();
        if ($request->isPost()) {
            $id = $request->param('id');
            $update = $invite_code->where('code_id', $id)->update(['is_active' => '1']);
            if ($update) {
                $res['code'] = "200";
                $res["msg"] = "启用成功";
            } else {
                $res['code'] = "300";
                $res["msg"] = "启用失败";
            }
            return json($res);
        }
    }
    /**
     * 获取当前邀请码id
     * @return json 停用邀请码结果
     */
    public function invitestop(Request $request)
    {
        $invite_code = new Invite_code();
        $id = $request->param('id');
        $update = $invite_code->where('code_id', $id)->update(['is_active' => '0']);
        if ($update) {
            $res['code'] = "200";
            $res["msg"] = "停用成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "停用失败";
        }
        return json($res);
    }
    /**
     * @return 添加邀请码界面
     */
    public function inviteadd()
    {
        return $this->fetch();
    }
    /**
     * 获取邀请码长度
     * @return json 邀请码
     */
    public function makeinvite(Request $request)
    {
        if ($request->isAjax()) {
            $num = $request->param('num');
            $arr = array_flip(range('a', 'z'));
            $codeArr = array_rand($arr, $num);
            $inviteCode = join('', $codeArr);
            $this->success($inviteCode, '', true);
        } else {
            $this->error('请重新刷新网络');
        }
    }
    /**
     * 获取邀请码信息
     * @return json 添加邀请码结果
     */
    public function forminvite(Request $request)
    {
        $invite_code = new Invite_code();
        $catagory = new Catagory();
        $invite['code_total_num'] = intval($request->param('num'));
        $invite['invite_code'] = htmlspecialchars($request->param('invitecode'));
        $invite["create_time"] = time();
        $re = $catagory->find($invite);
        if ($re) {
            $res['code'] = "300";
            $res["msg"] = "该验证码重复请重新输入";
            echo json_encode($res);
            die;
        }
        $insert = $invite_code->insert($invite);
        if ($insert) {
            $res['code'] = "200";
            $res["msg"] = "新增成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "新增失败";
        }
        echo json_encode($res);
    }
    /**
     * @return 小程序-用户协议界面
     */
    public function memberagreement()
    {
        $clause = new Clause();
        $info = $clause->where('idx', 1)->find();
        $this->assign('info', $info);
        return $this->fetch();
    }
    /**
     * 添加协议信息
     * @return json 结果
     */
    public function addAgreement(Request $request)
    {
        $clause = new Clause();
        $content['idx'] = 1;
        $content['clause'] = htmlspecialchars($request->param('content'));
        $insert = $clause->update($content);
        if ($insert) {
            $res['code'] = "200";
            $res["msg"] = "修改成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "修改失败";
        }
        return json($res);
    }
    /**
     * @return 分类界面
     */
    public function catagorylist()
    {
        $catagory = new Catagory();
        $data = $catagory->order('create_time desc')->select();
        $this->assign('catagory', $data);
        return $this->fetch();
    }
    /**
     * @return 添加分类页面
     */
    public function catagoryadd()
    {
        $catagory = new Catagory();
        $catagoryadd = $catagory->where('father_catagory_id', 0)->select();
        $this->assign('catagoryadd', $catagoryadd);
        return $this->fetch();
    }
    /**
     * 获取分类信息
     * @return json 新增分类结果
     */
    public function catagoryinsert(Request $request)
    {
        $catagory = new Catagory();
        $catagoryin['catagory_name'] = htmlspecialchars($request->param('name'));
        $catagoryin['father_catagory_id'] = $request->param('father_name');
        $catagoryin['orderby'] = intval($request->param('order'));
        $catagoryin['create_time'] = time();
        $insert = $catagory->insert($catagoryin);
        if ($insert) {
            $res['code'] = "200";
            $res["msg"] = "增加成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "增加失败";
        }
        return json($res);
    }
    /**
     * 获取当前分类id
     * @return json 分类激活结果
     */
    public function catagorystart(Request $request)
    {
        $catagory = new Catagory();
        if ($request->isPost()) {
            $id = $request->param('id');
            $update = $catagory->where('catagory_id', $id)->update(['is_delete' => '1']);
            if ($update) {
                $res['code'] = "200";
                $res["msg"] = "启用成功";
            } else {
                $res['code'] = "300";
                $res["msg"] = "启用失败";
            }
            return json($res);
        }
    }
    /**
     * 获取当前分类id
     * @return json 分类停用结果
     */
    public function catagorystop(Request $request)
    {
        $catagory = new Catagory();
        $id = $request->param('id');
        $update = $catagory->where('catagory_id', $id)->update(['is_delete' => '0']);
        if ($update) {
            $res['code'] = "200";
            $res["msg"] = "停用成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "停用失败";
        }
        return json($res);
    }
    /**
     * 当前分类id
     * @return json 删除分类结果
     */
    public function catagorydel(Request $request)
    {
        $catagory = new Catagory();
        $id = $request->param('id');
        $update = $catagory->where('catagory_id', $id)->delete();
        if ($update) {
            $res['code'] = "200";
            $res["msg"] = "删除成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "删除失败";
        }
        return json($res);
    }
    /**
     * @return 促销活动界面
     */
    public function promotion()
    {
        $promotion = new Promotion();
        $data = $promotion->field('promotion_id, count, name, start_time, end_time, is_active')->order('start_time desc')->select();
        $this->assign('promotion', $data);
        return $this->fetch();
    }
    /**
     * @return 添加促销活动界面
     */
    public function promotionadd()
    {
        return $this->fetch();
    }
    /**
     * 获取当前促销活动id
     * @return json 促销活动上线结果
     */
    public function changepromotion(Request $request)
    {
        $promotion = new Promotion();
        $re["is_active"] = 1;
        $id = $request->param('id');
        $result = $promotion->where('promotion_id', $id)->update($re);
        if ($result) {
            $res['code'] = "200";
            $res['msg'] = "活动上线成功";
        } else {
            $res['code'] = "300";
            $res['msg'] = "活动上线失败";
        }
        return json($res);
    }
    /**
     * 获取当前促销活动id
     * @return json 停止促销活动结果
     */
    public function promotionstop(Request $request)
    {
        $promotion = new Promotion();
        $id = $request->param('id');
        $re["is_active"] = 0;
        $update = $promotion->where('promotion_id', $id)->update($re);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "活动已停止";
        } else {
            $res['code'] = "300";
            $res['msg'] = "活动停止失败";
        }
        return json($res);
    }
    /**
     * 获取促销活动信息
     * @return json 添加促销活动数据结果
     */
    public function promotioninsert(Request $request)
    {
        $promotion = new Promotion();
        $re['name'] = htmlspecialchars($request->param('promotename'));
        $re['start_time'] = strtotime($request->param('timestart'));
        $re['end_time'] = strtotime($request->param('timeend'));
        $re['count'] = intval($request->param('promoteprice'));
        $insert = $promotion->insert($re);
        if ($insert) {
            $res['code'] = "200";
            $res["msg"] = "增加成功";
            // 移除活动缓存
            Cache::rm('promotionInfo');
        } else {
            $res['code'] = "300";
            $res["msg"] = "增加失败";
        }
        return json($res);
    }
    /**
     * @return 用户活动页面
     */
    public function activity()
    {
        $activity = new Activity();
        $activity_user = new Activity_user();
        $field = 'activity_id, name, brief, pic, start_time, end_time, first_price_num, first_price, second_price_num, second_price, third_price_num, third_price, detail, is_active, activity_poster, qrcode';
        $data = $activity->field($field)->where('is_delete', 0)->select();
        $data = collection($data)->toArray();
        $user = $activity_user->field('activity_id')->select();
        // 参加活动的人数
        if (!empty($data) && !empty($user)) {
            foreach ($data as $key => $value) {
                $data[$key]['num'] = 0;
                foreach ($user as $k => $v) {
                    if ($value['activity_id'] == $v['activity_id']) {
                        $data[$key]['num'] += 1;
                    }
                }
            }
        }
        $this->assign('activity', $data);
        return $this->fetch();
    }
    /**
     * @return 添加用户活动页面
     */
    public function activityadd()
    {
        $goods = new Goods();
        $data = $goods->select();
        $this->assign('goods', $data);
        return $this->fetch();
    }

    /**
     * @return 活动抽奖页面
     */
    public function activitylottery()
    {
        $request = Request::instance();
        $activity_id = intval($request->param('activity_id'));
        // dump($activity_id);die;
        $this->assign('activity_id', $activity_id);
        $activity_user = new Activity_user();
        // 用户参与活动的信息
        $num = $activity_user->where('activity_id', $activity_id)->count();
        $this->assign('num', $num);
        $activity = new Activity();
        // 奖品信息
        $prideinfo = $activity->field('first_price,first_price_num,second_price,second_price_num,third_price,third_price_num,name')->where('activity_id', $activity_id)->where('is_active', 1)->find();
        $this->assign('prideinfo', $prideinfo);
        return $this->fetch();
    }

    /**
     * 获取参与抽奖的用户信息
     * @return json 用户信息
     */
    public function lotteryUser(Request $request)
    {
        // 移除缓存
        // Cache::rm('activityInfo');
        // Cache::rm('type');
        // dump(123);die;
        $activityId = intval($request->param('activity_id'));
        $activity_pride = new Activity_pride();
        $activity = new Activity;
        $activity_user = new Activity_user();
        // 先判断活动是否抽过奖
        $state = $activity->where('activity_id', $activityId)->value('state');
        // state 2已经开奖
        if ($state == 2) {
            return json("300");
        } else {
            $type = Cache::get('type');
            // 开始抽奖，暂停活动
            // 将activity表的state改为5正在抽奖
            $where['state'] = 5;
            $state = $activity->where('activity_id', $activityId)->update($where);
            $activityInfo = Cache::get('activityInfo');
            // dump($activityInfo);die;
            // type未抽奖 获取所有参加活动的用户信息
            if (!$type) {
                // dump(123);die;
                // 获取参与抽奖的用户id/name/pic
                $personArray = $activity_user->field('user_id,user_name,pic')->where('activity_id', $activityId)->select();
                if ($personArray && count($personArray) != 0) {
                    $personArray = collection($personArray)->toArray();
                }
                // 先移除
                Cache::rm('activityInfo');
                // 存入缓存
                Cache::set('activityInfo', $personArray);
                return json($personArray);
            } 
                // dump(456);die;
            // 抽奖结束后 类型2
            if ($type == 2) {
                // 移除缓存
                Cache::rm('activityInfo');
                Cache::rm('type');
                // 将activity表的state改为2已开奖
                $update['state'] = 2;
                $state = $activity->where('activity_id', $activityId)->update($update);
                return json("300");
            }
            // 未抽奖或类型1
            if ($type == 1) {
                $ary = Cache::get('activityInfo');
                // 构造返回前端中奖者数据
                foreach ($ary as $key => $value) {
                    $temp['id'] = $value['user_id'];
                    $temp['name'] = $value['user_name'];
                    $temp['image'] = $value['pic'];
                    $temp['thumb_image'] = $value['pic'];
                    $personArray[] = $temp;
                }
                // dump($personArray);die;
                return json($personArray);
            }
        }
    }

    /**
     * 获取获奖的用户
     * @return json 结果
     */
    public function luckyUser(Request $request)
    {
        $activityId = intval($request->param('activity_id'));
        // 几等奖
        $luckyPrize = intval($request->param('luckyPrize'));
        // 先判断该活动是否抽过奖
        $activity = new Activity;
        $state = $activity->where('activity_id', $activityId)->value('state');
        if ($state == 2) {
            // 已经抽奖
            Cache::set('type', 2);
            $res['res'] = 300;
            $res['luckyResult'] = '该活动已经抽过奖了！';
            return json($res);
            exit;
        }
        //获取参与抽奖的用户
        $userArr = Cache::get('activityInfo');
        // dump($userArr);die;

        // 当抽奖的人数为0时
        if (count($userArr) == 0) {
            // 抽奖完成
            Cache::set('type', 2);
            $res['res'] = 300;
            $res['luckyResult'] = '抽奖人数不足！';
            return json($res);
            exit;
        }
        // 活动设定的中奖人数
        $activity = new Activity;
        $activityData = $activity->field('first_price_num, second_price_num, third_price_num')->where('is_delete', 0)->where('activity_id', $activityId)->select();
        // 对应的中奖人数
        foreach ($activityData as $k => $v) {
            switch ($luckyPrize) {
                case 1:
                    $luckyNum = $v['first_price_num'];
                    break;
                case 2:
                    $luckyNum = $v['second_price_num'];
                    break;
                case 3:
                    $luckyNum = $v['third_price_num'];
                    break;
            }
        }        // 判断有无抽奖人数
        if ($luckyNum == 0) {
            $res['res'] = 300;
            $res['luckyResult'] = '该奖项没有设置抽奖人数哦！';
            return json($res);
            exit;
        }
        // 判断抽奖人数 当抽奖人数小于中奖人数时
        if (count($userArr) <= $luckyNum) {
            $luckyNum = count($userArr);
            // dump($luckyNum);die;
        }
        // dump(999);
        $activity_user = new Activity_user();
        $activity = new Activity();
        $activity_pride = new Activity_pride();
        // 先判断各个等奖是否抽过奖
        $isLucky = $activity_pride->field('idx')->where('activity_id', $activityId)->where('level', $luckyPrize)->find();
        if ($isLucky) {
            $res['res'] = 300;
            $res['luckyResult'] = '该奖项已经抽过奖！';
            return json($res);
            exit;
        }
        // 一等奖
        if ($luckyPrize == 1) {
            $goodsPrize = 'first_price_num, first_price';
            $num = 'first_price_num';
            $prize = 'first_price';
        }
        // 二等奖
        if ($luckyPrize == 2) {
            $goodsPrize = 'second_price_num, second_price';
            $num = 'second_price_num';
            $prize = 'second_price';
        }
        // 三等奖
        if ($luckyPrize == 3) {
            $goodsPrize = 'third_price_num, third_price';
            $num = 'third_price_num';
            $prize = 'third_price';
        }
        // 后端写中奖随机数
        // 随机下标
        $randArr = range(0, count($userArr) - 1);
        shuffle($randArr); //调用现成的数组随机排列函数
        $indexArr = array_slice($randArr, 0, $luckyNum); //截取前$luckyNum个
        // dump($indexArr);die;
        // 构造返回前端的中奖者数据
        $luckyResult = [];
        foreach ($userArr as $key => $value) {
            foreach ($indexArr as $k => $v) {
                $temp[$k]['id'] = $userArr[$v]['user_id'];
                $temp[$k]['name'] = $userArr[$v]['user_name'];
                $temp[$k]['image'] = $userArr[$v]['pic'];
                $luckyResult = $temp;
            }
        }
        // dump($luckyResult);die;
        // 活动对应的奖品信息
        $prideInfo = $activity->field($goodsPrize)->where('activity_id', $activityId)->where('is_active', 1)->select();
        // 构造中奖者信息数组
        $activityInfo = $userArr;
        $luckArr = [];
        foreach ($luckyResult as $ke => $val) {
            $luckArr[$ke]['activity_id'] = $activityId;
            $luckArr[$ke]['user_id'] = $luckyResult[$ke]['id'];
            $luckArr[$ke]['level'] = $luckyPrize;
            $luckArr[$ke]['level_price'] = $prideInfo[0][$prize];
            $luckArr[$ke]['create_time'] = time();
            // 将中奖的人数剔除
            foreach ($activityInfo as $k => $v) {
                if ($v['user_id'] == $val['id']) {
                    // unset($info);
                    array_splice($activityInfo, $k, 1);
                     // 重新缓存抽奖者数据
                    Cache::set('activityInfo', $activityInfo);
                    break 1;
                }
            }
        }
        // 写入数据库前  先查询各个奖项中奖情况
        $prideData = $activity_pride->field('level')->where('activity_id', $activityId)->select();
        // 非空判断 是否已经中奖
        if ($prideData) {
            $num1 = 0;// 一等奖中奖人数
            $num2 = 0;// 二等奖中奖人数
            $num3 = 0;// 三等奖中奖人数
            foreach ($prideData as $key => $value) {
                switch ($value['level']) {
                    case 1:
                        $num1 += 1;
                        break;
                    case 2:
                        $num2 += 1;
                        break;
                    case 3:
                        $num3 += 1;
                        break;
                }
                // 判断是否抽奖完成
                foreach ($activityData as $k => $v) {
                    // 当中奖的人数等于抽奖人数
                    if ($num1 == $v['first_price_num'] && $num2 == $v['second_price_num'] && $num3 == $v['third_price_num']) {
                        // 抽奖完成
                        Cache::set('type', 2);
                        // 移除缓存
                        // Cache::rm('activityInfo');
                        $res['res'] = 300;
                        $res['luckyResult'] = '抽奖已完成！';
                        return json($res);
                        break 1;
                    } 
                    // else {
                    //     // 移除缓存
                    //     Cache::rm('activityInfo');
                    //     // 重新缓存抽奖者数据
                    //     Cache::set('activityInfo', $activityInfo);
                    //     // 类型1 抽奖
                    //     Cache::set('type', 1);
                    //     break 1;
                    // }
                }
            }
        }
        // 当抽奖的人数为0时
        if (count($activityInfo) == 0) {
            // 抽奖完成
            Cache::set('type', 2);
        }
        if (count($activityInfo) != 0) {
            // 抽奖未完成
            Cache::set('type', 1);
        }
        // dump($activityInfo);die;

        // 中奖者写入数据库中
        $result = $activity_pride->isUpdate(false)->saveAll($luckArr);
        if ($result) {
            $res['res'] = 200;
            $res['luckyResult'] = $luckyResult;
        } else {
            $res['res'] = 300;
            $res['luckyResult'] = '抽奖失败！';
        }
        return json($res);
    }

    /**
     * 获取用户活动当前id
     * @return json 活动上线结果
     */
    public function changeActivity(Request $request)
    {
        $activity = new Activity();
        $id = intval($request->param('id'));
        $re["is_active"] = 1;
        $update = $activity->where('activity_id', $id)->update($re);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "活动已上线";
        } else {
            $res['code'] = "300";
            $res['msg'] = "活动上线失败";
        }
        return json($res);
    }

    /**
     * 获取用户活动当前id
     * @return json 活动停止结果
     */
    public function activityStop(Request $request)
    {
        $activity = new Activity();
        $id = $request->param('id');
        $re["is_active"] = 0;
        $update = $activity->where('activity_id', $id)->update($re);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "活动已停止";
            // 将activity表的state改为3已暂停
            $where['state'] = 3;
            $state = $activity->where('activity_id', $id)->update($where);
        } else {
            $res['code'] = "300";
            $res['msg'] = "活动停止失败";
        }
        return json($res);
    }

    /**
     * 添加活动信息
     * @param Request $request
     * @return json 添加用户活动数据结果
     */
    public function activityInsert(Request $request)
    {
        $activity = new Activity();
        $ac['name'] = htmlspecialchars($request->param('activityname'));
        $ac['brief'] = htmlspecialchars($request->param('activitybrief'));
        $ac['start_time'] = strtotime($request->param('countTimestart'));
        $ac['end_time'] = strtotime($request->param('countTimeend'));
        $ac['detail'] = htmlspecialchars($request->param('content'));
        $ac['first_price_num'] = intval($request->param('firstsum'));
        $ac['first_price'] = htmlspecialchars($request->param('first_price'));
        $ac['second_price_num'] = intval($request->param('secondsum'));
        $ac['second_price'] = htmlspecialchars($request->param('second_price'));
        $ac['third_price_num'] = intval($request->param('thirdsum'));
        $ac['third_price'] = htmlspecialchars($request->param('third_price'));
        $ac['is_active'] = intval($request->param('activity_active'));
        $ac['create_time'] = time();
        // 活动名称是否重复
        $isExist = $activity->field('name')->where('name', $ac['name'])->find();
        if ($isExist) {
            $res = array('code' => 300, 'msg' => '添加失败，活动名称重复!');
            return json($res);
        }
        // 是否存在session
        if (Session::has('goodspic')) {
            // 取session值
            $source = ROOT_PATH . Session::get('goodspic');
            // dump($source);die;
            // 新的路径,取session值
            $str = substr_replace(Session::get('goodspic'), 'activity', 7, 7);
            // 创建文件夹
            $str3 = substr($str, 0, 25);
            if (!is_dir(ROOT_PATH . $str3)) {
                mkdir(ROOT_PATH . $str3);
            }
            // 框架应用根目录/public/minipro/目录
            $destination = ROOT_PATH . $str;
            // 拷贝文件到指定目录
            $res = copy($source, $destination);
            // 移动成功
            if ($res) {
                $ac['pic'] = DS . $str;
                $insert = $activity->insert($ac);
                if ($insert) {
                    $res = array('code' => 200, 'msg' => '添加成功');
                } else {
                    $res = array('code' => 300, 'msg' => '添加失败，请重新上传图片！');
                }
            } else {
                $res = array('code' => 200, 'msg' => '添加成功');
            }
            return json($res);
            // 删除session信息
            Session::delete('goodspic');
        } else {
            $res = array('code' => 300, 'msg' => '添加失败，请上传图片!');
            return json($res);
        }
    }
    /**
     * 删除活动信息
     * @param Request $request
     * @return 活动详情
     */
    public function delActivity(Request $request)
    {
        $id = intval($request->param('id'));
        $activity = new Activity();
        $delete = $activity->where('activity_id', $id)->update(['is_delete' => '1']);
        if ($delete) {
            $res['code'] = "200";
            $res['msg'] = "删除成功！";
        } else {
            $res['code'] = "300";
            $res['msg'] = "删除失败！";
        }
        return json($res);
    }

    /**
     * @return 用户参与详情界面显示
     */
    public function activitytomember()
    {
        $activity = new Activity();
        $activity_user = new Activity_user();
        $activity = $activity->select();
        $activity_user = $activity_user->select();
        $this->assign('activity', $activity);
        $this->assign('activity_user', $activity_user);
        return $this->fetch();
    }

    /**
     * 选择活动
     * @param Request $request
     * @return 详情
     */
    public function selectActivity(Request $request)
    {
        $activity_user = new Activity_user();
        $activity_pride = new Activity_pride();
        $activity = new Activity();
        $activity_id = intval($request->param('activity_id'));
        // 用户总数据
        $user = $activity_user->field('user_id,user_name,join_time,activity_id,user_id')->where('activity_id', $activity_id)->select();
        // 中奖用户数据
        $info = $activity_pride->field('user_id as id,level,level_price')->where('activity_id', $activity_id)->select();
        // 中奖奖品
        // dump($info);die;
        if ($user && $info) {
            // 构造返回数组
            foreach ($user as $key => $value) {
                $temp['user_id'] = $value['user_id'];
                $temp['user_name'] = $value['user_name'];
                $temp['join_time'] = $value['join_time'];
                $temp['activity_id'] = $value['activity_id'];
                $temp['level'] = 0;
                $temp['level_price'] = '无';
                $temp['is_pride'] = 0;
                // 中奖用户数据
                foreach ($info as $k => $v) {
                    if ($value['user_id'] == $v['id']) {
                        $temp['level'] = $v['level'];
                        $temp['level_price'] = $v['level_price'];
                        $temp['is_pride'] = 1;
                        break 1;
                    }
                }
                $data[] = $temp;
            }
        } else {
            $data = '';
        }
        // dump($data);die;
        return json($data);
    }
    /**
     * @return 活动管理-活动统计界面
     */
    public function activitychart()
    {
        return $this->fetch();
    }
    /**
     * activityData 获取图表数据
     * @return  json activityData 结果
     */
    public function activityData()
    {
        $activity = new Activity();
        $activity_user = new Activity_user();
        $ary = [];
        $nameArr = [];
        $data = [];
        $activityname = $activity->field('name, activity_id')->select();
        for ($i = 0; $i < count($activityname); $i++) {
            $sum = $activity_user->where('activity_id', $activityname[$i]['activity_id'])->count();
            $ary[] = $sum;
            $nameArr[] = ['name' => $activityname[$i]['name']];
        }
        $data[] = $nameArr;
        $data[] = $ary;
        return json($data);
    }
    /**
     * @return 商品管理界面显示
     */
    public function goods(Request $request)
    {
        $goods = new Goods();
        // $catagory = new Catagory();
        $promotion = new Promotion();
        $count = $goods->field('goods_id')->where('is_delete', 0)->count();
        // $catagory = $catagory -> select();
        $promotion = $promotion->field('promotion_id,name')->where('is_active', 1)->select();
        $this->assign('count', $count);
        // $this -> assign('catagory',$catagory);
        $this->assign('promotion', $promotion);
        return $this->fetch();
    }
    /**
     * @return 商品管理界面
     */
    public function goodsCatagory(Request $request)
    {
        $goods = new Goods();
        $catagory = new Catagory();
        $catagory_name = $request->param('nodeName');
        // 是否接收到请求
        if (isset($catagory_name)) {
            $res = $catagory->where('catagory_name', $catagory_name)->select();
            $catagory_id = $res['0']['catagory_id'];
            if ($catagory_id == "") {
                $data = "";
            } else {
                //给要关联的表取别名,并让两个值关联
                $result = $goods->alias('a')->join('promotion w', 'a.promotion_id = w.promotion_id', 'left')->field('a.goods_id,a.name,a.pic,a.create_time,a.is_active,w.name as activityname')->where('a.is_delete', 0)->where('catagory_id', $catagory_id)->select();
            }
            // detail表的信息
            $goodsDetailData = $goods->alias('a')->join('goods_detail w', 'a.goods_id = w.goods_id', 'left')->field('w.goods_id as id,w.detail_name as detailname,w.market_price as marketprice,w.shop_price as shopprice,w.keywords,w.detail_intro')->where('a.is_delete', 0)->where('catagory_id', $catagory_id)->select();
            // 不为空判断
            if ($result && $goodsDetailData) {
                // 合并数组
                foreach ($result as $key => $value) {
                    $temp = null;
                    foreach ($goodsDetailData as $k => $v) {
                        if ($value['goods_id'] == $v['id']) {
                            $temp[] = $v;
                        }
                    }
                    $result[$key]['detail'] = $temp;
                }
                $data = $result;
            } else {
                $data = null;
            }
        } else {
            //给要关联的表取别名,并让两个值关联
            $result = $goods->alias('a')->join('promotion w', 'a.promotion_id = w.promotion_id', 'left')->field('a.goods_id,a.name,a.pic,a.create_time,a.is_active,w.name as activityname')->where('a.is_delete', 0)->select();
            // detail表的信息
            $goodsDetailData = $goods->alias('a')->join('goods_detail w', 'a.goods_id = w.goods_id', 'left')->field('w.goods_id,w.detail_name as detailname,w.market_price as marketprice,w.shop_price as shopprice,w.keywords,w.detail_intro')->where('a.is_delete', 0)->select();
            // 不为空判断
            if ($result && $goodsDetailData) {
                // 合并数组
                foreach ($result as $key => $value) {
                    $temp = null;
                    foreach ($goodsDetailData as $k => $v) {
                        if ($value['goods_id'] == $v['goods_id']) {
                            $temp[] = $v;
                        }
                    }
                    $result[$key]['detail'] = $temp;
                }
                $data = $result;
                $data = collection($data)->toArray();
            } else {
                $data = null;
            }
        }
        // dump($data);die;
        return json($data);
    }

    /**
     * @return 添加商品页面
     */
    public function goodsadd()
    {
        $catagory = new Catagory();
        $promotion = new Promotion();
        $catagory = $catagory->select();
        $promotion = $promotion->select();
        $this->assign('catagory', $catagory);
        $this->assign('promotion', $promotion);
        return $this->fetch();
    }

    /**
     * 获取商品当前id
     * @return json 商品上架
     */
    public function changegoods(Request $request)
    {
        $goods = new Goods();
        $id = $request->param('id');
        $re["last_up_time"] = time();
        $re["is_active"] = 1;
        $update = $goods->where('goods_id', $id)->update($re);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "商品已上架";
            Cache::rm('shopGoodsInfo');
            Cache::rm('shopGoodsCount');
        } else {
            $res['code'] = "300";
            $res['msg'] = "商品上架失败";
            $res['goods_id'] = $id;
        }
        return json($res);
    }

    /**
     * 获取商品前id
     * @return json 商品下架
     */
    public function goodsstop(Request $request)
    {
        $goods = new Goods();
        $id = $request->param('id');
        $re["last_down_time"] = time();
        $re["is_active"] = 0;
        $update = $goods->where('goods_id', $id)->update($re);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "商品下架";
            $res['goods_id'] = $id;
            Cache::rm('shopGoodsInfo');
            Cache::rm('shopGoodsCount');
        } else {
            $res['code'] = "300";
            $res['msg'] = "商品下架失败请刷新";
        }
        return json($res);
    }
    /**
     * 获取当前分类id
     * @return json 删除商品结果
     */
    public function goodsdel(Request $request)
    {
        $goods = new Goods();
        $id = $request->param('id');
        $re["delete_time"] = time();
        $re["is_delete"] = 1;
        $update = $goods->where('goods_id', $id)->update($re);
        if ($update) {
            $res['code'] = "200";
            $res["msg"] = "删除成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "删除失败";
        }
        return json($res);
    }
    /**
     * 获取分类id
     * @return json 分类的下级元素
     */
    public function catagoryson(Request $request)
    {
        $catagory = new Catagory();
        $id = $request->param('id');
        $catagory = $catagory->where('catagory_id', $id)->find();
        if ($catagory["father_catagory_id"] == 0) {
            $son = $catagory->where('father_catagory_id', $id)->select();
            $res["code"] = 200;
            $res["msg"] = $son;
        } else {
            $res["code"] = 100;
            $res["msg"] = "";
        }
        echo json_encode($son);
    }
    /**
     * addPic 添加图片
     * @return json 添加结果
     */
    public function addPic(Request $request)
    {
        $file = request()->file('file');
        if (Session::has('goodspic')) {
            // 删除session信息
            Session::delete('goodspic');
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public/' . 'uploads');
        if ($info) {
            $str = $info->getSaveName();
            $goodspic = 'public/uploads/' . $str;
            // 存路径名到session
            Session::set('goodspic', $goodspic);
        }
        return $goodspic;
    }
    /**
     * 添加商品信息
     * @return json 添加商品
     */
    public function insertGoods(Request $request)
    {
        $goods = new Goods();
        $goods_detail = new Goods_detail();
        $good['name'] = htmlspecialchars($request->param('goodsname'));
        $good['catagory_id'] = intval($request->param('catagoryid'));
        $good['promotion_id'] = intval($request->param('promotion'));
        // 购买须知
        if (!empty($request->param('content1'))) {
            $good['intro'] = htmlspecialchars($request->param('content1'));
        } else {
            $good['intro'] = '';
        }
        // 商品描述
        if (!empty($request->param('content'))) {
            $good['spec'] = htmlspecialchars($request->param('content'));
        } else {
            $good['spec'] = '';
        }
        $good['dis_percent'] = intval($request->param('dis_percent'));
        $good['parent_dis_percent'] = intval($request->param('parent_dis_percent'));
        $good['grand_dis_percent'] = intval($request->param('grand_dis_percent'));
        $good['is_active'] = intval($request->param('updown'));
        $good['is_distri'] = intval($request->param('distri'));
        $good['create_time'] = time();
        // 商品detail
        $goods_name = $request->param('goods_name');
        $goods_keyword = $request->param('goods_keyword');
        $market_price = $request->param('market_price');
        $shop_price = $request->param('shop_price');
        $goods_intro = $request->param('goods_intro');
        // $distri        = $request->param('distri');
        $stock = $request->param('stock');
        $promotion = intval($request->param('promotion'));
        if ($promotion != 0) {
            $good['is_on_promotion'] = 1;
            $good['promotion_id'] = intval($request->param('promotion'));
        }
        // 是否存在session
        if (Session::has('goodspic')) {
            // 取session值
            $source = ROOT_PATH . Session::get('goodspic');
            // dump($source);die;
            // 新的路径,取session值
            $str = substr_replace(Session::get('goodspic'), 'goodspic', 7, 7);
            // 创建文件夹
            $str3 = substr($str, 0, 25);
            if (!file_exists(ROOT_PATH . $str3)) {
                mkdir(ROOT_PATH . $str3);
            }
            // 框架应用根目录/public/minipro/目录
            $destination = ROOT_PATH . $str;
            // 拷贝文件到指定目录
            $res = copy($source, $destination);
            // 移动成功
            if ($res) {
                $good['pic'] = DS . $str;
            }
            // 删除session信息
            Session::delete('goodspic');
        } else {
            $result['code'] = "300";
            $result["msg"] = "保存失败，请上传图片！";
            return json($result);
            exit;
        }
        // 商品详情
        $goodsName = rtrim($goods_name, ',');
        $goodsKeyword = rtrim($goods_keyword, ',');
        $marketPrice = rtrim($market_price, ',');
        $shopPrice = rtrim($shop_price, ',');
        $goodsIntro = rtrim($goods_intro, ',');
        // $distri          = rtrim($distri, ',');
        $stock = rtrim($stock, ',');
        $goodsNameArr = explode(',', $goodsName);
        $goodsKeywordArr = explode(',', $goodsKeyword);
        $goodsIntroArr = explode(',', $goodsIntro);
        // $distriArr       = explode(',', $distri);
        $marketPriceArr = explode(',', $marketPrice);
        $shopPriceArr = explode(',', $shopPrice);
        $stockArr = explode(',', $stock);
        // 新增数据并返回主键值
        $goodsId = $goods->insertGetId($good);
        if (!empty($goodsId)) {
            $goodsDetailData = [];
            for ($i = 0; $i < count($goodsNameArr); $i++) {
                $temp[$i]['detail_name'] = htmlspecialchars($goodsNameArr[$i]);
                $temp[$i]['keywords'] = htmlspecialchars($goodsKeywordArr[$i]);
                $temp[$i]['market_price'] = $marketPriceArr[$i];
                $temp[$i]['shop_price'] = $shopPriceArr[$i];
                $temp[$i]['detail_intro'] = htmlspecialchars($goodsIntroArr[$i]);
                // $temp[$i]['is_distri']    = $distriArr[$i];
                $temp[$i]['goods_id'] = $goodsId;
                $temp[$i]['stock'] = $stockArr[$i];
                $temp[$i]['create_time'] = time();
                $goodsDetailData = $temp;
            }
            $insert = $goods_detail->isUpdate(false)->saveAll($goodsDetailData);
            if ($insert) {
                $result['code'] = "200";
                $result["msg"] = "保存成功";
                // 移除本地商品缓存/系统商品总数缓存
                Cache::rm('shopGoodsInfo');
                Cache::rm('shopGoodsCount');
            }
        } else {
            $result['code'] = "300";
            $result["msg"] = "保存失败";
        }
        return json($result);
    }

    /**
     * @return 修改商品页面
     */
    public function goodsupdate()
    {
        $request = Request::instance();
        $goods_id = intval($request->param('goods_id'));
        $goods = new Goods();
        $catagory = new Catagory();
        $promotion = new Promotion();
        $goods_detail = new Goods_detail();

        $res = $goods->field('goods_id,catagory_id,name,pic,is_on_promotion,promotion_id,is_distri,dis_percent,parent_dis_percent,grand_dis_percent,is_active,intro,spec')->where('goods_id', $goods_id)->where('is_delete', 0)->select();
        // 将数据库查询出来的obj转为array
        $goodsInfo = collection($res)->toArray();
        // detail表的信息
        $goodsDetailData = $goods->alias('a')->join('goods_detail w', 'a.goods_id = w.goods_id', 'left')->field('w.goods_id as id,w.detail_name as detailname,w.market_price as marketprice,w.shop_price as shopprice,w.keywords,w.detail_intro,w.stock')->where('a.is_delete', 0)->select();
        if ($goodsInfo && $goodsDetailData) {
            // 合并数组
            foreach ($goodsInfo as $key => $value) {
                $temp = null;
                foreach ($goodsDetailData as $k => $v) {
                    if ($value['goods_id'] == $v['id']) {
                        $temp[] = $v;
                    }
                }
                $goodsInfo[$key]['detail'] = $temp;
            }
            $goodsInfo = $goodsInfo[0];
        }
        $catagory = $catagory->select();
        $promotion = $promotion->select();

        $this->assign('goodsInfo', $goodsInfo);
        $this->assign('catagory', $catagory);
        $this->assign('promotion', $promotion);
        return $this->fetch();
    }

    /**
     * 对应商品详情
     * @return 显示商品详情
     */
    public function goodsInfo(Request $request)
    {
        // $goods_id = intval($request-> param('goods_id'));
    }

    /**
     * 修改商品信息
     * @return 修改结果
     */
    public function updateGoods(Request $request)
    {
        $goods_id = intval($request->param('goods_id'));
        $goods = new Goods();
        $goods_detail = new Goods_detail();
        $good['goods_id'] = $goods_id;
        $good['name'] = htmlspecialchars($request->param('goodsname'));
        $good['catagory_id'] = intval($request->param('catagoryid'));
        // 购买须知
        if (!empty($request->param('content1'))) {
            $good['intro'] = htmlspecialchars($request->param('content1'));
        } else {
            $good['intro'] = '';
        }
        // 商品描述
        if (!empty($request->param('content'))) {
            $good['spec'] = htmlspecialchars($request->param('content'));
        } else {
            $good['spec'] = '';
        }
        $good['dis_percent'] = intval($request->param('dis_percent'));
        $good['parent_dis_percent'] = intval($request->param('parent_dis_percent'));
        $good['grand_dis_percent'] = intval($request->param('grand_dis_percent'));
        $good['is_active'] = intval($request->param('updown'));
        $good['is_distri'] = intval($request->param('distri'));
        // 商品详情
        $goods_name = $request->param('goods_name');
        $goods_keyword = $request->param('goods_keyword');
        $market_price = $request->param('market_price');
        $shop_price = $request->param('shop_price');
        $goods_intro = $request->param('goods_intro');
        // $distri        = $request->param('distri');
        $stock = $request->param('stock');

        $promotion = intval($request->param('promotion'));
        if ($promotion != 0) {
            $good['is_on_promotion'] = 1;
            $good['promotion_id'] = intval($request->param('promotion'));
        }
        // 是否存在session
        if (Session::has('goodspic')) {
            // 取session值
            $source = ROOT_PATH . Session::get('goodspic');
            // dump($source);die;
            // 新的路径,取session值
            $str = substr_replace(Session::get('goodspic'), 'goodspic', 7, 7);
            // 创建文件夹
            $str3 = substr($str, 0, 25);
            if (!file_exists(ROOT_PATH . $str3)) {
                mkdir(ROOT_PATH . $str3);
            }
            // 框架应用根目录/public/minipro/目录
            $destination = ROOT_PATH . $str;
            // 拷贝文件到指定目录
            $res = copy($source, $destination);
            // 移动成功
            if ($res) {
                $good['pic'] = DS . $str;
            }
            // 删除session信息
            Session::delete('goodspic');
        }
        // 商品详情
        $goodsName = rtrim($goods_name, ',');
        $goodsKeyword = rtrim($goods_keyword, ',');
        $marketPrice = rtrim($market_price, ',');
        $shopPrice = rtrim($shop_price, ',');
        $goodsIntro = rtrim($goods_intro, ',');
        // $distri          = rtrim($distri, ',');
        $stock = rtrim($stock, ',');
        $goodsNameArr = explode(',', $goodsName);
        $goodsKeywordArr = explode(',', $goodsKeyword);
        $goodsIntroArr = explode(',', $goodsIntro);
        // $distriArr       = explode(',', $distri);
        $marketPriceArr = explode(',', $marketPrice);
        $shopPriceArr = explode(',', $shopPrice);
        $stockArr = explode(',', $stock);
        // 更新需要的主键
        $res = $goods_detail->where('goods_id', $goods_id)->select();
        foreach ($res as $key => $value) {
            $idxArr[] = $res[$key]['idx'];
        }
        // 先删除原来的数据
        $delete = $goods_detail->where('goods_id', $goods_id)->delete();
        // 更新goods表数据
        $goodsId = $goods->update($good);
        if ($goodsId) {
            $goodsDetailData = [];
            for ($i = 0; $i < count($goodsNameArr); $i++) {
                $temp[$i]['detail_name'] = htmlspecialchars($goodsNameArr[$i]);
                $temp[$i]['keywords'] = htmlspecialchars($goodsKeywordArr[$i]);
                $temp[$i]['market_price'] = $marketPriceArr[$i];
                $temp[$i]['shop_price'] = $shopPriceArr[$i];
                $temp[$i]['detail_intro'] = htmlspecialchars($goodsIntroArr[$i]);
                // $temp[$i]['is_distri']    = $distriArr[$i];
                $temp[$i]['goods_id'] = $goods_id;
                $temp[$i]['stock'] = $stockArr[$i];
                $temp[$i]['create_time'] = time();
                $goodsDetailData = $temp;
            }
            $insert = $goods_detail->isUpdate(false)->saveAll($goodsDetailData);
            if ($insert) {
                $result['code'] = "200";
                $result["msg"] = "修改成功";
                // 移除本地商品缓存/系统商品总数缓存
                Cache::rm('shopGoodsInfo');
                Cache::rm('shopGoodsCount');
            }
        } else {
            $result['code'] = "300";
            $result["msg"] = "修改失败";
        }
        return json($result);
    }

    /**
     * 获取商品id
     * @return 显示商品详情
     */
    public function goodsdetail(Request $request)
    {
        $goods = new Goods();
        $id = $request->param('id');
        $detail = $goods->where('goods_id', $id)->find();
        $this->assign('goods', $detail);
        return $this->fetch();
    }

    /**
     * [changeAllPromotion 批量更改活动状态]
     * @return [ary]      [返回值]
     */
    public function changeAllPromotion(Request $request)
    {
        $promotion = $request->param('promotion');
        if ($promotion == 0) {
            $goods['is_on_promotion'] = 0;
            $promotion = $request->param('promotion');
        } else {
            $goods['is_on_promotion'] = 1;
            $promotion = $request->param('promotion');
        }
        $wid = $request->param('ids');
        $wid = substr($wid, 0, strlen($wid) - 1);
        $widArr = explode("*", $wid);
        $updateWidArr = array();
        for ($i = 0; $i < count($widArr); $i++) {
            $arr = array();
            $arr['goods_id'] = $widArr[$i];
            $arr['is_on_promotion'] = $goods['is_on_promotion'];
            $arr['promotion_id'] = $promotion;
            $updateWidArr[] = $arr;
        }
        $goods = new Goods;
        $changepromotion = $goods->saveAll($updateWidArr);
        if ($changepromotion) {
            $res['code'] = "200";
            $res["msg"] = "更改成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "更改失败";
        }
        return json($res);
    }
    /**
     * [upGoods 批量上架]
     * @param  Request $request   [数据]
     * @return [ary]            [返回值]
     */
    public function upGoods(Request $request)
    {
        $wid = $request->param('ids');
        $wid = substr($wid, 0, strlen($wid) - 1);
        $widArr = explode("*", $wid);
        $updateWidArr = array();
        $goods = new Goods;
        for ($i = 0; $i < count($widArr); $i++) {
            $arr = array();
            $arr['goods_id'] = $widArr[$i];
            $arr['is_active'] = 1;
            $updateWidArr[] = $arr;
        }
        $upgoods = $goods->saveAll($updateWidArr);
        if ($upgoods) {
            $res['code'] = "200";
            $res["msg"] = "上架成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "上架失败";
        }
        return json($res);
    }
    /**
     * [downGoods 批量下架]
     * @param  Request $request   [数据]
     * @return [ary]            [返回值]
     */
    public function downGoods(Request $request)
    {
        $wid = $request->param('ids');
        $wid = substr($wid, 0, strlen($wid) - 1);
        $widArr = explode("*", $wid);
        $updateWidArr = array();
        $goods = new Goods;
        for ($i = 0; $i < count($widArr); $i++) {
            $arr = array();
            $arr['goods_id'] = $widArr[$i];
            $arr['is_active'] = 0;
            $updateWidArr[] = $arr;
        }
        $downgoods = $goods->saveAll($updateWidArr);
        if ($downgoods) {
            $res['code'] = "200";
            $res["msg"] = "下架成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "下架失败";
        }
        return json($res);
    }
    /**
     * 获取商品批量id
     * @return josn 批量删除
     */
    public function delgoods(Request $request)
    {
        $wid = intval($request->param('ids'));
        $wid = substr($wid, 0, strlen($wid) - 1);
        $widArr = explode("*", $wid);
        $updateWidArr = array();
        $goods = new Goods;
        for ($i = 0; $i < count($widArr); $i++) {
            $arr = array();
            $arr['goods_id'] = $widArr[$i];
            $arr['is_delete'] = 1;
            $updateWidArr[] = $arr;
        }
        $delete = $goods->saveAll($updateWidArr);
        if ($delete) {
            $res['code'] = "200";
            $res["msg"] = "删除成功";
        } else {
            $res['code'] = "300";
            $res["msg"] = "删除失败";
        }
        return json($res);
    }

    /**
     * 订单管理页面
     */
    public function ordermanagement()
    {
        $order = new Order();
        // $num = $order->field('order_id')->count();
        // $this->assign('num', $num);
        return $this->fetch();
    }

    /**
     * 发货商品
     * @return ary           发货结果
     */
    public function expressTo(Request $request)
    {
        $orderId = $request->param('order_id');
        $order = new Order();
        $re['express_co'] = htmlspecialchars($request->param('express_co'));
        // $re['express_fee']  = number_format($request->param('express_fee'), 2);
        $re['express_num'] = $request->param('express_num');
        $re['express_time'] = time();
        $re['status'] = 3;
        $update = $order->where('order_id', $orderId)->update($re);
        if ($update) {
            $res['code'] = "200";
            $res['msg'] = "已发货！";
        } else {
            $res['code'] = "300";
            $res['msg'] = "发货失败";
        }
        return json($res);
    }

    /**
     * 获取商品详情
     * @return josn 商品详情
     */
    public function orderDetail(Request $request)
    {
        $order = new Order;
        $order_detail = new Order_detail;
        $promotion = new Promotion();
        $user = new Promotion();
        // 获取活动信息
        $promotionList = $promotion->field('promotion_id, name as promotion_name, count as promotion_count')->select();
        // 获取订单
        $orderField = 'order_id, user_openid, user_id, total_fee, express_fee, express_co, express_num, status, create_time, tel_num, user_name, pay_time, cancel_time, address, tel_num, is_refound, message, verify_time, apply_refound_time, accept_refound_time, onas_time, finish_as_time';
        $orderList = $order->where('is_delete', 0)->field($orderField)->select();
        if (!$orderList) {
            $orderList = '';
            return json($orderList);
            exit;
        }
        $orderList = collection($orderList)->toArray();
        // 时间戳转换
        foreach ($orderList as &$order) {
            if($order['pay_time'])  $order['pay_time'] = date('Y-m-d H:i:s', intval($order['pay_time']));
            if($order['cancel_time'])  $order['cancel_time'] = date('Y-m-d H:i:s', intval($order['cancel_time']));
            if($order['verify_time'])  $order['verify_time'] = date('Y-m-d H:i:s', intval($order['verify_time']));
            if($order['apply_refound_time'])  $order['apply_refound_time'] = date('Y-m-d H:i:s', intval($order['apply_refound_time']));
            if($order['accept_refound_time'])  $order['accept_refound_time'] = date('Y-m-d H:i:s', intval($order['accept_refound_time']));
            if($order['onas_time'])  $order['onas_time'] = date('Y-m-d H:i:s', intval($order['onas_time']));
            if($order['finish_as_time'])  $order['finish_as_time'] = date('Y-m-d H:i:s', intval($order['finish_as_time']));
        }
        // 构造商品详情
        foreach ($orderList as $k => $v) {
            // 商品详情表
            $detail = $order_detail->alias('d')->join('ft_goods g', 'd.goods_id = g.goods_id', 'LEFT')->where('d.order_id', $v['order_id'])->field('d.idx, d.goods_id, d.detail_id, d.quantity, d.market_price, d.shop_price, d.promotion_id, g.name, g.pic')->select();
            // 获取商品总数
            $goodsTotalNum = 0;
            $detail = collection($detail)->toArray();
            foreach ($detail as $ke => $va) {
                $goodsTotalNum += $va['quantity'];
                $detail[$ke]['pic'] = "https://ft.up.maikoo.cn" . $va['pic'];
                if ($promotionList) {
                    $promotionList = collection($promotionList)->toArray();
                    foreach ($promotionList as $key => $value) {
                        $detail[$ke]['promotion_name'] = '无活动';
                        $detail[$ke]['promotion_count'] = '无折扣';
                        if ($va['promotion_id'] == $value['promotion_id']) {
                            $detail[$ke]['promotion_name'] = $value['promotion_name'];
                            $detail[$ke]['promotion_count'] = $value['promotion_count'];
                        }
                    }
                }
            }
            $orderList[$k]['detail'] = $detail;
        }
        return json($orderList);
    }

    /**
     * 选择订单状态
     * @return josn 订单状态
     */
    public function selectState(Request $request)
    {
        $status = intval($request->param('state'));
        $order = new Order;
        $order_detail = new Order_detail;
        $promotion = new Promotion();
        $user = new Promotion();
        // 获取活动信息
        $promotionList = $promotion->field('promotion_id, name as promotion_name, count as promotion_count')->select();
        // 获取订单
        $orderList = $order->where('is_delete', 0)->field('order_id, user_openid, user_id, total_fee, express_fee, express_co, express_num, status, create_time, tel_num, user_name, pay_time, cancel_time, address, tel_num, is_refound, message, verify_time, apply_refound_time, accept_refound_time, onas_time, finish_as_time')->where('status', $status)->select();
        if (!$orderList) {
            $orderList = '';
            return json($orderList);
            exit;
        }
        $orderList = collection($orderList)->toArray();
        // 时间戳转换
        foreach ($orderList as &$order) {
            if($order['pay_time'])  $order['pay_time'] = date('Y-m-d H:i:s', intval($order['pay_time']));
            if($order['cancel_time'])  $order['cancel_time'] = date('Y-m-d H:i:s', intval($order['cancel_time']));
            if($order['verify_time'])  $order['verify_time'] = date('Y-m-d H:i:s', intval($order['verify_time']));
            if($order['apply_refound_time'])  $order['apply_refound_time'] = date('Y-m-d H:i:s', intval($order['apply_refound_time']));
            if($order['accept_refound_time'])  $order['accept_refound_time'] = date('Y-m-d H:i:s', intval($order['accept_refound_time']));
            if($order['onas_time'])  $order['onas_time'] = date('Y-m-d H:i:s', intval($order['onas_time']));
            if($order['finish_as_time'])  $order['finish_as_time'] = date('Y-m-d H:i:s', intval($order['finish_as_time']));
        }
        // 构造商品详情
        foreach ($orderList as $k => $v) {
            // 商品详情表
            $detail = $order_detail->alias('d')->join('ft_goods g', 'd.goods_id = g.goods_id', 'LEFT')->where('d.order_id', $v['order_id'])->field('d.idx, d.goods_id, d.detail_id, d.quantity, d.market_price, d.shop_price, d.promotion_id, g.name, g.pic')->select();
            // 获取商品总数
            $goodsTotalNum = 0;
            $detail = collection($detail)->toArray();
            foreach ($detail as $ke => $va) {
                $goodsTotalNum += $va['quantity'];
                $detail[$ke]['pic'] = "https://ft.up.maikoo.cn" . $va['pic'];
                if ($promotionList) {
                    $promotionList = collection($promotionList)->toArray();
                    foreach ($promotionList as $key => $value) {
                        $detail[$ke]['promotion_name'] = '无活动';
                        $detail[$ke]['promotion_count'] = '无折扣';
                        if ($va['promotion_id'] == $value['promotion_id']) {
                            $detail[$ke]['promotion_name'] = $value['promotion_name'];
                            $detail[$ke]['promotion_count'] = $value['promotion_count'];
                        }
                    }
                }
            }
            $orderList[$k]['detail'] = $detail;
        }
        return json($orderList);
    }

    // public function cancelDistribution(Request $request)
    // {
    // $orderId = $request->param('order_id');
    // $where['is_success'] = 0;
    // $distribution_fee = new Distribution_fee;
    // $result = $distribution_fee ->where('order_id',$orderId) ->update($where);
    // if($result){
    //     $res['code'] = "200";
    //     $res['msg']  = "已更改";
    // }else{
    //     $res['code'] = "300";
    //     $res['msg']  = "更改失败";
    // }
    // return json($res);

    // $data = $distribution_fee ->field('parent_id,parent_fee,grand_id,grand_fee')->where('order_id',$orderId) ->select();
    // 缓存的数据
    // $cacheData = Cache::get('userAccountInfo');
    // dump($cacheData);die;
    // // 查询出的数据 id匹配后减去缓存的数据 存入缓存中
    // foreach ($data as $key => $value) {
    //     foreach ($cacheData as $k => $v) {

    //     }
    // }
    // }

    /**
     * 更改售后状态
     * @param  Request $request 参数
     * @return ary              返回值
     */
    public function cancelAfterSale(Request $request)
    {
        $orderId = $request->param('order_id');
        $where['status'] = 7;
        $where['finish_as_time'] = time();
        $order = new Order;
        $result = $order->where('order_id', $orderId)->update($where);
        if ($result) {
            $res['code'] = "200";
            $res['msg'] = "已更改！";
        } else {
            $res['code'] = "300";
            $res['msg'] = "更改失败！";
        }
        return json($res);
    }

    /**
     * 插入或更新小程序基本信息
     * @return  result              更新结果
     */
    public function editProgram(Request $request)
    {
        $system_setting = new System_setting;
        $idx = intval($request->param('idx'));
        $data['mini_name'] = htmlspecialchars($request->param('mini_name'));
        $data['service_phone'] = $request->param('service_phone');
        $data['logi_fee'] = $request->param('logi_fee');
        $data['logi_free_fee'] = $request->param('logi_free_fee');
        $data['user_rebate_min'] = $request->param('user_rebate_min');
        $data['share_text'] = htmlspecialchars($request->param('share_text'));
        $data['mini_color'] = $request->param('mini_color');
        $data['update_at'] = time();
        // $data['update_by'] = Session::get('admin_id');
        $result = $system_setting->where(['idx' => $idx])->update($data);
        if ($result) {
            $res['code'] = "200";
            $res['msg'] = "保存成功!";
        } else {
            $res['code'] = "300";
            $res['msg'] = "保存失败!";
        }
        return json($res);
    }

    /**
     * 小程序编辑界面
     * @return  array  小程序基本信息数据
     */
    public function miniproset(Request $request)
    {
        $system_setting = new System_setting;
        $systemSettingData = $system_setting->order('idx asc')->select();
        if ($systemSettingData) {
            $systemSettingData = collection($systemSettingData)->toArray();
            $systemSettingData = $systemSettingData[0];
            $systemSettingData['layer_img'] = empty($systemSettingData['layer_img']) ? '' : '/static' . $systemSettingData['layer_img'];
            $systemSettingData['logi_fee'] = number_format($systemSettingData['logi_fee'], 2);
        }
        $this->assign('data', $systemSettingData);
        return $this->fetch();
    }

    /**
     * 提现管理页面
     * @return ary 数据
     */
    public function rebateset()
    {
        $user_rebate = new User_rebate;
        // 连表查询
        $userRebateData = $user_rebate->alias('a')->join('userinfo w', 'a.user_id = w.user_id', 'LEFT')->join('admin n', 'a.update_by = n.user_id', 'LEFT')->field('a.idx,a.rebate,a.created_at,a.status,a.update_by,a.update_at,w.name,w.tel_num,n.username')->select();
        // 非空判断
        if ($userRebateData) {
            $userRebateData = collection($userRebateData)->toArray();
            // 时间戳转换
            foreach ($userRebateData as &$rebate) {
                $rebate['created_at'] = date('Y-m-d H:i:s', $rebate['created_at']);
                $rebate['update_at'] = $rebate['update_at'] ? date('Y-m-d H:i:s', intval($rebate['update_at'])) : '';
            }
        }
        $this->assign('userRebateData', $userRebateData);
        return $this->fetch();
    }

    /**
     * 是否通过提现
     * @param  Request $request 参数
     * @return ary              返回值
     */
    public function passRebate(Request $request)
    {
        $idx = intval($request->param('idx'));
        $where['update_by'] = Session::get('userId');
        $where['update_at'] = time();
        $where['status'] = 2;
        $user_rebate = new User_rebate;
        $result = $user_rebate->where('idx', $idx)->update($where);
        if ($result) {
            $res['code'] = "200";
            $res['msg'] = "提现成功!";
        } else {
            $res['code'] = "300";
            $res['msg'] = "提现失败!";
        }
        return json($res);
    }

    /**
     * 点击生成海报地址
     * @param  Request $request 参数
     * @return ary              返回值
     */
    public function createActivityPoster(Request $request)
    {
        $activityId = intval($request->param('activity_id'));
        // 跨控制器调用
        $fante = controller('Fangte');
        $url   = $fante->getActivityQRCode($activityId);
        if ($url) {
            $res['code'] = "200";
            $res["msg"]  = "生成成功！";
            $res["data"] = $url;
        } else {
            $res['code'] = "300";
            $res["msg"]  = "生成失败！";
        }
        return json($res);
    }
}
