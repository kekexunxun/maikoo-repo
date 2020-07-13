<?php 
namespace  app\index\controller;

use \think\Controller;

use \think\Request;

use \think\Cache;

use \think\Db;

use \think\Session;

use \think\File;

use app\index\model\Minipro;

use app\index\model\Column as columndata;

class Column extends Controller{

    /**
     * @return columnlist 界面
     */
    public function columnlist(){
        // 调用公共函数-获取所有的专栏列表
        $columndata = getAllColumn('idx,name,brief,pic,views,is_active',true,'','');
        $this->assign('columndata',$columndata);
        return $this->fetch();
    }


    /**
     * 获取当前column id
     * @return json column 启用结果
     */
    public function columnStart(Request $request){       
        if ($request->isPost()) {
            $where['idx'] = $request->param('id');
            $where['is_active'] = 1;
           // 调用公共函数，参数true为更新
            $update = saveData('column',$where,true);
            if($update){
                return objReturn(200,'启用成功');
            }else{
                return objReturn(300,'启用失败');
            }  
        }
    }


    /**
     * 获取当前column id
     * @return json column 停用结果
     */
    public function columnStop(Request $request){
        $where['idx'] = $request->param('id');
        $where['is_active'] = 0;
       // 调用公共函数，参数true为更新
        $update = saveData('column',$where,true);
        if($update){
            return objReturn(200,'停用成功');
        }else{
            return objReturn(300,'停用失败');
        }
    }


