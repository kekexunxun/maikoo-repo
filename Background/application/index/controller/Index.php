<?php
namespace app\index\controller;

use app\index\model\Admin;
use app\index\model\Order;
use app\index\model\Power;
use app\index\model\User;
use \think\Controller;
use \think\Request;
use \think\Session;

class Index extends Controller
{
    /**
     * index 主页
     * @return   html     页面
     */
    public function index()
    {
        // 判断是否存在session
        if (!Session::has('loginname')) {
            header("Location: http://xnps.up.maikoo.cn/index/index/login");
        } else {
            $username = Session::get('loginname');
            $this->assign("username", $username);
            $admin = new Admin;
            $admin_id = $admin->where('username', $username)->where('status', '<>', 2)->value('admin_id');
            // 查询mch_id的值
            $mchId = $admin->where('admin_id', $admin_id)->value('mch_id');
            // 存入Session中
            Session::set('admin_id', $admin_id);
            Session::set('mch_id', $mchId);
            $this->assign('admin_id', $admin_id);
            if ($admin_id != '') {
                // 根据id找菜单的id
                $power = new Power();
                // $menuList = $power -> field('menu_id') -> where('admin_id',$admin_id) -> select();
                $powerList = $power->field('menu_id')->where('admin_id', $admin_id)->select();
                $powerList = collection($powerList)->toArray();
                if ($powerList) {
                    $menuId = $powerList[0]['menu_id'];
                    Session::set('menuId', $menuId);
                    // 对字符串处理转为数组
                    $ary = explode(',', $menuId);
                    // 组成新数组
                    foreach ($ary as $key => $value) {
                        $temp = array('menu_id' => intval($value));
                        $menuList[] = $temp;
                    }
                    // dump($menuList);die;
                    $this->assign("menuList", $menuList);
                }
                return $this->fetch();
            }
        }
    }
    /**
     * @return 登录界面
     */
    public function login()
    {
        Session::clear();
        return $this->fetch();
    }

    /**
     * @return 退出登录
     */
    public function logout()
    {
        Session::delete('loginname');
        $url = 'http://xnps.up.maikoo.cn/index/index/login';
        $this->redirect($url);
    }

    /**
     * checkLogin 确认登录信息
     *
     * @param    Request    $request 参数
     * @return   ary              返回值
     */
    public function checkLogin(Request $request)
    {
        $username = $request->param('username');
        $password = $request->param('password');
        $admin = new Admin;
        $res = $admin->where('username', $username)->select();
        if (empty($res)) {
            return objReturn(100, '账号不存在！');
        } else {
            $result = $admin->where('username', $username)->where('status', 0)->find();
            if ($result) {
                return objReturn(400, '账号未启用！');
            } else {
                $result = $admin->where('username', $username)->where('password', $password)->find();
                if ($result) {
                    // 存登录名到全局session
                    Session::set('loginname', $username);
                    return objReturn(0, '登录成功！');
                } else {
                    return objReturn(300, '密码错误！');
                }
            }
        }
    }

