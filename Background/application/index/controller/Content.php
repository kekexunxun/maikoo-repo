<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Cache;
use \think\Db;
use \think\Session;
use \think\File;
use app\index\model\Banner as bannerdata;
use app\index\model\Goods;
use app\index\model\Article;
use app\index\model\Feedback;
use app\index\model\Question as QuestionDb;
use app\index\model\Clause as ClauseDb;
use app\index\model\Search_kw;
use app\index\model\Column;
use app\index\model\Column_goods;

class Content extends Controller
{
    /**
     * 轮播图展示
     * @return ary 返回值
     */
	public function bannerlist(){
		// 调用公共函数
		$bannerData = getBanner(0,true);
		$this->assign('bannerData',$bannerData);
        return $this->fetch();
	}

	/**
	 * 添加banner图
	 * @return html 页面
	 */
	public function banneradd(){
		$goods = new Goods;
		$goodsData = $goods ->field('goods_id,goods_name') ->where('status', '<>',4) ->select();
		$this->assign('goodsData',$goodsData);		
        return $this->fetch();
	}	 

    /**
     * 上传单张图片
     * @param  Request $request 
     * @return 图片路径
     */
    public function addPic(Request $request){
        $file = request()->file('file');
        // 是否存在session
        if(Session::has('picsrc')){
            // 删除session信息
            Session::delete('picsrc');            
        }
        // ->validate(['size'=>52428800,'ext'=>'jpg,png,gif,jpeg'])
        // 移动到框架应用根目录/static/imgTemp/目录下
        $info = $file->move(DEFAULT_STATIC_PATH .'imgTemp');
        if ($info) {
            $str = $info->getSaveName();
            $picsrc = 'imgTemp'.DS. $str;
            // 存路径名到session
            Session::set('picsrc',$picsrc); 
            return objReturn(0,'上传成功！',$picsrc);
        }
        return objReturn(400,'上传失败！');        
    }	

    /**
     * webuploader上传多图 不超过15张
     * @param Request $request 
     * @return 图片路径
     */
    public function addMultiPic(Request $request){
        $file = request()->file('file');
        // 移动到框架应用根目录/public/static/imageTemp/ 目录下
        $info = $file->move(DEFAULT_STATIC_PATH .'imgTemp');
        if ($info) {
            $str2 = $info->getSaveName();
            $src = 'imgTemp'.DS. $str2;
            return json($src);         
        } 
    }    

    /**
     * 添加banner图片
     * @param Request $request 
     * @return ary 返回结果
     */
    public function addBanner(Request $request){
        $navType = intval($request->param('nav_type'));
        $where['sort'] = intval($request->param('banner_sort'));
        $where['status'] = intval($request->param('banner_active'));
        // 是否存在图片路径session
        if(Session::has('picsrc')){
            $source = DEFAULT_STATIC_PATH.Session::get('picsrc');
            // 新的路径,取session值
            $word = DS.'banner';
            $str = substr_replace(Session::get('picsrc'),$word,3,4);
            // 创建文件夹
            $str1 = substr($str,0,20);
            if(!is_dir(DEFAULT_STATIC_PATH.$str1)){
                mkdir(DEFAULT_STATIC_PATH.$str1); 
            }            
            // 框架应用根目录/public/static/img/目录
            $destination = DEFAULT_STATIC_PATH.$str;
            // 拷贝文件到指定目录
            $res = copy($source,$destination);
            // 移动成功
            if($res){
                $str2 = DS.$str;
                $where['img_src'] = $str2;
	            // 删除session信息
	            // Session::delete('picsrc');
                $where['img_type'] = 0;
                $where['nav_type'] = $navType;	
	            $where['created_at'] = time();
	            // type为0时不跳转
		        // if($navType == 0){
		        // }
		        // type为1时跳转到商品id
		        if($navType == 1){
		        	$where['nav_id'] = intval($request->param('select_goods'));	        	
		        }
                // 调用公共函数保存，参数false为新增
                $insert = saveData('banner',$where,false);
                if($insert){
                    return objReturn(0,'保存成功！');
                }else{
                    return objReturn(400,'保存失败！');
                } 
            }else{
                return objReturn(400,'上传失败,请重新上传图片！');
            }
        }
        return objReturn(400,'保存失败,请上传图片！');
    }

