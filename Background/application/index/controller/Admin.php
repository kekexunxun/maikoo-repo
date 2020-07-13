<?php 
namespace  app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Db;
use \think\Session;

use app\index\model\Admin as AdminDb;
use app\index\model\Menu;
use app\index\model\Power;

class Admin extends Controller{

	/**
	 * adminlist 管理员管理页面
	 * @return   ary     数据
	 */
	public function adminlist(){
		$admin = new AdminDb;
		$adminData = $admin -> field('id,name,create_time,is_active')->where('id','<>',1) ->where('is_delete',0)->select();
		$this->assign('adminData',$adminData);
		return $this->fetch();
	}

	/**
	 * startAdmin 展示
	 * @param    Request    $request 参数
	 * @return   ary                 返回值
	 */
    public function startAdmin(Request $request){       
        $where['id'] = $request->param('id');
        $where['is_active'] = 1;
       // 调用公共函数，参数true为更新
        $update = saveData('admin',$where,true);
        if($update){
            return objReturn(0,'启用成功');
        }else{
            return objReturn(400,'启用失败');
        }  
    }

	/**
	 * stopAdmin 不展示

	 * @param    Request    $request 参数
	 * @return   ary                 返回值
	 */
    public function stopAdmin(Request $request){
        $where['id'] = $request->param('id');
        $where['is_active'] = 0;
       // 调用公共函数，参数true为更新
        $update = saveData('admin',$where,true);
        if($update){
            return objReturn(0,'停用成功');
        }else{
            return objReturn(400,'停用失败');
        }
    }

