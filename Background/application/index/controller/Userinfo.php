<?php 
namespace  app\index\controller;

use \think\Controller;

use \think\Request;

use \think\Cache;

use \think\Db;

use \think\Session;

use \think\File;

use app\index\model\Userinfo as Userinfodata;

class Userinfo extends Controller{

    /**
     * @return userinfolist 界面
     */
    public function userinfolist(){
        $userinfolist = new Userinfodata();
        $userinfo = $userinfolist -> field('user_id') -> count();
        $this->assign('userinfo',$userinfo);
        return $this->fetch();
    }

    /**

     * 获取当前用户详情

     * @return json 详情

     */
    public function userDetail(){
    	$userinfolist = new Userinfodata();
   		$data = $userinfolist -> field('invite_code_id,nickName,avatarUrl,country,province,city,gender,is_auth,name,user_openid,identID,telNum') -> select();
    	return json($data);
    }    



}