    /**
     * @return 欢迎页面
     */
    public function welcome()
    {
        $order = new Order();
        $user = new User();
        // 时间戳
        $starttime = strtotime(date('Y-m-d', strtotime('-1 days'))); //昨天时间戳
        $todaytime = strtotime(date("Y-m-d 00:00:00")); //当天时间戳
        $endtime = strtotime(date('Y-m-d', strtotime('+1 days'))); //当天结束时间戳
        // 今日平台收入与昨日平台收入数据
        $res1 = $order->field('total_fee, finish_at')->where('finish_at', 'between', [$starttime, $endtime])->where('status', 'in', [4, 5])->select();
        // 先定义收入0
        $yesorder = 0;
        $todayorder = 0;
        // 先定义订单数为0
        $yestotal = 0;
        $todaytotal = 0;
        if ($res1) {
            $res1 = collection($res1)->toArray();
            // dump($res1);die;
            // 计算今天与昨日的收入
            foreach ($res1 as $key => $value) {
                if ($value['finish_at'] >= $starttime && $value['finish_at'] < $todaytime) {
                    $yesorder += 1;
                    $yestotal += $value['total_fee'];
                }
                if ($value['finish_at'] >= $todaytime && $value['finish_at'] < $endtime) {
                    // dump($value['total_fee']);
                    $todayorder += 1;
                    $todaytotal += $value['total_fee'];
                }
            }
        }
        $this->assign('yesorder', $yesorder);
        $this->assign('todayorder', $todayorder);
        $this->assign('yestotal', number_format($yestotal, 2));
        $this->assign('todaytotal', number_format($todaytotal, 2));

        // 今日未完成订单与昨日未完成订单数据
        $res2 = $order->field('total_fee, finish_at')->where('created_at', 'between', [$starttime, $endtime])->where('status', 'in', [2, 3])->select();
        // 先定义订单数为0
        $yesundoneorder = 0;
        $todayundoneorder = 0;
        if ($res2) {
            $res2 = collection($res2)->toArray();
            // 计算今天与昨日的订单
            foreach ($res2 as $key => $value) {
                if ($value['finish_at'] >= $starttime && $value['finish_at'] < $todaytime) {
                    $yesundoneorder += 1;
                }
                if ($value['finish_at'] >= $todaytime && $value['finish_at'] < $endtime) {
                    $todayundoneorder += 1;
                }
            }
        }
        $this->assign('yesundoneorder', $yesundoneorder);
        $this->assign('todayundoneorder', $todayundoneorder);

        // 今日已取消订单与昨日已取消订单数据
        $res3 = $order->field('total_fee, finish_at')->where('created_at', 'between', [$starttime, $endtime])->where('status', 6)->select();
        // 先定义订单数为0
        $yescancelorder = 0;
        $todaycancelorder = 0;
        if ($res3) {
            $res3 = collection($res3)->toArray();
            // 计算今天与昨日的订单
            foreach ($res3 as $key => $value) {
                if ($value['finish_at'] >= $starttime && $value['finish_at'] < $todaytime) {
                    $yescancelorder += 1;
                }
                if ($value['finish_at'] >= $todaytime && $value['finish_at'] < $endtime) {
                    $todaycancelorder += 1;
                }
            }
        }
        $this->assign('yescancelorder', $yescancelorder);
        $this->assign('todaycancelorder', $todaycancelorder);

        // 平台总收益与已完成总订单
        $res4 = $order->field('total_fee, order_id')->where('status', 'in', [4, 5])->select();
        // 先定义总收益与总订单为0
        $systemtotal = 0;
        $systemorder = 0;
        if ($res4) {
            $res4 = collection($res4)->toArray();
            // 计算
            foreach ($res4 as $key => $value) {
                $systemtotal += $value['total_fee'];
                $systemorder += 1;
            }
        }
        $this->assign('systemtotal', $systemtotal);
        $this->assign('systemorder', $systemorder);

        // 未完成总订单
        $systemundoneorder = $order->field('order_id')->where('status', 'in', [2, 3])->count();
        $this->assign('systemundoneorder', $systemundoneorder);
        // 已取消总订单
        $systemcancelorder = $order->field('order_id')->where('status', 6)->count();
        $this->assign('systemcancelorder', $systemcancelorder);

        // 会员数量
        $time = strtotime(date('Y-m-01 00:00:00')); //当月1号的时间戳
        $nowtime = strtotime(date('Y-m-d H:i:s')); //现在的时间戳
        $res5 = $user->field('uid, created_at')->select();
        // 先定义会员信息0
        $todayuser = 0;
        $yesuser = 0;
        $monthuser = 0;
        $totaluser = 0;
        if ($res5) {
            $res5 = collection($res5)->toArray();
            // 计算
            foreach ($res5 as $key => $value) {
                if ($value['created_at'] >= $todaytime && $value['created_at'] < $endtime) {
                    $todayuser += 1;
                }
                if ($value['created_at'] >= $starttime && $value['created_at'] < $todaytime) {
                    $yesuser += 1;
                }
                if ($value['created_at'] >= $time && $value['created_at'] < $nowtime) {
                    $monthuser += 1;
                }
                $totaluser += 1;
            }
        }
        $this->assign('todayuser', $todayuser);
        $this->assign('yesuser', $yesuser);
        $this->assign('monthuser', $monthuser);
        $this->assign('totaluser', $totaluser);
        return $this->fetch();
    }

    /**
     * 修改管理员密码功能
     *@return ary 修改结果
     */
    public function passwordUpdate(Request $request)
    {
        $adminId = intval($request->param('admin_id'));
        $oriPwd = $request->param('password');
        $newPwd = $request->param('password1');
        $admin = new Admin;
        $pwd = $admin->where('admin_id', $adminId)->value('password');
        if ($oriPwd != $pwd) {
            return objReturn(400, '初始密码错误！');
        } else {
            $where['admin_id'] = $adminId;
            $where['password'] = $newPwd;
            // 调用公共函数，参数true为更新
            $update = saveData('admin', $where, true);
            if ($update) {
                return objReturn(0, '修改成功');
            } else {
                return objReturn(400, '修改失败');
            }
        }
    }
}
