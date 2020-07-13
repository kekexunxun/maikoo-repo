<?php 
namespace  app\index\controller;

use \think\Controller;

use \think\Request;

use \think\Cache;

use \think\Db;

use \think\Session;

use \think\File;

use app\index\model\Admin as admindata;

use app\index\model\Menu;

use app\index\model\Power;

use app\index\model\Mini_click_count;

use app\index\model\Catagory;

class Admin extends Controller{

	/**
	 * [adminlist 管理员管理页面]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-11
	 * @version  V1.0.0
	 * @return   [ary]     [数据]
	 */
	public function adminlist(){
		$admin = new admindata;
		$adminData = $admin -> field('id,name,create_time,is_active')->where('id','<>',10) ->where('is_delete',0)->select();
		$this->assign('adminData',$adminData);
		return $this->fetch();
	}

	/**
	 * [startAdmin 展示]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-11
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   ary                 [返回值]
	 */
    public function startAdmin(Request $request){       
        $where['id'] = $request->param('id');
        $where['is_active'] = 1;
       // 调用公共函数，参数true为更新
        $update = saveData('admin',$where,true);
        if($update){
            return objReturn(200,'启用成功');
        }else{
            return objReturn(300,'启用失败');
        }  
    }

	/**
	 * [stopAdmin 不展示]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-11
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   ary                 [返回值]
	 */
    public function stopAdmin(Request $request){
        $where['id'] = $request->param('id');
        $where['is_active'] = 0;
       // 调用公共函数，参数true为更新
        $update = saveData('admin',$where,true);
        if($update){
            return objReturn(200,'停用成功');
        }else{
            return objReturn(300,'停用失败');
        }
    }