    /**
     * 获取当前column id
     * @return json 删除column结果
     */
    public function columnDel(Request $request){     
        $del['idx'] = $request->param('id');
        $del['delete_time'] = time();
        $del['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $update = saveData('column',$del,true);
        if($update){
            return objReturn(200,'删除成功');
        }else{
            return objReturn(300,'删除失败');
        }
    }

    /**
     * @return columnminis 专栏详情
     */
    public function columnminis(){
        $request = Request::instance();
        $column_id = intval($request -> param('column_id'));
        $this->assign('column_id',$column_id);
        // dump($column_id); die;
        $miniprodata = getColumnById($column_id,'minis','',true);
        $minipro = $miniprodata['minis'];
        // dump($minipro);die;
        $this->assign('minipro',$minipro);
        return $this->fetch();
    }

    /**
     * miniproDel 删除小程序
     * @return json 结果
     */
    public function miniproDel(Request $request){
        $id = intval($request->param('id'));
        $del['idx'] = intval($request->param('idx'));
        $name = $request->param('name');
        // dump($id);die;
        $column = new columndata();
        $minis = $column ->where('idx',$del['idx']) ->value('minis');
        if(!empty($minis)){
            $miniArr = explode('*',$minis);
            // 最后一个不能删
            if(sizeof($miniArr)==1){
                return objReturn(300,'删除失败');
            }else{
                foreach ($miniArr as $key => $value) {
                    $mini = explode(':',$value);
                    // dump($mini);
                    if (isset($mini[0])) {
                        if ($mini[0]==$id) {
                            $miniAry = explode($value.'*',$minis.'*');
                            // dump($miniAry);
                            // if($miniAry[0]!=''){
                            //     $miniAry[0] = rtrim($miniAry[0]);
                            // }
                            // if($miniAry[1]!=''){
                            //     $miniAry[1] = rtrim($miniAry[1]);
                            // }
                            $result = rtrim($miniAry[0].$miniAry[1],'*');
                            // dump($result);               
                        }            
                    }
                }
                // dump($result);die;
                // dump(123);die;
                $del['minis'] = $result;
                // 调用公共函数，参数true为更新
                $update = saveData('column',$del,true);
                if($update){
                    return objReturn(200,'删除成功');
                }else{
                    return objReturn(300,'删除失败');
                }
            }
        }else{
            return objReturn(300,'删除失败');
        }
    }


    /**
     * @return 添加column界面
     */
    public function columnadd(){
        // 清除路径session
        if(Session::has('columnsrc')){
            // 删除session信息
            Session::delete('columnsrc');            
        }      
        // 调用公共函数
        $miniprodata = getAllMini('mini_id,name,avatarUrl',false);
    	$this->assign('miniprodata',$miniprodata);  
        return $this->fetch();
    }


    /**
     * addPic 添加图片
     * @return json 添加结果
     */
    public function addPic(Request $request){
    	$file = request()->file('file');
        // 是否存在session
        if(Session::has('columnsrc')){
            // 删除session信息
            Session::delete('columnsrc');            
        }
        // 移动到框架应用根目录/public/image/imageTemp/ 目录下
        $info = $file->move(ROOT_PATH . 'public'.DS. 'image'.DS.'imageTemp');
        if ($info) {
            $str = $info->getSaveName();
            $columnsrc = 'public'.DS. 'image'.DS.'imageTemp'.DS. $str;
            // 存路径名到session
            Session::set('columnsrc',$columnsrc); 
        }
        // return $columnsrc;
    }

    /**
     * addColumn 专栏添加
     * @return json 添加结果
     */
    public function addColumn(Request $request){
 		// 接收前端数据
        $add['name'] = htmlspecialchars($request -> param('column_name'));
        $add['brief'] = htmlspecialchars($request -> param('column_brief'));
        $add['minis'] = rtrim($request -> param('column_minis'), '*');
        $add['is_active'] = intval($request -> param('column_active'));
        $add['create_time'] = time();
        // 取session数据
        if(Session::get('columnsrc')!=null){
            // 取session值
            $source = ROOT_PATH.Session::get('columnsrc');
            // dump($source);die; 
            // 新的路径,取session值
            $str = substr_replace(Session::get('columnsrc'),'column',13,9);  
            // 创建文件夹
            $str3 = substr($str,0,29);
            if(!file_exists(ROOT_PATH . $str3)){
                mkdir(ROOT_PATH . $str3); 
            }            
            // 框架应用根目录/public/column/目录
            $destination = ROOT_PATH.$str;    
            // 拷贝文件到指定目录
            $res = copy($source,$destination);
            // 移动成功
            if($res){
                $str = substr($str,6);
                $add['pic'] = $str;
                // 删除session信息
                Session::delete('columnsrc');
                // 调用公共函数，参数false为新增
                $update = saveData('column',$add,false);        
                if($update){
                    return objReturn(200,'添加成功！');
                }else{
                    return objReturn(300,'添加失败！');
                }         
            }
        }
            return objReturn(300,'添加失败，请上传图片！');
    }

    /**
     * @return 修改column界面
     */
    public function columnedit(){
        $request = Request::instance();
        $column_id = intval($request -> param('column_id'));
        // 清除路径session
        if(Session::has('columnsrc')){
            // 删除session信息
            Session::delete('columnsrc');            
        }
        // 调用公共函数
        $miniprodata = getAllMini('mini_id,name,avatarUrl',false);
        // dump($miniprodata);die;
        $this->assign('miniprodata',$miniprodata); 
        // 调用公共函数
        $field = 'idx,name,brief,pic,minis,is_active';
        $columndata = getColumnById($column_id,$field);
        // dump($columndata);die;
        $this->assign('columndata',$columndata);  
        return $this->fetch();
    }

    /**
     * addColumn 专栏修改
     * @return json 添加结果
     */
    public function editColumn(Request $request){
        // 接收前端数据
        $add['idx'] = intval($request -> param('column_id'));
        $add['name'] = htmlspecialchars($request -> param('column_name'));
        $add['brief'] = htmlspecialchars($request -> param('column_brief'));
        $add['minis'] = rtrim($request -> param('column_minis'), '*');
        $add['is_active'] = intval($request -> param('column_active'));
        $add['create_time'] = time();
        // 取session数据
        if(Session::get('columnsrc')!=null){
            // 取session值
            $source = ROOT_PATH.Session::get('columnsrc');
            // dump($source);die; 
            // 新的路径,取session值
            $str = substr_replace(Session::get('columnsrc'),'column',13,9);  
            // 创建文件夹
            $str3 = substr($str,0,29);
            if(!file_exists(ROOT_PATH . $str3)){
                mkdir(ROOT_PATH . $str3); 
            }            
            // 框架应用根目录/public/column/目录
            $destination = ROOT_PATH.$str;      
            // 拷贝文件到指定目录
            $res = copy($source,$destination);
            // 移动成功
            if($res){
                $str = substr($str,6);
                $add['pic'] = $str;
                // 删除session信息
                Session::delete('columnsrc');         
            }        
        }
        // 调用公共函数，参数true为更新
        $update = saveData('column',$add,true);        
        if($update){
            return objReturn(200,'修改成功！');
        }else{
            return objReturn(300,'修改失败！');
        }
    }


}
?>