    /**
     * 获取当前banner id
     * @return json banner 启用结果
     */
    public function bannerStart(Request $request){
        $where['img_id'] = intval($request->param('id'));
        $where['status'] = 1;
        // 调用公共函数，参数true为更新
        $update = saveData('banner',$where,true);
        if($update){
            return objReturn(0,'启用成功！');
        }else{
            return objReturn(400,'启用失败！');
        }
    }

    /**
     * 获取当前banner id
     * @return json banner 停用结果
     */
    public function bannerStop(Request $request){
        $where['img_id'] = intval($request->param('id'));
        $where['status'] = 0;
        // 调用公共函数，参数true为更新
        $update = saveData('banner',$where,true);
        if($update){
            return objReturn(0,'停用成功！');
        }else{
            return objReturn(400,'停用失败！');
        }
    }

    /**
     * 获取当前banner id
     * @return json 删除banner结果
     */
    public function bannerDel(Request $request){
        $where['img_id'] = intval($request->param('id'));
        $where['status'] = 3;
        // 调用公共函数，参数true为更新
        $update = saveData('banner',$where,true);
        if($update){
            return objReturn(0,'删除成功！');
        }else{
            return objReturn(400,'删除失败！');
        }
    }

    /**
     * 文章界面
     * @return ary 文章列表页面
     */
    public function article(){
    	$article = new Article;
    	$articleData = $article ->field('article_id,article_title,article_desc,article_brief,status') ->where('status','<>',2) ->select();
		$this->assign('articleData',$articleData);		
        return $this->fetch();
    }

    /**
     * 添加文章列表页面
     * @return ary 
     */
	public function articleadd(){
        return $this->fetch();
	}

	/**
	 * 添加文章功能
	 * @param Request $request 
	 * @return ary   返回值
	 */
	public function addArticle(Request $request){
		$where['article_title'] = htmlspecialchars($request -> param('article_name'));
		$where['article_brief'] = htmlspecialchars($request -> param('article_content'));
        $where['status'] = intval($request->param('article_active'));
        $where['created_at'] = time();
        if(!empty($request -> param('article_picsrc'))){
            $source = $request -> param('article_picsrc');
            // 字符串分割为数组
            $srcArr = explode(',',$source);
            $src = '';
            // 遍历数组移动目录图片
            foreach ($srcArr as $key => $value) {
                // 新的路径
                $word = DS.'article';
                $strTemp = substr_replace($value,$word,3,4);
                // dump($strTemp);die;
                // 创建文件夹
                $str3 = substr($strTemp,0,21);
                if(!is_dir(DEFAULT_STATIC_PATH.$str3)){
                    mkdir(DEFAULT_STATIC_PATH.$str3);
                }
                // 框架应用根目录/public/static/img/目录
                $destination = DEFAULT_STATIC_PATH . $strTemp;    
                $sou = DEFAULT_STATIC_PATH . $value;
                // 拷贝文件到指定目录
                $res = copy($sou,$destination);
                if($res){
                    $src .= DS.$strTemp. ',';          
                }else{
                    return objReturn(400,'上传失败,请重新上传图片！');
                }
            }
            $picSrc= substr($src, 0, strlen($src) - 1);
            $where['article_desc'] = $picSrc;
            // 调用公共函数保存，参数false为新增
            $insert = saveData('article',$where,false);
            if($insert){
                return objReturn(0,'保存成功！');
            }else{
                return objReturn(400,'保存失败！');
            }             
        }else{
            return objReturn(400,'上传失败,请重新上传图片！');
        }	
	}

    /**
     * 获取当前article id
     * @return json article 启用结果
     */
    public function articleStart(Request $request){
        $where['article_id'] = intval($request->param('id'));
        $where['status'] = 1;
        // 调用公共函数，参数true为更新
        $update = saveData('article',$where,true);
        if($update){
            return objReturn(0,'启用成功！');
        }else{
            return objReturn(400,'启用失败！');
        }
    }

    /**
     * 获取当前article id
     * @return json article 停用结果
     */
    public function articleStop(Request $request){
        $where['article_id'] = intval($request->param('id'));
        $where['status'] = 0;
        // 调用公共函数，参数true为更新
        $update = saveData('article',$where,true);
        if($update){
            return objReturn(0,'停用成功！');
        }else{
            return objReturn(400,'停用失败！');
        }
    }

