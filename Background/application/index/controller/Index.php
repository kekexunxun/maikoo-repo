<?php

namespace  app\index\controller;

use \think\Controller;

use \think\Request;

use \think\Cache;

use \think\Db;

use \think\Session;

use \think\File;

use app\index\model\Power;

use app\index\model\Admin;

use app\index\model\Userinfo;

use app\index\model\Minipro;


class Index extends Controller

{

   /**
     * @return 登录界面
     */
    public function login(){
        // Session::delete('loginname');
        Session::clear();
        return $this->fetch();
    }

    /**
     * @return 退出登录界面
     */   
    public function logout(){
        Session::delete('loginname');
        $url = 'http://minipro.up.maikoo.cn/index/login';
        $this->redirect($url);
    }

    /**
     * [checkLogin 确认登录信息]
     * @Author   Mr.fang
     * @DateTime 2018-07-11
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function checkLogin(Request $request){
        $username = $request->param('username');
        $password = md5($request->param('password'));
        $admin = new Admin;
        $res = $admin -> where('name',$username) ->select();
        if(empty($res)){
            return objReturn(100,'账号不存在！');
        }else{
            $result = $admin -> where('name',$username) ->where('is_active',0) ->find();
            if($result){
                return objReturn(400,'账号未启用！');
            }else{
                $result = $admin ->where('name',$username)-> where('password',$password) -> find();
                if($result){
                    // 存登录名到全局session
                    Session::set('loginname',$username);
                    return objReturn(200,'登录成功！');
                }else{
                    return objReturn(300,'密码错误！');    
                }                
            }
        }
    }

    /**
     * @return 主页界面
     */
    public function index(){
        // 判断是否存在session
        if(!Session::has('loginname')){
            header("Location: http://minipro.up.maikoo.cn/index/login");
        }else{
            $username = Session::get('loginname');
            $this->assign("username", $username);
            $admin = new Admin;
            $admin_id = $admin -> where('name',$username) -> where('is_delete',0) -> value('id');
            if($admin_id!=''){
                // 根据id找菜单的id
                $power = new Power();
                $menuList = $power -> field('menu_id') -> where('admin_id',$admin_id) -> select();
                $this->assign("menuList", $menuList);           
            }
            return $this->fetch();
        }
    }

    /**
     * @return 我的桌面界面
     */
    public function welcome(){
        $userinfo = new Userinfo;
        $minipro = new Minipro;
        $starttime = strtotime(date("Y-m-d 00:00:00")); //当天时间戳
        $endtime = strtotime(date('Y-m-d', strtotime('+1 days'))); //当天结束
        // 今日新增用户
        $todayUser = $userinfo -> field('user_id') ->where('create_time', 'between',[$starttime,$endtime]) -> count();
        $this->assign('todayUser',$todayUser);  
        // 今日新增小程序
        $todayMinipro = $minipro -> field('mini_id') ->where('create_time', 'between',[$starttime,$endtime]) ->where('is_delete',0)-> count();
        $this->assign('todayMinipro',$todayMinipro);         
        return $this->fetch();
    }

}
?>