	/**
	 * delAdmin 删除
	 * @param    Request    $request 参数
	 * @return   ary                 返回值
	 */
    public function delAdmin(Request $request){     
        $del['id'] = $request->param('id');
        $del['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $delete = saveData('admin',$del,true);
        if($delete){
            return objReturn(0,'删除成功');
        }else{
            return objReturn(400,'删除失败');
        }
    }

	/**
	 * adminadd 添加最高管理员
	 * @return   html     页面
	 */
	public function adminadd(){
		return $this->fetch();
	}

	/**
	 * power 权限列表
	 * @param    Request    $request 参数
	 * @return   ary               ztree数据
	 */
	public function power(Request $request){
        $menu = new Menu();
        $menuList = $menu -> field('id,parent_id,name') ->where('is_admin',0) ->select();
        $menuAry = array();
        // 构造ztree数据
        foreach ($menuList as $key => $value) {
            $ary=array(
                'id'=>$value['id'],
                'pId'=>$value['parent_id'],
                'name'=>$value['name'],
                'open'=>"true",
                'checked'=>"false"
            );
            array_push($menuAry, $ary);
        }
        return json($menuAry);		
	}

	/**
	 * selectPower 权限id
	 * @param    Request    $request 参数
	 * @return   ary               返回值
	 */
	public function selectPower(Request $request){
        $menuid = $request->param('menuid');
        // 去除逗号
       	$menuid = rtrim($menuid, ',');
        // 是否存在session
        if(Session::has('menuid')){
            // 删除session信息
            Session::delete('menuid');            
        }
        // 存menuid到session
        Session::set('menuid',$menuid);
        if(Session::has('menuid')){
        	return objReturn(0,'权限信息保存成功！');
        }else{
        	return objReturn(400,'权限信息保存失败！');
        }
	}

	/**
	 * addAdmin 添加管理员
	 * @param    Request    $request 参数
	 * @return   ary               返回值
	 */
	public function addAdmin(Request $request){
		$add['name'] = $request->param('admin_name');
		$add['password'] = $request->param('password');
		$add['is_active'] = intval($request -> param('admin_active'));
		$add['create_time'] = time();
 		$admin = new AdminDb;
 		// 先验证用户名是否重复
 		$res = $admin ->where('name',$add['name'])->where('is_delete',0)->find();
 		if(!$res){
	        // 是否存在session
	        if(Session::has('menuid')){
	        	// 先更新admin表获得admin_id
		        $admin_id = $admin -> insertGetId($add);
				if($admin_id){
					// 取session数据
					$menuid = Session::get('menuid');
					$power['admin_id'] = $admin_id;
					$power['menu_id'] = $menuid;
			        // 新增权限信息
				    // 调用公共函数保存，参数false为新增
		            $result  = saveData('power',$power,false);
			        if($result){
	        			return objReturn(0,'保存成功！');
			        }else{
	        			return objReturn(400,'保存失败！');
			        }
				}
	        	return objReturn(400,'保存失败！');
	        }
        	return objReturn(400,'保存失败,未勾选权限信息！');
        }
        	return objReturn(400,'保存失败,名称重复，请输入新名称！');
	}

	/**
	 * adminedit 修改管理员界面
	 * @return   ary     数据
	 */
	public function adminedit(){
    	$request = Request::instance();
        $admin_id = intval($request -> param('admin_id'));
        $admin = new AdminDb;
        $adminData = $admin ->field('id,name,is_active') ->where('id',$admin_id)->where('is_delete',0)->find();
        $this->assign('adminData',$adminData);
		return $this->fetch();
	}

	/**
	 * prePower 原先的权限
	 * @return   ary     数据
	 */
	public function prePower(Request $request){
		$admin_id = intval($request -> param('admin_id'));
		// menu表数据
        $menu = new Menu;
        $menuList = $menu -> field('id,parent_id,name') ->where('is_admin',0) ->select();
        $menuList = collection($menuList) -> toArray();
        // power表数据
        $power = new Power;
        $powerList = $power ->field('id,admin_id,menu_id')->where('admin_id',$admin_id) ->select();
        $powerList = collection($powerList) -> toArray();
        if($powerList){
            $menuId = $powerList[0]['menu_id'];
		   // 对字符串处理转为数组
		    $ary = explode(',',$menuId);
			// 组成新数组
			foreach ($ary as $key => $value) {
				$powerArr[] = array('admin_id'=>$admin_id,'menu_id'=>$value);
			}       
        }
        // dump($powerArr);die;
        // 构造ztree数据
        $menuAry = array();
	    foreach ($menuList as $key => $value) {
	    	$temp['id'] = $value['id'];
	        $temp['pId'] = $value['parent_id'];
	        $temp['name'] = $value['name'];
	        $temp['open'] = true;
	        $temp['checked'] = false;
        	foreach ($powerArr as $k => $v) {
        		if($value['id'] == $v['menu_id']){
					$temp['checked'] = true;
					break 1;     			
        		}
	        }
        	$menuAry[] = $temp;
        }
        return json($menuAry);		
	}

	/**
	 * editAdmin 修改管理员
	 * @param    Request    $request 参数
	 * @return   ary               返回值
	 */
	public function editAdmin(Request $request){	
		$admin_id = intval($request -> param('admin_id'));
		$admin['name'] = $request->param('admin_name');
		$oldPwd = $request->param('password');
		$newPwd = $request->param('password1');
		$admin['is_active'] = intval($request -> param('admin_active'));
		$admindata = new AdminDb;
 		// 先验证用户名是否重复
 		$res = $admindata ->field('name')->where('name',$admin['name'])->where('is_delete',0)->where('id',$admin_id)->find();
 		if(!$res){
			// 原密码判断
			$res = $admindata ->where('id',$admin_id) ->where('password',$oldPwd) ->where('is_delete',0) ->find();
			if(!empty($res)){
				$admin['password'] = $newPwd;
			    // 是否存在session
			    if(Session::has('menuid')){
					// 取session数据
					$menuid = Session::get('menuid');
					$where['menu_id'] = $menuid;
			        // 更新权限信息
			        $power = new Power;
					$result = $power -> where('admin_id', $admin_id)->update($where);
					// if(!$result){
	    //     			return objReturn(400,'权限信息更新失败！');	
					// }
		            // 删除session信息
		            Session::delete('menuid');					
	    		} 	
	    			$admin['id'] = $admin_id;			
			        // 调用公共函数保存，参数true为更新
			        $update = saveData('admin',$admin,true);	  
			        if($update){
						return objReturn(0,'修改成功！');
			        }else{
						return objReturn(400,'修改失败！');
			        }
			}else{
				return objReturn(400,'修改失败，初始密码错误！');
			}
		}
        	return objReturn(400,'保存失败,名称重复，请输入新名称！');
	}
}