    /**
     * 获取当前article id
     * @return json 删除article结果
     */
    public function articleDel(Request $request){
        $where['article_id'] = intval($request->param('id'));
        $where['status'] = 2;
        // 调用公共函数，参数true为更新
        $update = saveData('article',$where,true);
        if($update){
            return objReturn(0,'删除成功！');
        }else{
            return objReturn(400,'删除失败！');
        }
    }

    /**
     * @return 用户协议页面
     */
    public function clauselist(){
        $clause = new ClauseDb;
        $info = $clause ->where('idx',1) ->find();
        $this->assign('info',$info);    
        return $this->fetch();
    }

    /**
     * 修改用户协议
     * @return json 修改结果
     */
    public function updateClause(Request $request){
        $clause = new ClauseDb;
        $content['idx'] = 1;
        $content['clause'] = htmlspecialchars($request -> param('content'));
        $update = $clause -> update($content);
        if($update){
            return objReturn(0,'修改成功');
        }else{
            return objReturn(400,'修改失败');
        } 
    }

// ***********************    
    /**
     * 关键词列表界面
     */
    public function searchlist()
    {
        $searchKwDb = new Search_kw;
        $searchKwData = $searchKwDb -> where("status != 2") -> order('sort asc') -> select();
        foreach( $searchKwData as $k => $v ){
            $searchKwData[$k]['created_at'] = date( 'Y-m-d H:i:s',$v['created_at'] );
            $searchKwData[$k]['update_at'] = empty($v['update_at']) ? '' : date( 'Y-m-d H:i:s',$v['update_at'] );
        }
        $this -> assign('data',$searchKwData);
        return $this->fetch();
    }

    /**
     * 添加关键词界面
     * @param   
     * @return  result 添加结果
     */
    public function searchAdd(Request $request)
    {
        $searchKwDb = new Search_kw;
        if( $request -> isPost() ){
            $data['sort'] = $request -> param('sort');
            $data['nav_type'] = $request -> param('nav_type');
            $data['value'] = $request -> param('search_kw');
            $data['created_at'] = time();
            if( $data['nav_type'] == 0 ){
                $result = $searchKwDb -> insert($data);
                if( $result ){
                    return objReturn(0,'添加关键词成功!');
                }else{
                    return objReturn(400,'添加关键词失败!');
                }
            }else if( $data['nav_type'] == 1 ){
                $goods_id = $request -> param('goods_id');
                if( empty( $goods_id ) ){
                    return objReturn(400,'请选择跳转商品!');
                }
                $data['nav_id'] = $goods_id;
                $result = $searchKwDb -> insert($data);
                if( $result ){
                    return objReturn(0,'添加关键词成功!');
                }else{
                    return objReturn(400,'添加关键词失败!');
                }
            }else{
                $article_id = $request -> param('article_id');
                if( empty( $article_id ) ){
                    return objReturn(400,'请选择跳转内容!');
                }
                $data['nav_id'] = $article_id;
                $result = $searchKwDb -> insert($data);
                if( $result ){
                    return objReturn(0,'添加关键词成功!');
                }else{
                    return objReturn(400,'添加关键词失败!');
                }
            }
        }else{
            return $this -> fetch();
        }
    }

    /**
     * 删除关键词
     * @param   idx  关键词ID
     * @return  result 删除结果
     */
    public function searchDelete(Request $request)
    {
        $idx = $request -> param('idx');
        $searchKwDb = new Search_kw;
        $data['status'] = 2;
        $result = $searchKwDb -> where(['idx'=>$idx]) -> update($data);
        if( $result ){
            return objReturn(0,'关键词删除成功!');
        }else{
            return objReturn(400,'关键词删除失败!');
        }
    }

    /**
     * 改变关键词状态
     * @param   idx  关键词ID
     * @return  result 操作结果
     */
    public function searchaChange(Request $request)
    {
        $searchKwDb  = new Search_kw;
        $idx = $request->param('idx');
        $status      = $request->param('status');
        $msg         = '';
        $error       = '';
        if ($status == 0) {
            $data['status'] = 1;
            $msg            = '启用成功!';
            $error          = '启用失败!';
        } else {
            $data['status'] = 0;
            $msg            = '关闭成功!';
            $error          = '关闭失败!';
        }
        $result = $searchKwDb->where(['idx' => $idx])->update($data);
        if ($result) {
            return objReturn(0, $msg);
        } else {
            return objReturn(400, $error);
        }
    }

