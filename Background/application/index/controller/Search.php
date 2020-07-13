<?php 
namespace  app\index\controller;

use \think\Controller;

use \think\Request;

use \think\Cache;

use \think\Db;

use \think\Session;

use \think\File;

use app\index\model\Search as searchdata;

class Search extends Controller{

    /**
     * @return searchlist 页面
     */
    public function searchlist(){
        $field = 'mini_id,orderby,idx,is_active';
        $searchdata = getSearchList($field);
        // dump($searchdata);die;
        $this->assign('searchdata',$searchdata);
        return $this->fetch();
    }

    /**
     * 获取当前search id
     * @return json search 启用结果
     */
    public function searchStart(Request $request){       
        if ($request->isPost()) {
            $where['idx'] = $request->param('id');
            $where['is_active'] = 1;
           // 调用公共函数，参数true为更新
            $update = saveData('search',$where,true);
            if($update){
                return objReturn(200,'启用成功');
            }else{
                return objReturn(300,'启用失败');
            }  
        }
    }

    /**
     * 获取当前search id
     * @return json search 停用结果
     */
    public function searchStop(Request $request){
        $where['idx'] = $request->param('id');
        $where['is_active'] = 0;
       // 调用公共函数，参数true为更新
        $update = saveData('search',$where,true);
        if($update){
            return objReturn(200,'停用成功');
        }else{
            return objReturn(300,'停用失败');
        }
    }

    /**
     * 获取当前search id
     * @return json 删除search结果
     */
    public function searchDel(Request $request){     
        $del['idx'] = $request->param('id');
        $del['delete_time'] = time();
        $del['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $update = saveData('search',$del,true);
        if($update){
            return objReturn(200,'删除成功');
        }else{
            return objReturn(300,'删除失败');
        }
    }

    /**
     * @return 添加search界面
     */
    public function searchadd(){  
    	// 调用公共函数
        $miniprodata = getAllMini('mini_id,name',true);
        $this->assign('miniprodata',$miniprodata);  
        return $this->fetch();
    } 

    /**
     * 添加搜索关键词
     * @return json addSearch 结果
     */
    public function addSearch(Request $request){
    	$where['mini_id'] = intval($request->param('mini_id'));
    	$where['is_active'] = intval($request->param('active'));
    	$where['orderby'] = intval($request->param('orderby'));
       // 调用公共函数，参数false为新增
        $insert = saveData('search',$where,false);
        if($insert){
            return objReturn(200,'添加成功');
        }else{
            return objReturn(300,'添加失败');
        }
    }

    /**
     * @return 修改search界面
     */
	// public function searchedit(){
 //        $request = Request::instance();
 //        $search_id = intval($request -> param('search_id'));
 //        $this->assign('search_id',$search_id);
 //        $search = new searchdata;
 //        $mini_id = $search ->where('idx',$search_id) ->value('mini_id');
	// 	// 调用公共函数
 //        $miniprodata = getAllMini('mini_id,name',true);
 //        $this->assign('miniprodata',$miniprodata);
 //        // 调用公共函数
 //        $field = 'mini_id,name,is_active,catagory_id';
 //        $search = getMiniById($mini_id,$field,true);
 //        dump($search);die;
 //        $this->assign('search',$search);
 //        return $this->fetch();
	// }

    /**
     * 修改搜索关键词
     * @return json editSearch 结果
     */
    public function editSearch(Request $request){
        $where['idx'] = intval($request -> param('idx'));
    	// $where['mini_id'] = intval($request->param('mini_id'));
    	// $where['is_active'] = intval($request->param('active'));
    	$where['orderby'] = intval($request->param('orderby'));
        // dump($where['idx']);
        // dump($where['orderby']);die;
       // 调用公共函数，参数true为更新
        $update = saveData('search',$where,true);
        if($update){
            return objReturn(200,'修改成功');
        }else{
            return objReturn(300,'修改失败');
        }        
    }
}