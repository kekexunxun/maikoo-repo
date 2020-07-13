<?php 
namespace  app\index\controller;

use \think\Controller;

use \think\Request;

use \think\Cache;

use \think\Db;

use \think\Session;

use \think\File;

use app\index\model\Article as articledata;

use app\index\model\Article_cat;

class Article extends Controller{
	
	/**
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @return   页面     文章管理-测评管理
	 */
	public function evaluation(){
		$article = new articledata;
		$evaluationData = $article ->field('article_id,title,content,author,brief,pic,views,is_active')->where('type',0)->where('is_delete',0) ->select();
		$this->assign('evaluationData',$evaluationData);
		return $this->fetch();
	}
	
	/**
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @return   页面     文章管理-测评管理添加
	 */
	public function evaluationadd(){
		return $this->fetch();
	}

	/**
	 * [addEvaluation 新增测评]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.1
	 * @param    Request    $request [参数]
	 */
	public function addEvaluation(Request $request){
		$add['title'] = htmlspecialchars($request->param('evaluation_title'));
		$add['author'] = htmlspecialchars($request->param('evaluation_author'));
		$add['brief'] = htmlspecialchars($request->param('evaluation_brief'));
		$add['content'] = htmlspecialchars($request->param('evaluation_content'));
        $add['is_active'] = intval($request->param('evaluation_active'));
		$add['views'] = intval($request->param('evaluation_views'));
		$add['create_time'] = time();
        $add['type'] = 0;
        // 是否存在session
        if(Session::has('articleSrc')){

            $source = ROOT_PATH.Session::get('articleSrc');
            // dump($source);die;
            // 新的路径,取session值
            $str = substr_replace(Session::get('articleSrc'),'article',13,9);
            // 创建文件夹
            $str3 = substr($str,0,30);
            if(!file_exists(ROOT_PATH . $str3)){
                mkdir(ROOT_PATH . $str3); 
            }            
            // 框架应用根目录/public/minipro/目录
            $destination = ROOT_PATH.$str;
            // dump($destination);die;
            // 拷贝文件到指定目录
            $res = copy($source,$destination);
            // 移动成功
            if($res){
                $str = substr($str,6);
                $add['pic'] = $str; 
            }
            // 删除session信息
            Session::delete('articleSrc');
            // 调用公共函数，参数false为新增
            $insert = saveData('article',$add,false);
            if($insert){
                return objReturn(200,'新增成功');
            }else{
                return objReturn(300,'新增失败');
            }
        }
            return objReturn(300,'新增失败，请上传图片！');
	}

	/**
	 * [startEvaluation 展示]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   ary                 [返回值]
	 */
    public function startEvaluation(Request $request){       
        $where['article_id'] = $request->param('id');
        $where['is_active'] = 1;
       // 调用公共函数，参数true为更新
        $update = saveData('article',$where,true);
        if($update){
            return objReturn(200,'启用成功');
        }else{
            return objReturn(300,'启用失败');
        }  
    }

	/**
	 * [stopEvaluation 不展示]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   ary                 [返回值]
	 */
    public function stopEvaluation(Request $request){
        $where['article_id'] = $request->param('id');
        $where['is_active'] = 0;
       // 调用公共函数，参数true为更新
        $update = saveData('article',$where,true);
        if($update){
            return objReturn(200,'停用成功');
        }else{
            return objReturn(300,'停用失败');
        }
    }