    /**
     * 关键词编辑
     * @param   idx  关键词ID
     * @return  result 编辑结果
     */
    public function searchEdit(Request $request)
    {
        $idx = $request -> param('idx');
        $searchKwDb = new Search_kw;
        if( $request -> post() ){
            $data['nav_type'] = $request -> param('nav_type');
            $data['sort'] = $request -> param('sort');
            $data['value'] = $request -> param('search_kw');
            $data['update_at'] = time();
            $data['update_by'] = 1;
            if( $data['nav_type'] == 0 ){
                $data['nav_id'] = 0;
                $result = $searchKwDb -> where(['idx'=>$idx]) -> update($data);
                if( $result ){
                    return objReturn(0,'修改关键词成功!');
                }else{
                    return objReturn(400,'修改关键词失败!');
                }
            }else if( $data['nav_type'] == 1 ){
                $goods_id = $request -> param('goods_id');
                if( empty( $goods_id ) ){
                    return objReturn(400,'请选择跳转商品!');
                }
                $data['nav_id'] = $goods_id;
                $result = $searchKwDb -> where(['idx'=>$idx]) -> update($data);
                if( $result ){
                    return objReturn(0,'修改关键词成功!');
                }else{
                    return objReturn(400,'修改关键词失败!');
                }
            }else{
                $article_id = $request -> param('article_id');
                if( empty( $article_id ) ){
                    return objReturn(400,'请选择跳转内容!');
                }
                $data['nav_id'] = $article_id;
                $result = $searchKwDb -> where(['idx'=>$idx]) -> update($data);
                if( $result ){
                    return objReturn(0,'修改关键词成功!');
                }else{
                    return objReturn(400,'修改关键词失败!');
                }
            }
        }else{
            $searchKwData = $searchKwDb -> where(['idx'=>$idx]) -> find();
            $this -> assign('data',$searchKwData);
            return $this -> fetch();
        }
    }

    /**
     * 获取商品数据或文章数据
     * @param   type  判断获取文章还是商品
     * @return  array 返回商品数据或文章数据
     */
    public function getSelect(Request $request)
    {
        $type = $request -> param('type');
        if( $type == 'goods' ){
            $goods     = new Goods;
            $goodsInfo = $goods->field('goods_id,goods_name') ->where('status', '<>',4) ->select();
            return json($goodsInfo);
        }else{
            $article = new Article;
            $articleData = $article ->field('article_id,article_title') ->where('status','<>',2) ->select();
            return json($articleData);
        }
    }

    /**
     * 用户反馈列表界面
     * @return array 用户反馈列表数据
     */
    public function feedbacklist()
    {
        $feedbackData = getFeedback();
        $this->assign('data', $feedbackData);
        return $this->fetch();
    }

    /**
     * 用户反馈回复界面
     * @param  idx    用户反馈记录ID
     * @param  reply  回复内容
     * @return result 回复结果
     */
    public function feedbackReply(Request $request)
    {
        $feedbackDb = new Feedback;
        $idx        = $request->param('idx');
        if ($request->isPost()) {
            $data['reply'] = $request->param('reply');
            $status        = $request->param('status');
            if ($status != 0) {
                return objReturn(0, '此反馈已处理!');
            }
            $data['reply_at'] = time();
            $data['reply_by'] = 3;
            $data['status']   = 1;
            $result           = $feedbackDb->where(['idx' => $idx])->update($data);
            if ($result) {
                return objReturn(0, '回复成功!');
            } else {
                return objReturn(400, '回复失败!');
            }
        } else {
            $feedbackData = $feedbackDb->where(['idx' => $idx])->find();
            $this->assign('data', $feedbackData);
            return $this->fetch();
        }
    }

    /**
     * 问答列表界面
     * @return array
     */
    public function questionlist()
    {
        $questionData = getSysQA();
        $this->assign('data', $questionData);
        return $this->fetch();
    }