	/**
	 * [delAdmin 删除]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-11
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   ary                 [返回值]
	 */
    public function delAdmin(Request $request){     
        $del['id'] = $request->param('id');
        $del['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $delete = saveData('admin',$del,true);
        if($delete){
            return objReturn(200,'删除成功');
        }else{
            return objReturn(300,'删除失败');
        }
    }


	/**
	 * [adminadd 添加最高管理员]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-11
	 * @version  V1.0.0
	 * @return   [html]     [页面]
	 */
	public function adminadd(){
		return $this->fetch();
	}

	/**
	 * [power 权限列表]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-11
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   [ary]               [ztree数据]
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
                'open'=>true,
                'checked'=>false
            );
            array_push($menuAry, $ary);
        }
        return json($menuAry);		
	}

	/**
	 * [selectPower 权限id]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-11
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   [ary]               [返回值]
	 */
	public function selectPower(Request $request){
        $menuid = $request->param('menuid');
        // 是否存在session
        if(Session::has('menuid')){
            // 删除session信息
            Session::delete('menuid');            
        }
        // 存menuid到session
        Session::set('menuid',$menuid);

        if(Session::has('menuid')){
        	return objReturn(200,'权限信息保存成功！');
        }else{
        	return objReturn(300,'权限信息保存失败！');
        }
	}

	/**
	 * [addAdmin 添加管理员]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-11
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   [ary]               [返回值]
	 */
	public function addAdmin(Request $request){
		$add['name'] = $request->param('admin_name');
		$add['password'] = md5($request->param('password'));
		$add['is_active'] = intval($request -> param('admin_active'));
		$add['create_time'] = time();
 		$admin = new admindata;
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
			        // 对字符串处理转为数组
			        $menuArr = explode(',',$menuid,-1);
			        // dump($menuArr);die;
			        // 构造数据写入power表
			        $update = array();
			        $temp = [];
			        foreach ($menuArr as $key => $value) {
			        	$temp['admin_id'] = $admin_id;
			            $temp['menu_id'] = $menuArr[$key];
			            $update[] = $temp;
			        }
					$power = new Power;
			        // 新增权限信息
			        $result = $power ->saveAll($update);
			        if($result){
	        			return objReturn(200,'保存成功！');
			        }else{
	        			return objReturn(300,'保存失败！');
			        }
				}
	        	return objReturn(300,'保存失败！');
	        }
        	return objReturn(300,'保存失败,未勾选权限信息！');
        }
        	return objReturn(300,'保存失败,名称重复，请输入新名称！');
	}

	/**
	 * [adminedit 修改管理员界面]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-11
	 * @version  V1.0.0
	 * @return   [ary]     [数据]
	 */
	public function adminedit(){
    	$request = Request::instance();
        $admin_id = intval($request -> param('admin_id'));
        $admin = new admindata;
        $adminData = $admin ->field('id,name,is_active') ->where('id',$admin_id)->where('is_delete',0)->find();
        $this->assign('adminData',$adminData);
		return $this->fetch();
	}


	public function prePower(Request $request){
		$admin_id = intval($request -> param('admin_id'));
        $menu = new Menu();
        $menuList = $menu -> field('id,parent_id,name') ->where('is_admin',0) ->select();

        // power表数据
        $power = new Power;
        $powerList = $power ->field('id,admin_id,menu_id')->where('admin_id',$admin_id) ->select();
        // 构造ztree数据
        // $menuAry = array();
        // $temp = [];
        // foreach ($menuList as $key => $value) {
        // 	foreach ($powerList as $k => $v) {
        // 		if($value['id'] == $v['menu_id']){
		      //       $ary=array(
		      //           'id'=>$value['id'],
		      //           'pId'=>$value['parent_id'],
		      //           'name'=>$value['name'],
		      //           'open'=>true,
		      //           'checked'=>true
		      //       );        			
        // 		}else{
		      //       $ary=array(
		      //           'id'=>$value['id'],
		      //           'pId'=>$value['parent_id'],
		      //           'name'=>$value['name'],
		      //           'open'=>true,
		      //           'checked'=>false
		      //       );	
        // 		}
        // 	}
        // 	array_push($menuAry, $ary);
        // }
        // dump($menuAry);die;


        $menuAry = array();
        foreach ($menuList as $key => $value) {
            $ary=array(
                'id'=>$value['id'],
                'pId'=>$value['parent_id'],
                'name'=>$value['name'],
                'open'=>true,
                'checked'=>false
            );
            array_push($menuAry, $ary);
        }
        return json($menuAry);		
	}

	/**
	 * [editAdmin 修改管理员]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-11
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   [ary]               [返回值]
	 */
	public function editAdmin(Request $request){
		$admin_id = intval($request -> param('admin_id'));
		$admin['name'] = $request->param('admin_name');
		$oldPwd = md5($request->param('password'));
		$newPwd = md5($request->param('password1'));
		$admin['is_active'] = intval($request -> param('admin_active'));
		$admindata = new admindata;
 		// 先验证用户名是否重复
 		$res = $admindata ->field('name')->where('name',$admin['name'])->where('is_delete',0)->find();
 		if(!$res){
		    // 是否存在session
		    if(Session::has('menuid')){
				// 取session数据
				$menuid = Session::get('menuid');
		        // 对字符串处理转为数组
		        $menuArr = explode(',',$menuid,-1);
		        // dump($menuArr);die;
		        // 构造数据写入power表
		        $update = array();
		        $temp = [];
		        foreach ($menuArr as $key => $value) {
		        	$temp['admin_id'] = $admin_id;
		            $temp['menu_id'] = $menuArr[$key];
		            $update[] = $temp;
		        }
				$power = new Power;
		        // 新增权限信息
		        $result = $power ->saveAll($update);
		    }
			// 密码判断
			$res = $admindata ->where('id',$admin_id) ->where('password',$oldPwd) ->where('is_delete',0) ->find();
			if(!empty($res)){
				$admin['password'] = $newPwd;
		        // 调用公共函数保存，参数true为更新
		        $update = saveData('admin',$admin,true);	  
		        if($update){
					return objReturn(200,'修改成功！');
		        }else{
					return objReturn(300,'修改失败！');
		        }
			}else{
					return objReturn(300,'修改失败，初始密码错误！');
			}
		}
        	return objReturn(300,'保存失败,名称重复，请输入新名称！');
	}

	/**
	 * [clickset 自定义点击量页面]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-12
	 * @version  V1.0.0
	 * @return   [ary]     [返回值]
	 */
	public function clickset(){
		$click_count = new Mini_click_count;
		//给要关联的表取别名,并让两个值关联
        $clicksetdata = $click_count -> alias('a') -> join('minipro w','a.mini_id = w.mini_id','left') ->field('a.idx,a.clicks,a.is_enter,a.is_enable,w.name,w.avatarUrl')->where('a.mini_appid','system')->where('a.is_delete',0)->where('w.is_delete',0)->select();
        $this->assign('clicksetdata',$clicksetdata);
		return $this->fetch();
	}

	/**
	 * [clicksetadd 自定义点击量添加页面]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-12
	 * @version  V1.0.0
	 * @return   [ary]     [返回值]
	 */
	public function clicksetadd(){
		// $click_count = new Mini_click_count;
		// $clicksetdata = $click_count ->field('mini_id') ->select();
		// 调用公共函数
        $miniprodata = getAllMini('mini_id,name',false);
        $this->assign('miniprodata',$miniprodata);
        // 小程序分类
        $catagory = new Catagory;
        $catagorydata = $catagory ->field('catagory_id,name') ->where('father_id','<>',0)->where('is_active',1) ->where('is_delete',0) ->select(); 
        $this->assign('catagorydata',$catagorydata);
		return $this->fetch();
	}

	/**
	 * [addClickset 添加]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-12
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   [ary]     [返回值]
	 */
	public function addClickset(Request $request){
		$num = intval($request -> param('num'));
		$enable = intval($request -> param('clickset_enable'));
		$miniId = intval($request -> param('mini_id'));
		$clicks = intval($request -> param('clickset_num'));
		$clicksetDetail = intval($request -> param('clickset_detail'));
		$clickset_time = strtotime($request -> param('clickset_time'));
		$mini_click_count = new Mini_click_count;
		// dump($enable);die;
		// 选择指定小程序
		if($num == 0){
				$where['create_time'] = $clickset_time;				
				$add['create_time'] = $clickset_time;				
			// 详情页
			if($clicksetDetail == 0){
				$where['mini_id'] = $miniId;
				$where['clicks'] = $clicks;				
				$where['is_enable'] = $enable;				
				$where['mini_appid'] = 'system';				
				$where['is_enter'] = $clicksetDetail;				
				$where['click_time'] = $clickset_time;				
			}
			// 跳转
			if($clicksetDetail == 1){
				$where['mini_id'] = $miniId;
				$where['clicks'] = $clicks;
				$where['is_enable'] = $enable;
				$where['mini_appid'] = 'system';												
				$where['is_enter'] = $clicksetDetail;
				$where['click_time'] = $clickset_time;								
			}
			// 全部
			if($clicksetDetail == 2){
				$where['mini_id'] = $miniId;
				$where['clicks'] = $clicks;
				$where['is_enable'] = $enable;
				$where['mini_appid'] = 'system';												
				$where['is_enter'] = 1;
				$where['click_time'] = $clickset_time;				

				$add['mini_id'] = $miniId;
				$add['clicks'] = $clicks;
				$add['is_enable'] = $enable;
				$add['mini_appid'] = 'system';												
				$add['is_enter'] = 0;
				$add['click_time'] = $clickset_time;				
		       // 调用公共函数，参数false为新增
		        $add = saveData('mini_click_count',$add,false);
		        if($add){
		        	$isAdd = 1;
		        }
		        $isAdd = 0;												
			}
			if(isset($isAdd)){
				if($isAdd==1){
					$where['click_time'] = $clickset_time;				
			       // 调用公共函数，参数false为新增
			        $insert = saveData('mini_click_count',$where,false);
			        if($insert){
			            return objReturn(200,'添加成功');
			        }else{
			            return objReturn(300,'添加失败');
			        }					
				}
			    	return objReturn(300,'添加失败');
			}
			$where['click_time'] = $clickset_time;				
	       // 调用公共函数，参数false为新增
	        $insert = saveData('mini_click_count',$where,false);
	        if($insert){
	            return objReturn(200,'添加成功');
	        }else{
	            return objReturn(300,'添加失败');
	        }
		}
		// 选择分类的小程序
		if($num == 1){
			$catagoryId = $miniId;
	        // 引用公共函数-通过分类ID获取小程序详情
		    $field = 'catagory_id,father_id,name';
		    $miniField = 'mini_id,name,avatarUrl,is_active,catagory_id';
		    $res = getCatagoryById($catagoryId,$field,$miniField,true);
    		$res = collection($res) -> toArray();
    		// 非空判断
    		if(!empty($res)){
    	      	$result = $res[0]['minis'];
				// dump($result);die;
				// 详情页
				if($clicksetDetail == 0){
		      		// 构造数据写入表
		      		$add = [];
		      		foreach ($result as $key => $value) {
		      			// 已经启用的小程序
		      			if($value['is_active']==1){
		      				$where['mini_id'] = $value['mini_id'];
							$where['clicks'] = $clicks;
							$where['is_enable'] = $enable;
							$where['mini_appid'] = 'system';												
							$where['is_enter'] = $clicksetDetail;
							$where['click_time'] = $clickset_time;
							$where['create_time'] = $clickset_time;				
							$add[] = $where;
		      			}
		      		}
		      		// 批量保存
			        $insert = $mini_click_count ->saveAll($add,false);
			        if($insert){
			            return objReturn(200,'添加成功');
			        }else{
			            return objReturn(300,'添加失败');
			        }					
				}
				// 跳转
				if($clicksetDetail == 1){
		      		// 构造数据写入表
		      		$add = [];
		      		foreach ($result as $key => $value) {
		      			// 已经启用的小程序
		      			if($value['is_active']==1){
		      				$where['mini_id'] = $value['mini_id'];
							$where['clicks'] = $clicks;
							$where['is_enable'] = $enable;
							$where['mini_appid'] = 'system';												
							$where['is_enter'] = $clicksetDetail;
							$where['click_time'] = $clickset_time;
							$where['create_time'] = $clickset_time;				
							$add[] = $where;
		      			}
		      		}
		      		// 批量保存
			        $insert = $mini_click_count ->saveAll($add,false);
			        if($insert){
			            return objReturn(200,'添加成功');
			        }else{
			            return objReturn(300,'添加失败');
			        }					
				}
				// 全部
				if($clicksetDetail == 2){
		      		// 构造数据写入表
		      		$add = [];
		      		foreach ($result as $key => $value) {
		      			// 已经启用的小程序
		      			if($value['is_active']==1){
		      				$where['mini_id'] = $value['mini_id'];
							$where['clicks'] = $clicks;
							$where['is_enable'] = $enable;
							$where['mini_appid'] = 'system';												
							$where['is_enter'] = 1;
							$where['click_time'] = $clickset_time;
							$where['create_time'] = $clickset_time;				

		      				$where2['mini_id'] = $value['mini_id'];
							$where2['clicks'] = $clicks;
							$where2['is_enable'] = $enable;
							$where2['mini_appid'] = 'system';												
							$where2['is_enter'] = 0;
							$where2['click_time'] = $clickset_time;
							$where2['create_time'] = $clickset_time;				

							$add[] = $where;		      				
							$add[] = $where2;		      				
		      			}
		      		}
		      		// 批量保存
			        $insert = $mini_click_count ->saveAll($add,false);
			        if($insert){
			            return objReturn(200,'添加成功');
			        }else{
			            return objReturn(300,'添加失败');
			        }
				}
    		}
		}
		// 选择全部
		if($num==2){
			// 调用公共函数
	        $miniprodata = getAllMini('mini_id,name',false);
	        // dump($miniprodata);die;
	        // 详情页
	        if($clicksetDetail == 0){
		        // 构造数据写入表
		        $add = [];
		        foreach ($miniprodata as $key => $value) {
	  				$where['mini_id'] = $value['mini_id'];
					$where['clicks'] = $clicks;
					$where['is_enable'] = $enable;
					$where['mini_appid'] = 'system';												
					$where['is_enter'] = 1;
					$where['click_time'] = $clickset_time;
					$where['create_time'] = $clickset_time;				
					$add[] = $where;
		        }
		        // dump($add);die;
	      		// 批量保存
		        $insert = $mini_click_count ->saveAll($add,false);
		        if($insert){
		            return objReturn(200,'添加成功');
		        }else{
		            return objReturn(300,'添加失败');
		        }		        	        	
	        }
			// 跳转
			if($clicksetDetail == 1){
	      		// 构造数据写入表
	      		$add = [];
	      		foreach ($miniprodata as $key => $value) {
	      			// 已经启用的小程序
	      			if($value['is_active']==1){
	      				$where['mini_id'] = $value['mini_id'];
						$where['clicks'] = $clicks;
						$where['is_enable'] = $enable;
						$where['mini_appid'] = 'system';												
						$where['is_enter'] = $clicksetDetail;
						$where['click_time'] = $clickset_time;
						$where['create_time'] = $clickset_time;				
						$add[] = $where;
	      			}
	      		}
	      		// 批量保存
		        $insert = $mini_click_count ->saveAll($add,false);
		        if($insert){
		            return objReturn(200,'添加成功');
		        }else{
		            return objReturn(300,'添加失败');
		        }					
			}
			// 全部
			if($clicksetDetail == 2){	
	      		// 构造数据写入表
	      		$add = [];
	      		foreach ($miniprodata as $key => $value) {
	      			// 已经启用的小程序
	      			if($value['is_active']==1){
	      				$where['mini_id'] = $value['mini_id'];
						$where['clicks'] = $clicks;
						$where['is_enable'] = $enable;
						$where['mini_appid'] = 'system';												
						$where['is_enter'] = 1;
						$where['click_time'] = $clickset_time;				
						$where['create_time'] = $clickset_time;				

	      				$where2['mini_id'] = $value['mini_id'];
						$where2['clicks'] = $clicks;
						$where2['is_enable'] = $enable;
						$where2['mini_appid'] = 'system';												
						$where2['is_enter'] = 0;
						$where2['click_time'] = $clickset_time;				
						$where2['create_time'] = $clickset_time;				

						$add[] = $where;		      				
						$add[] = $where2;		      				
	      			}
	      		}
	      		// 批量保存
		        $insert = $mini_click_count ->saveAll($add,false);
		        if($insert){
		            return objReturn(200,'添加成功');
		        }else{
		            return objReturn(300,'添加失败');
		        }
			}
		}		
	}

	/**
	 * [editClickset 修改]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-12
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   [ary]     [返回值]
	 */
	public function editClickset(Request $request){
		$where['idx'] = intval($request -> param('idx'));
		$where['clicks'] = intval($request -> param('clickset_num'));
		$where['click_time'] = time();
		// 调用公共函数，参数true为更新
        $update = saveData('mini_click_count',$where,true);
        if($update){
            return objReturn(200,'修改成功');
        }else{
            return objReturn(300,'修改失败');
        } 
	}

	/**
	 * [startClickset 启用]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-13
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   [ary]               [返回值]
	 */
    public function startClickset(Request $request){       
        $where['idx'] = $request->param('idx');
        $where['is_enable'] = 1;
       // 调用公共函数，参数true为更新
        $update = saveData('mini_click_count',$where,true);
        if($update){
            return objReturn(200,'启用成功');
        }else{
            return objReturn(300,'启用失败');
        }  
    }

    /**
     * [stopClickset 不启用]
     * @Author   Mr.fang
     * @DateTime 2018-07-13
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function stopClickset(Request $request){
        $where['idx'] = $request->param('idx');
        $where['is_enable'] = 0;
       // 调用公共函数，参数true为更新
        $update = saveData('mini_click_count',$where,true);
        if($update){
            return objReturn(200,'停用成功');
        }else{
            return objReturn(300,'停用失败');
        }
    }

    /**
     * [delClickset 删除]
     * @Author   Mr.fang
     * @DateTime 2018-07-13
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function  delClickset(Request $request){
        $del['idx'] = $request->param('idx');
        // $del['delete_time'] = time();
        $del['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $update = saveData('mini_click_count',$del,true);
        if($update){
            return objReturn(200,'删除成功');
        }else{
            return objReturn(300,'删除失败');
        }    	
    }	

}
?>