	/**
	 * [delEvaluation 删除]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   ary                 [返回值]
	 */
    public function delEvaluation(Request $request){     
        $del['article_id'] = $request->param('id');
        $del['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $delete = saveData('article',$del,true);
        if($delete){
            return objReturn(200,'删除成功');
        }else{
            return objReturn(300,'删除失败');
        }
    }

	/**
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @return   页面     文章管理-测评管理修改
	 */
	public function evaluationedit(){
    	$request = Request::instance();
        $article_id = intval($request -> param('evaluation_id'));	
		$article = new articledata;
		$evaluationData = $article ->field('article_id,title,content,author,brief,pic,views,is_active')->where('article_id',$article_id)->where('is_delete',0)->select();
		// dump($evaluationData);die;
		$evaluationData = $evaluationData[0];
		$this->assign('evaluationData',$evaluationData);        
		return $this->fetch();
	}    

    /**
     * [editEvaluation 修改测评]
     * @Author   Mr.fang
     * @DateTime 2018-07-02
     * @version  V1.0.1
     * @return   [ary]     [返回值]
     */
    public function editEvaluation(){
    	$request = Request::instance();
        $update['article_id'] = intval($request -> param('evaluation_id'));
		$update['title'] = htmlspecialchars($request->param('evaluation_title'));
		$update['author'] = htmlspecialchars($request->param('evaluation_author'));
		$update['brief'] = htmlspecialchars($request->param('evaluation_brief'));
		$update['content'] = htmlspecialchars($request->param('evaluation_content'));
		$update['is_active'] = intval($request->param('evaluation_active'));
        $update['views'] = intval($request->param('evaluation_views'));
        // 是否存在session
        if(Session::has('articleSrc')){

            $source = ROOT_PATH.Session::get('articleSrc');
            // dump($source);die;
            // 新的路径,取session值
            $str = substr_replace(Session::get('articleSrc'),'article',13,9);
            // 创建文件夹
            $str3 = substr($str,0,30);
            if(!file_exists(ROOT_PATH . $str3)){
                mkdir(ROOT_PATH . $str3); 
            }            
            // 框架应用根目录/public/minipro/目录
            $destination = ROOT_PATH.$str;
            // dump($destination);die;
            // 拷贝文件到指定目录
            $res = copy($source,$destination);
            // 移动成功
            if($res){
                $str = substr($str,6);
                $update['pic'] = $str; 
            }
            // 删除session信息
            Session::delete('articleSrc');
        }
        // 调用公共函数，参数true为更新
        $update = saveData('article',$update,true);        
        if($update){
            return objReturn(200,'修改成功！');
        }else{
            return objReturn(300,'修改失败！');
        }      
    }

	/**
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @return   页面     文章管理-资讯管理
	 */
	public function info(){
		$article = new articledata;
		$infoData = $article ->field('article_id,title,content,author,brief,pic,views,is_active,cat_id')->where('type',1)->where('is_delete',0) ->select();
		$this->assign('infoData',$infoData);
		return $this->fetch();
	}
	
	/**
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @return   页面     文章管理-测评管理添加
	 */
	public function infoadd(){
        $article_cat = new Article_cat;
        $infosortData = $article_cat -> field('cat_id,name')->where('father_id','<>',0)->where('is_delete',0) ->select();
        // dump($infosortData);die;
        $this->assign('infosortData',$infosortData);        
		return $this->fetch();
	}

	/**
	 * [addinfo 新增资讯]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 */
	public function addInfo(Request $request){
		$add['title'] = htmlspecialchars($request->param('info_title'));
		$add['author'] = htmlspecialchars($request->param('info_author'));
		$add['brief'] = htmlspecialchars($request->param('info_brief'));
		$add['content'] = htmlspecialchars($request->param('info_content'));
		$add['is_active'] = intval($request->param('info_active'));
        $add['views'] = intval($request->param('info_views'));
		$add['cat_id'] = intval($request->param('info_catagory'));
        $add['create_time'] = time();
		$add['type'] = 1;
        // 是否存在session
        if(Session::has('articleSrc')){

            $source = ROOT_PATH.Session::get('articleSrc');
            // dump($source);die;
            // 新的路径,取session值
            $str = substr_replace(Session::get('articleSrc'),'article',13,9);
            // 创建文件夹
            $str3 = substr($str,0,30);
            if(!file_exists(ROOT_PATH . $str3)){
                mkdir(ROOT_PATH . $str3); 
            }            
            // 框架应用根目录/public/minipro/目录
            $destination = ROOT_PATH.$str;
            // dump($destination);die;
            // 拷贝文件到指定目录
            $res = copy($source,$destination);
            // 移动成功
            if($res){
                $str = substr($str,6);
                $add['pic'] = $str; 
            }
            // 删除session信息
            Session::delete('articleSrc');
            // 调用公共函数，参数false为新增
            $insert = saveData('article',$add,false);
            if($insert){
                return objReturn(200,'新增成功');
            }else{
                return objReturn(300,'新增失败');
            }  
        }
            return objReturn(300,'新增失败，请上传图片！');
	}

	/**
	 * [startInfo 展示]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   ary                 [返回值]
	 */
    public function startInfo(Request $request){       
        $where['article_id'] = $request->param('id');
        $where['is_active'] = 1;
       // 调用公共函数，参数true为更新
        $update = saveData('article',$where,true);
        if($update){
            return objReturn(200,'启用成功');
        }else{
            return objReturn(300,'启用失败');
        }  
    }

	/**
	 * [stopInfo 不展示]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   ary                 [返回值]
	 */
    public function stopInfo(Request $request){
        $where['article_id'] = $request->param('id');
        $where['is_active'] = 0;
       // 调用公共函数，参数true为更新
        $update = saveData('article',$where,true);
        if($update){
            return objReturn(200,'停用成功');
        }else{
            return objReturn(300,'停用失败');
        }
    }

	/**
	 * [delInfo 删除]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   ary                 [返回值]
	 */
    public function delInfo(Request $request){     
        $del['article_id'] = $request->param('id');
        $del['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $delete = saveData('article',$del,true);
        if($delete){
            return objReturn(200,'删除成功');
        }else{
            return objReturn(300,'删除失败');
        }
    }

	/**
	 * @Author   Mr.fang
	 * @DateTime 2018-07-02
	 * @version  V1.0.0
	 * @return   页面     文章管理-测评管理修改
	 */
	public function infoedit(){
    	$request = Request::instance();
        $article_id = intval($request -> param('info_id'));	
		$article = new articledata;
		$infoData = $article ->field('article_id,title,content,author,brief,pic,views,is_active,cat_id')->where('article_id',$article_id)->where('is_delete',0)->select();
		$infoData = $infoData[0];
		$this->assign('infoData',$infoData);
        $article_cat = new Article_cat;
        $infosortData = $article_cat -> field('cat_id,name')->where('father_id','<>',0)->where('is_delete',0) ->select();
        $this->assign('infosortData',$infosortData);                
		return $this->fetch();
	}    

    /**
     * [editInfo 修改测评]
     * @Author   Mr.fang
     * @DateTime 2018-07-02
     * @version  V1.0.0
     * @return   [ary]     [返回值]
     */
    public function editInfo(){
    	$request = Request::instance();
        $update['article_id'] = intval($request -> param('info_id'));
		$update['title'] = htmlspecialchars($request->param('info_title'));
		$update['author'] = htmlspecialchars($request->param('info_author'));
		$update['brief'] = htmlspecialchars($request->param('info_brief'));
		$update['content'] = htmlspecialchars($request->param('info_content'));
		$update['is_active'] = intval($request->param('info_active'));
        $update['cat_id'] = intval($request->param('info_catagory'));
        $update['views'] = intval($request->param('info_views'));
        // 是否存在session
        if(Session::has('articleSrc')){

            $source = ROOT_PATH.Session::get('articleSrc');
            // dump($source);die;
            // 新的路径,取session值
            $str = substr_replace(Session::get('articleSrc'),'article',13,9);
            // 创建文件夹
            $str3 = substr($str,0,30);
            if(!file_exists(ROOT_PATH . $str3)){
                mkdir(ROOT_PATH . $str3); 
            }            
            // 框架应用根目录/public/minipro/目录
            $destination = ROOT_PATH.$str;
            // dump($destination);die;
            // 拷贝文件到指定目录
            $res = copy($source,$destination);
            // 移动成功
            if($res){
                $str = substr($str,6);
                $update['pic'] = $str; 
            }
            // 删除session信息
            Session::delete('articleSrc');
        }
        // 调用公共函数，参数true为更新
        $update = saveData('article',$update,true);        
        if($update){
            return objReturn(200,'修改成功！');
        }else{
            return objReturn(300,'修改失败！');
        }      
    }

    /**
     * [infosort 资讯分类页面]
     * @Author   Mr.fang
     * @DateTime 2018-07-02
     * @version  V1.0.0
     * @return   [html]     [资讯分类页面]
     */
    public function infosort(){
    	$article_cat = new Article_cat;
    	$infosortData = $article_cat -> field('cat_id,name')->where('father_id',0)->where('is_delete',0) ->select();
    	$this->assign('infosortData',$infosortData);	
		return $this->fetch();
    }

    /**
     * [catagory 分类数据]
     * @Author   Mr.fang
     * @DateTime 2018-07-02
     * @version  V1.0.0
     * @return   [ary]     [返回值]
     */
    public function catagory(){
    	$article_cat = new Article_cat;
    	$data = $article_cat -> field('cat_id,father_id,name,is_active') ->where('is_delete',0) ->order('orderby desc')->select();
    	// 先进行排序 父级与子级
    	
    	// 返回ztree数据
        $catagoryArr = array();
        foreach ($data as $key => $value) {
            $ary=array(
                'id'=>$value['cat_id'],
                'pId'=>$value['father_id'],
                'name'=>$value['name'],
                'open'=>true,
            );
            array_push($catagoryArr, $ary);
        }
        return json($catagoryArr);
    }

    /**
     * [addInfosort 添加资讯分类]
     * @Author   Mr.fang
     * @DateTime 2018-07-03
     * @version  V1.0.0
     */
	public function addInfosort(Request $request){
		$add['father_id'] = 1;
		$add['name'] = htmlspecialchars($request -> param('infosort_name'));
		$add['orderby'] = intval($request -> param('infosort_orderby'));
		$add['is_active'] = intval($request -> param('infosort_active'));
		$add['create_time'] = time();
	    // 调用公共函数，参数false为新增
        $insert = saveData('article_cat',$add,false);
        if($insert){
            return objReturn(200,'新增成功');
        }else{
            return objReturn(300,'新增失败');
        }
	}    

	/**
	 * [selectInfo 选择节点信息]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-03
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   [ary]               [返回值]
	 */
	public function selectInfo(Request $request){
		$cat_id = intval($request -> param('cat_id'));
    	$article_cat = new Article_cat;
		$data = $article_cat ->field('father_id,name,is_active,orderby') ->where('cat_id',$cat_id)->find();
		if($data){
            return objReturn(200,'success',$data);
		}else{
            return objReturn(300,'failed',$data);
		}
	}

	/**
	 * [editInfosort 修改节点信息]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-03
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 * @return   [ary]               [返回值]
	 */
	public function editInfosort(Request $request){
		$update['cat_id'] = intval($request->param('infosort_cat_id'));
		// $update['father_id'] = 1;
		$update['name'] = $request->param('infosort_cat_name');
		$update['orderby'] = intval($request->param('infosort_cat_orderby'));
		$update['is_active'] = intval($request->param('infosort_cat_active'));
        // 调用公共函数，参数true为更新
        $update = saveData('article_cat',$update,true);        
        if($update){
            return objReturn(200,'修改成功！');
        }else{
            return objReturn(300,'修改失败！');
        } 			
	}

	/**
	 * [delCat 只删除子节点]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-03
	 * @version  V1.0.0
	 * @return   [ary]     [返回值]
	 */
	public function delCat(Request $request){
        $del['cat_id'] = intval($request->param('infosort_cat_id'));
        $del['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $delete = saveData('article_cat',$del,true);
        if($delete){
            return objReturn(200,'删除成功！');
        }else{
            return objReturn(300,'删除失败！');
        }		
	}

	/**
	 * [addPic 添加单张图片]
	 * @Author   Mr.fang
	 * @DateTime 2018-07-06
	 * @version  V1.0.0
	 * @param    Request    $request [参数]
	 */
    public function addPic(Request $request){
        $file = request()->file('file');
        // 是否存在session
        if(Session::has('articleSrc')){
            // 删除session信息
            Session::delete('articleSrc');            
        }
        // 移动到框架应用根目录/public/image/imageTemp/ 目录下
        $info = $file->move(ROOT_PATH . 'public'.DS. 'image'.DS.'imageTemp');
        if ($info) {
            $str = $info->getSaveName();
            $articleSrc = 'public'.DS. 'image'.DS.'imageTemp'.DS. $str;
            // 存路径名到session
            Session::set('articleSrc',$articleSrc); 
        }
        return $articleSrc;   
    }
}
?>