    /**
     * 问答添加界面
     * @param  question 问题
     * @param  answer   回答
     * @return result 添加结果
     */
    public function questionadd(Request $request)
    {
        if ($request->isPost()) {
            $data               = $request->post();
            $data['created_at'] = time();
            $data['created_by'] = 1;
            $questionDb         = new QuestionDb;
            $result             = $questionDb->insert($data);
            if ($result) {
                return objReturn(0, '添加问答成功!');
            } else {
                return objReturn(400, '添加问答失败!');
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 问答编辑界面
     * @param  question_id  问答ID
     * @param  question 问题
     * @param  answer   回答
     * @return result 编辑结果
     */
    public function questionedit(Request $request)
    {
        $questionDb  = new QuestionDb;
        $question_id = $request->param('question_id');
        if ($request->isPost()) {
            $data['question']  = $request->param('question');
            $data['answer']    = $request->param('answer');
            $data['update_at'] = time();
            $data['update_by'] = 1;
            $result            = $questionDb->where(['question_id' => $question_id])->update($data);
            if ($result) {
                return objReturn(0, '编辑问答成功!');
            } else {
                return objReturn(400, '编辑问答失败!');
            }
        } else {
            $questionData = $questionDb->where(['question_id' => $question_id])->find();
            $this->assign('data', $questionData);
            return $this->fetch();
        }
    }

    /**
     * 问答的删除
     * @param  question_id  问答ID
     * @return result 删除结果
     */
    public function questiondelete(Request $request)
    {
        $questionDb     = new QuestionDb;
        $question_id    = $request->param('question_id');
        $data['status'] = 2;
        $result         = $questionDb->where(['question_id' => $question_id])->update($data);
        if ($result) {
            return objReturn(0, '删除问答成功!');
        } else {
            return objReturn(400, '删除问答失败!');
        }
    }

    /**
     * 改变问答的状态
     * @param  question_id  问答ID
     * @return result 改变状态结果
     */
    public function questionChange(Request $request)
    {
        $questionDb  = new QuestionDb;
        $question_id = $request->param('question_id');
        $status      = $request->param('status');
        $msg         = '';
        $error       = '';
        if ($status == 0) {
            $data['status'] = 1;
            $msg            = '启用成功!';
            $error          = '启用失败!';
        } else {
            $data['status'] = 0;
            $msg            = '关闭成功!';
            $error          = '关闭失败!';
        }
        $result = $questionDb->where(['question_id' => $question_id])->update($data);
        if ($result) {
            return objReturn(0, $msg);
        } else {
            return objReturn(400, $error);
        }
    }
// ******************
    /**
     * 专栏列表
     */
    public function columnlist()
    {
        $columnData = getColumn();
        $this -> assign('data',$columnData);
        return $this -> fetch();
    }

    /**
     * 添加专栏
     * @param   column_sort  专栏顺序
     * @param   column_name  专栏名称
     * @param   column_color 专栏主题颜色
     * @param   column_img   专栏封面图片
     * @param   column_goods 专栏商品
     * @return  result 添加结果
     */
    public function columnadd(Request $request)
    {
        if( $request -> post() ){
            $post = $request -> post();
            //判断专栏主题颜色是否为空
            if( empty($post['column_color']) ){
                return objReturn(400,'请选择专栏主题颜色!');
            }
            //判断专栏商品是否为空
            if( empty( $post['goods'] ) ){
                return objReturn(400,'请选择商品!');
            }
            $data['column_color'] = $post['column_color'];
            $data['sort'] = $post['sort'];
            $data['column_name'] = $post['column_name'];
            $data['status'] = $post['status'];
            $data['is_top'] = $post['is_top'];
            $goods = $post['goods'];
            $file = $request->file('column_img');
            if( !$file ){
                return objReturn(400,'请选择专栏封面图片');
            }
            //专栏封面图片存储目录
            $dir = '.' . DS . 'static' . DS . 'img' . DS . 'column' . DS;
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $info = $file->move($dir);
            if ($info) {
                $saveName     = $info->getSaveName();
                $data['column_img'] = '/img/column/'.$saveName;
            } else {
                return objReturn(400, $file->getError());
            }
            $data['created_at'] = time();
            // $data['created_by'] =
            $columnDb = new Column;
            $column_id = $columnDb -> insertGetId($data);
            if( !$column_id ){
                return objReturn(500,'请求错误,请重试!');
            }
            $column_goods_data = [];
            foreach( $goods as $k => $v ){
                $column_goods_data[] = ['column_id'=>$column_id,'goods_id'=>$v,'created_at'=>$data['created_at'],'created_by'=>0,'status'=>1];
            }
            $column_goodsDb = new Column_goods;
            $result = $column_goodsDb -> insertAll($column_goods_data);
            if( $result ){
                return objReturn(0,'添加专栏成功!');
            }else{
                return objReturn(400,'添加专栏失败!');
            }
        }else{
            $goodsDb = new Goods;
            $goodsData = $goodsDb -> where(['status'=>2]) -> select();
            $this -> assign('data',$goodsData);
            return $this -> fetch();
        }
    }

    /**
     * 编辑专栏
     * @param   column_sort  专栏顺序
     * @param   column_name  专栏名称
     * @param   column_color 专栏主题颜色
     * @param   column_img   专栏封面图片
     * @param   column_goods 专栏商品
     * @return  result 编辑结果
     */
    public function columnedit(Request $request)
    {
        $column_id = $request -> param('column_id');
        if( $request -> isPost() ){
            $data = $request -> except(['column_img','column_id','old_column_img','goods'],'post');
            $post = $request -> post();
            //图片上传
            $file = $request->file('column_img');
            if( !$file ){
                $oldImg = $post['old_column_img'];
                if( empty( $oldImg ) ){
                    return objReturn(400,'请选择专栏封面图片');
                }
                $dir = pathinfo( pathinfo( $oldImg,PATHINFO_DIRNAME ),PATHINFO_BASENAME );
                $data['column_img'] = '/img/column/'.$dir.'/'.pathinfo( $oldImg,PATHINFO_BASENAME ); 
            }else{
                //专栏封面图片存储目录
                $dir = '.' . DS . 'static' . DS . 'img' . DS . 'column' . DS;
                $info = $file->move($dir);
                if ($info) {
                    $saveName     = $info->getSaveName();
                    $data['column_img'] = '/img/column/'.$saveName;
                } else {
                    return objReturn(400, $file->getError());
                }
            }
            $data['update_at'] = time();
            // $data['update_by'] =
            //更新column表
            $columnDb = new Column;
            $result = $columnDb -> where(['column_id'=>$column_id]) -> update($data);
            if( !$result ){
                return objReturn(500,'请求错误,请重试!');
            }
            //更新column_goods表
            $columnGoodsDb = new Column_goods;
            $columnGoodsDb -> where( ['column_id'=>$column_id] ) -> delete();
            $column_goods_data = [];
            if( empty($post['goods']) ){
                return objReturn(0,'修改专栏成功!');
            }
            $goods = $post['goods'];
            foreach( $goods as $k => $v ){
                $column_goods_data[] = ['column_id'=>$column_id,'goods_id'=>$v,'update_at'=>$data['update_at'],'update_by'=>0];
            }
            $result = $columnGoodsDb -> insertAll($column_goods_data);
            if( $result ){
                return objReturn(0,'修改专栏成功!');
            }else{
                return objReturn(400,'修改专栏失败!');
            }
        }else{
            $columnData = getColumnById($column_id);
            $goods_ids = [];
            foreach( $columnData['goods'] as $k => $v ){
                $goods_ids[] = $v['goods_id'];
            }
            $goodsDb = new Goods;
            $goodsData = $goodsDb -> where(['status'=>2]) -> select();
            $this -> assign('goodsData',$goodsData);
            $this -> assign('goods_ids',$goods_ids);
            $this -> assign('data',$columnData);
            return $this -> fetch();
        }
    }

    /**
     * 编辑专栏
     * @param   column_id 专栏ID
     * @return  result    删除结果
     */
    public function columnDelete(Request $request)
    {
        $column_id = $request -> param('column_id');
        $columnDb = new Column;
        $columnGoodsDb = new Column_goods;
        //删除column_goods表专栏商品
        $columnGoodsDb -> where(['column_id'=>$column_id]) -> delete();
        //删除column表专栏
        $result = $columnDb -> where(['column_id'=>$column_id]) -> update(['status'=>3]);
        if( $result ){
            return objReturn(0,'专栏删除成功!');
        }else{
            return objReturn(400,'专栏删除失败!');
        }
    }

    /**
     * 专栏商品列表
     * @param   column_id 专栏ID
     */
    public function columngoodslist(Request $request)
    {
        $column_id = $request -> param('column_id');
        $columnData = getColumnById($column_id);
        $this -> assign('column_id',$column_id);
        $this -> assign('data',$columnData['goods']);
        return $this -> fetch();
    }

}