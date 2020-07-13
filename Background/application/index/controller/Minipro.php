<?php 
namespace  app\index\controller;

use \think\Controller;

use \think\Request;

use \think\Cache;

use \think\Db;

use \think\Session;

use \think\File;

use app\index\model\Minipro as miniprodata;

use app\index\model\Catagory;

use app\index\model\Column;

use app\index\model\Clause;

use app\index\model\User_fav;

use app\index\model\Mini_click_count;

use app\index\model\Rank;

use app\index\model\Click_set;

class Minipro extends Controller{

    /**
     * @return 小程序信息界面
     */   
    public function miniprolist(){
        // 引用公共函数
        $minipro = getAllMini('mini_id',false);
        $num = sizeof($minipro);
        $this->assign('num',$num);
        return $this->fetch();
    }

    /**
     * catagory 分类列表
     * @return  json catagory 返回值
     */
    public function catagory(Request $request){
        // $catagory_id = intval($request->param($catagory_id));
        // 调用公共函数
        $catagorylist = getAllCatagory('catagory_id,father_id,name',false,true);
        // 返回ztree数据
        $catagory_ary = array();
        foreach ($catagorylist as $key => $value) {
            $ary=array(
                'id'=>$value['catagory_id'],
                'pId'=>$value['father_id'],
                'name'=>$value['name'],
                'open'=>true,
            );
            array_push($catagory_ary, $ary);
        }
        return json($catagory_ary);
    }

    /**
     * miniproInfo 小程序信息
     * @return  json miniproInfo 返回值
     */
    public function miniproInfo(){
        // 引用公共函数获取小程序信息
        $res = getAllMini('mini_id,name,avatarUrl,brief,views,is_openable,is_active',true);
        return json($res);
    }

    /**
     * changeMinipro 启用
     * @return  json changeMinipro 启用结果
     */
    public function changeMinipro(Request $request){
        $where['mini_id'] = intval($request->param('id'));
        $where['is_active'] = 1;
        // 引用公共函数更新
        $update = saveData('mini',$where,true);
        if($update){
            return objReturn(200,'已启用');
        }else{
            return objReturn(300,'启用失败');
        }
    }

    /**
     * stopMinipro 停用
     * @return  json stopMinipro 停用结果
     */
    public function stopMinipro(Request $request){
        $where["is_active"] = 0;
        $where["mini_id"] = intval($request->param('id'));       
        // 引用公共函数更新        
        $update = saveData('mini',$where);
        if($update){
            return objReturn(200,'已停用');
        }else{
            return objReturn(300,'停用失败');   
        }       
    }

    /**
     * delMinipro 删除
     * @return  json delMinipro 删除结果
     */
    public function delMinipro(Request $request){
        $where["mini_id"]= intval($request->param('id'));
        $where["delete_time"] = time();
        $where["is_delete"] = 1;
        // 引用公共函数更新
        $update = saveData('mini',$where);
        if($update){
            return objReturn(200,'删除成功');
        }else{
            return objReturn(300,'删除失败');
        }       
    }

    /**
     * selectMinipro 筛选
     * @return  json selectMinipro 筛选结果
     */
    public function selectMinipro(Request $request){
        $catagory_id = intval($request->param('catagory_id'));
        // 引用公共函数-通过分类ID获取小程序详情
        $field = 'catagory_id,father_id,name';
        $miniField = 'mini_id,name,avatarUrl,brief,views,is_openable,is_active,catagory_id';
        $res = getCatagoryById($catagory_id,$field,$miniField,false);
        // dump($res);die;
        return json($res[0]['minis']);
    }


    /**
     * @return 小程序信息添加界面
     */
    public function miniproadd(){
        $catagory = new Catagory();
        $catagorylist = $catagory -> field('catagory_id,name') ->where('father_id',0) ->where('is_delete',0) -> select();
        $this->assign('catagorylist',$catagorylist);
        return $this->fetch();
    }


    /**
     * catagoryList 获取分类信息
     * @return json 添加结果
     */
    public function catagoryList(Request $request){
        $catagory_id = intval($request->param('catagory_id'));
        $catagory = new Catagory();
        $list = $catagory ->where('father_id',$catagory_id) ->where('is_delete',0) ->select();
        if($list){
            return objReturn(200,'success',$list);
        }else{
            return objReturn(300,'failed',$list);
        }
    }

    /**
     * @return 添加小程序头像
     */
    public function miniprothum(){
        return $this->fetch('miniprothum');
    }

    /**
     * addPic 添加图片
     * @return json 添加结果
     */
    public function addPic(Request $request){
        $file = request()->file('file');
        // 是否存在session
        if(Session::has('miniprosrc')){
            // 删除session信息
            Session::delete('miniprosrc');            
        }
        // 移动到框架应用根目录/public/image/imageTemp/ 目录下
        $info = $file->move(ROOT_PATH . 'public'.DS. 'image'.DS.'imageTemp');
        if ($info) {
            $str = $info->getSaveName();
            $miniprosrc = 'public'.DS. 'image'.DS.'imageTemp'.DS. $str;
            // 存路径名到session
            Session::set('miniprosrc',$miniprosrc); 
        }
        return $miniprosrc;   
    }

    /**
     * addMiniproPic 添加图片
     * @return json 添加结果
     */
    public function addMiniproPic(Request $request){
        $file = request()->file('file');
        // 移动到框架应用根目录/public/image/imageTemp/ 目录下
        $info = $file->move(ROOT_PATH . 'public'.DS. 'image'.DS.'imageTemp');
        if ($info) {
            $str2 = $info->getSaveName();
            $minipropicsrc = 'public'.DS. 'image'.DS.'imageTemp'.DS. $str2;
        } 
        return json($minipropicsrc);
    }

    /**
     * 添加小程序
     * @return json 添加小程序信息
     */
    public function addMinipro(Request $request) {
        $minipro['name'] = htmlspecialchars($request -> param('minipro_name'));
        $minipro['brief'] = htmlspecialchars($request -> param('minipro_brief'));
        $minipro['intro'] = htmlspecialchars($request -> param('minipro_intro'));
        $minipro['appid'] = $request -> param('minipro_appid');
        $minipro['path'] = htmlspecialchars($request -> param('minipro_path'));
        $minipro['extra_data'] = htmlspecialchars($request -> param('minipro_extraData'));
        $minipro['views'] = intval($request -> param('minipro_views'));
        $minipro['keywords'] = rtrim($request -> param('minipro_keyword'), ',');
        // 是否存在session
        if(Session::has('miniprosrc')){

            $source = ROOT_PATH.Session::get('miniprosrc');
            // dump($source);die;
            // 新的路径,取session值
            $str = substr_replace(Session::get('miniprosrc'),'mini',13,9);
            // 创建文件夹
            $str3 = substr($str,0,27);
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
                $minipro['avatarUrl'] = $str; 
            }
            // 删除session信息
            Session::delete('miniprosrc');
            // 是否为空
            if(!empty($request -> param('minipro_picsrc'))){
                $source = $request -> param('minipro_picsrc');
                // 字符串分割为数组
                $srcary = explode('*',$source);
                $src = '';
                // 遍历数组移动目录图片
                foreach ($srcary as $key => $value) {
                    // 新的路径
                    $strTemp = substr_replace($value,'mini',13,9);
                    // 创建文件夹
                    $str3 = substr($strTemp,0,27);
                    if(!file_exists(ROOT_PATH . $str3)){
                        mkdir(ROOT_PATH . $str3); 
                    }
                    // 框架应用根目录/public/mini/目录
                    $destination = ROOT_PATH . $strTemp;    
                    $sou = ROOT_PATH . $value;
                    // 拷贝文件到指定目录
                    $res = copy($sou,$destination);
                    $src .= substr($strTemp,6). '*';          
                }
                $srcpics = substr($src, 0, strlen($src) - 1);
                $minipro['pics'] = $srcpics;

                $minipro['is_active'] = intval($request -> param('minipro_active'));
                $minipro['catagory_id'] = intval($request -> param('minipro_catagory'));
                $minipro['create_time'] = time();
                // 调用公共函数保存，参数false为新增
                $insert = saveData('mini',$minipro,false);
                if($insert){
                    return objReturn(200,'保存成功');
                }else{
                    return objReturn(300,'保存失败');
                }        
            }else{
                return objReturn(300,'保存失败,请上传图片！');
            }
        }
            return objReturn(300,'保存失败,请上传图片！');
    }

    /**
     * @return 修改小程序页面
     */
    public function miniproedit(){
        $request = Request::instance();
        $minipro_id = intval($request -> param('minipro_id'));
        // 清除路径session
        if(Session::has('miniprosrc')){
            // 删除session信息
            Session::delete('miniprosrc');            
        }
        // 分类信息
        $catagory = new Catagory();
        $catagorylist = $catagory -> field('catagory_id,name,father_id') ->where('father_id',0) ->where('is_delete',0) -> select();
        $list = $catagory -> field('catagory_id,name,father_id') ->where('father_id','<>',0) ->where('is_delete',0) -> select();
        // dump($list);die;
        $this->assign('list',$list);
        $this->assign('catagorylist',$catagorylist);
        $miniField = 'mini_id,name,brief,intro,is_active,avatarUrl,pics,catagory_id,appid,path,keywords,extra_data,views';
        $minipro = getMiniById($minipro_id,$miniField,true);
        // 判断是否存在pic
        if(isset($minipro['pics'])){
        // $pic = isset($minipro['pics']) ? $minipro['pics'] : '';
            $miniArr = explode('*',$minipro['pics']); 
            // // 构建返回数组
            $miniList = array();           
            foreach ($miniArr as $key => $value) {
                $miniList[] = $value;
            }
            $minipro['pics'] = $miniList;
        }
        // 判断是否存在keywords
        if(isset($minipro['keywords'])){
        // $pic = isset($minipro['pics']) ? $minipro['pics'] : '';
            $miniArr2 = explode(',',$minipro['keywords']); 
            // // 构建返回数组
            $miniList2 = array();           
            foreach ($miniArr2 as $key => $value) {
                if($value!=''){
                    $miniList2[] = $value;    
                }
            }
            $minipro['keywords'] = $miniList2;
        }        
        // dump($minipro);die;
        $this->assign('minipro',$minipro);
        return $this->fetch('miniproedit'); 
    }

    /**
     * 修改小程序
     * @return json 修改小程序信息
     */
    public function editMinipro(Request $request) {
        $minipro['mini_id'] = intval($request -> param('mini_id'));
        $minipro['name'] = htmlspecialchars($request -> param('minipro_name'));
        $minipro['brief'] = htmlspecialchars($request -> param('minipro_brief'));
        $minipro['intro'] = htmlspecialchars($request -> param('minipro_intro'));
        $minipro['appid'] = $request -> param('minipro_appid');
        $minipro['path'] = $request -> param('minipro_path');
        $minipro['extra_data'] = htmlspecialchars($request -> param('minipro_extraData'));
        $minipro['views'] = intval($request -> param('minipro_views'));
        $minipro['keywords'] = rtrim($request -> param('minipro_keyword'), ',');
        // 是否存在session
        if(Session::has('miniprosrc')){
            // 取session值
            $source = ROOT_PATH.Session::get('miniprosrc');
            // dump($source);die;
            // 新的路径,取session值
            $str = substr_replace(Session::get('miniprosrc'),'mini',13,9);
            // 创建文件夹
            $str3 = substr($str,0,27);
            if(!file_exists(ROOT_PATH . $str3)){
                mkdir(ROOT_PATH . $str3); 
            }            
            // 框架应用根目录/public/minipro/目录
            $destination = ROOT_PATH.$str;
            // 拷贝文件到指定目录
            $res = copy($source,$destination);
            // 移动成功
            if($res){
                $str = substr($str,6);
                $minipro['avatarUrl'] = $str; 
            }
            // 删除session信息
            Session::delete('miniprosrc');
        }
        // 是否为空
        if(!empty($request -> param('minipro_picsrc'))){
            $source = $request -> param('minipro_picsrc');
            // 字符串分割为数组
            $srcary = explode('*',$source);
            $src = '';
            // 遍历数组移动目录图片
            foreach ($srcary as $key => $value) {
                // 新的路径
                $strTemp = substr_replace($value,'mini',13,9);
                // 创建文件夹
                $str3 = substr($strTemp,0,27);
                if(!file_exists(ROOT_PATH . $str3)){
                    mkdir(ROOT_PATH . $str3); 
                }
                // 框架应用根目录/public/minipro/目录
                $destination = ROOT_PATH . $strTemp;    
              
                $sou = ROOT_PATH . $value;
                // 拷贝文件到指定目录
                $res = copy($sou,$destination);
                $src .= substr($strTemp,6). '*';          
            }
            $srcpics = substr($src, 0, strlen($src) - 1);
            $minipro['pics'] = $srcpics;       
        }    
        $minipro['is_active'] = intval($request -> param('minipro_active'));
        $minipro['catagory_id'] = intval($request -> param('catagory'));
        $minipro['create_time'] = time();
        // 调用公共函数保存，参数true为更新
        $update = saveData('mini',$minipro,true);
        if($update){
            return objReturn(200,'修改成功');
        }else{
            return objReturn(300,'修改失败');
        }  
    }

    /**
     * @return 小程序-用户协议页面
     */
    function miniproagreement(){
        $clause = new Clause();
        $info = $clause ->where('idx',1) ->find();
        $this->assign('info',$info);    
        return $this->fetch();
    }

    /**
     * 修改小程序-用户协议
     * @return json 修改结果
     */
    public function addAgreement(Request $request){
        $clause = new Clause();
        $content['idx'] = 1;
        $content['clause'] = htmlspecialchars($request -> param('content'));
        $update = $clause -> update($content);
        if($update){
            return objReturn(200,'修改成功');
        }else{
            return objReturn(300,'修改失败');
        } 
        return json($res);
    }

    /**
     * @return 小程序管理-用户收藏查看页面
     */
    public function miniprofav(){
        $user_fav = new User_fav;
        //给要关联的表取别名,并让两个值关联
        $favData = $user_fav -> alias('a') -> join('userinfo w','a.user_openid = w.user_openid','left') ->field('a.user_openid,w.nickName')-> group('a.user_openid')->select();
        $this->assign('favData',$favData);
        return $this->fetch();
    }

    /**
     * [selectFav 选择用户收藏]
     * @Author   Mr.fang
     * @DateTime 2018-07-04
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [收藏信息]
     */
    public function selectFav(Request $request){
        $user_openid = $request -> param('user_openid');
        // 调用公共函数
        $field = 'idx,fav_id,fav_type';
        $data = getUserFavList($user_openid,$field,0,true);
        foreach ($data as $key => $value) {
            // type为1时为收藏的小程序
            if($data[$key]['fav_type']==1){
                // 调用公共函数
                $field2 = 'name,avatarUrl,catagory_id';
                $miniData = getMiniById($data[$key]['fav_id'],$field2,true);
                // array_push($favData, $miniData);
                $data[$key]['name'] = $miniData['name'];
                $data[$key]['pic'] = '/public'.$miniData['avatarUrl'];
            }
            // type为2时为收藏的专栏
            if($data[$key]['fav_type']==2){
                // 调用公共函数
                $field3 = 'name,pic';
                $columnData = getColumnById($data[$key]['fav_id'],$field3,null,true);
                $data[$key]['name'] = $columnData['name'];
                $data[$key]['pic'] = '/public'.$columnData['pic'];                
            }
        }
        // dump($data);
        return objReturn(200,'success',$data);
    }

    /**
     * [miniproclick 小程序点击量统计]
     * @Author   Mr.fang
     * @DateTime 2018-07-04
     * @version  V1.0.0
     * @return   [html]     [页面]
     */
    public function miniproclick(){
        $miniprodata = new miniprodata;
        // 调用公共函数
        $field = 'mini_id,name';
        $miniData = getAllMini($field,false);
        // dump($miniData);die;
        $this->assign('miniData',$miniData);
        return $this->fetch();
    }
    
    /**
     * [getStats 小程序点击量数据]
     * @Author   Mr.fang
     * @DateTime 2018-07-05
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    function getStats(Request $request){
        $select = $request -> param('select');
        $mini_click_count = new Mini_click_count;
        // 选择今天的数据
        if($select == 'today'){
            // 现在的时间戳
            $nowTime = time();
            // 今天0点时间戳
            $todayTime = strtotime(date("Y-m-d 00:00:00"));
            // 构造今日的数据 y轴小程序名称 x轴三个数据
            $isEnter = [];
            $isNotEnter =[];
            $isAll = [];
            // 调用公共函数
            $field = 'mini_id,name';
            $miniData = getAllMini($field,false);
            // 查询数据
            $totalClick = $mini_click_count -> field('mini_id, is_enter,clicks') -> where('click_time', 'between',[$todayTime,$nowTime]) ->where('is_enable',1)->where('is_delete',0)->select();
            // $totalClick = collection($totalClick) -> toArray();
            // 非空判断
            if(!empty($totalClick)){
                $xData = [];
                foreach ($miniData as $k => $v) {
                    $xData [] = $v['name'];
                    $isEnter[$k] = 0;
                    $isNotEnter[$k] = 0;
                    $isAll[$k] = 0;
                    foreach ($totalClick as $ke => $va) {
                        if ($v['mini_id'] == $va['mini_id']) {
                            if ($va['is_enter'] == 1) {
                                $isEnter[$k] += $va['clicks'];
                            }else if ($va['is_enter'] == 0) {
                                $isNotEnter[$k] += $va['clicks'];
                            }
                            $isAll[$k] += $va['clicks'];
                        }
                    }
                }
                // dump($isEnter);die;
            }else{
                    // 当所有数据为空，显示自定义点击量
                    // 构造当月的数据 y轴小程序名称 x轴三个数据
                    $isEnter = [];
                    $isNotEnter =[];
                    $isAll = [];
                    $xData = [];
                    foreach ($miniData as $k => $v) {
                        $xData [] = $v['name'];
                        $isEnter[$k] = 0;
                        $isNotEnter[$k] = 0;
                        $isAll[$k] = 0;                      
                    }              
                }
                // y轴数据
                $data1 = array(
                    'data' => $isEnter,
                    'name' => '已跳转'
                );
                $data2 = array(
                    'data' => $isNotEnter,
                    'name' => '未跳转'
                );
                $data3 = array(
                    'data' => $isAll,
                    'name' => '点击总量'
                );                
                $seriesData = array();
                array_push($seriesData, $data1);
                array_push($seriesData, $data2);
                array_push($seriesData, $data3);
                $title = '小程序今日点击量统计';
                $data = array(
                    'seriesData' => $seriesData,
                    'title'      => $title,
                    'xData'      => $xData,
                );
                return json($data);
        }else{
            // 一整月的数据
            $days = date("t");//获取当前月份天数
            // 当月第一天
            $startTime = strtotime(date('Y-m-01')); //获取本月第一天时间戳搜索
            // 当月最后一天
            $endTime = mktime(23,59,59,date('m'),date('t'),date('Y'));
            $xData = array();
            for ($i=0; $i < $days ; $i++) { 
                $xData[] = date('Y-m-d',$startTime+$i*86400); //每隔一天赋值给数组
            }
            // dump($xData);die;
            // 构造当月的数据 y轴小程序名称 x轴三个数据
            $isEnter = [];
            $isNotEnter =[];
            $isAll = [];
            // 查询数据
            $totalClick = $mini_click_count -> field('mini_id, is_enter,click_time,clicks')->where('mini_id',$select) -> where('click_time', 'between',[$startTime,$endTime])->where('is_enable',1)->where('is_delete',0)-> select();
            $totalClick = collection($totalClick) -> toArray();
            // dump($totalClick);die;
            
            // 非空判断
            if(!empty($totalClick)){             
                // 数组里判断时间
                foreach ($xData as $k => $v) {
                    $isEnter[$k] = 0;
                    $isNotEnter[$k] = 0;
                    $isAll[$k] = 0;
                    $begin = strtotime($v);
                    $end = strtotime($v)+86400;
                    foreach ($totalClick as $ke => $va) {
                        if ($va['click_time'] >= $begin&&$va['click_time'] < $end) {
                            if ($va['is_enter'] == 1) {
                                $isEnter[$k] += $va['clicks'];
                            }else if ($va['is_enter'] == 0) {
                                $isNotEnter[$k] += $va['clicks'];
                            }
                            $isAll[$k] += $va['clicks'];
                        }
                    }
                }
                // dump($isAll);die;
                // dump($isNotEnter);die;
            }else{
                // 该小程序无点击量数据
                // 构造当月的数据 y轴小程序名称 x轴三个数据
                $isEnter = [];
                $isNotEnter =[];
                $isAll = [];
                foreach ($xData as $k => $v) {
                    $isEnter[$k] = 0;
                    $isNotEnter[$k] = 0;
                    $isAll[$k] = 0;
                }                             
            }
            // y轴数据
            $data1 = array(
                'data' => $isEnter,
                'name' => '已跳转'
            );
            $data2 = array(
                'data' => $isNotEnter,
                'name' => '未跳转'
            );
            $data3 = array(
                'data' => $isAll,
                'name' => '点击总量'
            );                
            $seriesData = array();
            array_push($seriesData, $data1);
            array_push($seriesData, $data2);
            array_push($seriesData, $data3);
            $title = '小程序当月点击量统计';
            $data = array(
                'seriesData' => $seriesData,
                'title'      => $title,
                'xData'      => $xData,
            );                         
            return json($data);
        }
    }

    /**
     * [getSearchStats 根据日期选择数据]
     * @Author   Mr.fang
     * @DateTime 2018-07-05
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    function getSearchStats(Request $request){
        $startTime = strtotime($request -> param('startTime'));
        $endTime = strtotime($request -> param('endTime')) + 86400;
        $mini_id = intval($request -> param('mini_id'));
        // 每隔5天取日期
        $days = ($endTime-$startTime)/86400;
        $xData = array();
        // if($days>30&$days<60){
        //     $step = 2;
        // }
        // if($days>60&$days<93){
        //     $step = 5;
        // }
        // if($days<=30){
        //     $data = '300';
        //     return json($data);
        // }
        for ($i = $startTime; $i <= $endTime; $i += 86400 ) {

            $xData [] = date('Y-m-d',$i); //每隔step天赋值给数组
            // if(($endTime-$i)<$step*86400){
            //     $xData [] = date('Y-m-d',$endTime); //最后几天的数据  
            // }
        }
        // 构造当月的数据 y轴小程序名称 x轴三个数据
        $isEnter = [];
        $isNotEnter =[];
        $isAll = [];
        $mini_click_count = new Mini_click_count;
        // 查询数据
        $totalClick = $mini_click_count -> field('mini_id, is_enter,click_time,clicks')->where('mini_id',$mini_id) -> where('click_time', 'between',[$startTime,$endTime])->where('is_enable',1)->where('is_delete',0) -> select();
        $totalClick = collection($totalClick) -> toArray();
        // dump($totalClick);die;
        // 非空判断
        // dump(strtotime("2018-10-7"));
        // dump($xData);die;
        if(!empty($totalClick)){
            // 数组里判断时间
            foreach ($xData as $k => $v) {
                $isEnter[$k] = 0;
                $isNotEnter[$k] = 0;
                $isAll[$k] = 0;
                foreach ($totalClick as $ke => $va) {
                    if ($va['click_time'] >= strtotime($v) && $va['click_time'] <= strtotime($v) + 86400) {
                        if ($va['is_enter'] == 1) {
                            $isEnter[$k] += $va['clicks'];
                        }else if ($va['is_enter'] == 0) {
                            $isNotEnter[$k] += $va['clicks'];
                        }
                        $isAll[$k] += $va['clicks'];
                    }
                }
            }           
            // dump($isEnter);die;
            // dump($isNotEnter);die;
            // dump($isAll);die;
        }else{
            // 构造当月的数据 y轴小程序名称 x轴三个数据
            $isEnter = [];
            $isNotEnter =[];
            $isAll = [];
            foreach ($xData as $k => $v) {
                $isEnter[$k] = 0;
                $isNotEnter[$k] = 0;
                $isAll[$k] = 0;
            }           
        }
        // y轴数据
        $data1 = array(
            'data' => $isEnter,
            'name' => '已跳转'
        );
        $data2 = array(
            'data' => $isNotEnter,
            'name' => '未跳转'
        );
        $data3 = array(
            'data' => $isAll,
            'name' => '点击总量'
        );                
        $seriesData = array();
        array_push($seriesData, $data1);
        array_push($seriesData, $data2);
        array_push($seriesData, $data3);
        $title = '小程序点击量统计';
        $data = array(
            'seriesData' => $seriesData,
            'title'      => $title,
            'xData'      => $xData,
        );
        return json($data);   
    }


    /**
     * [miniprocatagory 小程序分类页面]
     * @Author   Mr.fang
     * @DateTime 2018-07-06
     * @version  V1.0.0
     * @return   [html]     [小程序分类页面]
     */
    public function miniprocatagory(){
        $catagory = new Catagory;
        $catagoryData = $catagory -> field('catagory_id,name')->where('father_id',0)->where('is_delete',0) ->select();
        $this->assign('catagoryData',$catagoryData);    
        return $this->fetch();
    }

    /**
     * [catagoryZtree 分类数据]
     * @Author   Mr.fang
     * @DateTime 2018-07-06
     * @version  V1.0.0
     * @return   [ary]     [返回值]
     */
    public function catagoryZtree(){
        $catagory = new Catagory;
        $data = $catagory -> field('catagory_id,father_id,name,is_active') ->where('is_delete',0) ->order('orderby desc')->select();
        // 先进行排序 父级与子级
        // 返回ztree数据
        $catagoryArr = array();
        foreach ($data as $key => $value) {
            $ary=array(
                'id'=>$value['catagory_id'],
                'pId'=>$value['father_id'],
                'name'=>$value['name'],
                'open'=>true,
            );
            array_push($catagoryArr, $ary);
        }
        return json($catagoryArr);
    }

    /**
     * [addCatagory 添加小程序分类]
     * @Author   Mr.fang
     * @DateTime 2018-07-06
     * @version  V1.0.0
     */
    public function addCatagory(Request $request){
        $father_id = intval($request -> param('catagory_father'));
        if($father_id == 0){
            $add['father_id'] = 0;
        }else{
            $add['father_id'] = intval($request -> param('catagory_cat_id'));
        }
        // 是否存在session
        if(Session::has('miniprosrc')){
            // 取session值
            $source = ROOT_PATH.Session::get('miniprosrc');
            // dump($source);die;
            // 新的路径,取session值
            $str = substr_replace(Session::get('miniprosrc'),'catagory',13,9);
            $str3 = substr($str,0,31);            
            // dump($str3);die;
            // 创建文件夹
            if(!file_exists(ROOT_PATH . $str3)){
                mkdir(ROOT_PATH . $str3); 
            }            
            // 框架应用根目录/public/catagory/目录
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
            Session::delete('miniprosrc');

            $add['name'] = htmlspecialchars($request -> param('catagory_name'));
            $add['orderby'] = intval($request -> param('catagory_orderby'));
            $add['is_active'] = intval($request -> param('catagory_active'));
            $add['create_time'] = time();
            // 调用公共函数，参数false为新增
            // dump(123);die;
            $insert = saveData('catagory',$add,false);
            if($insert){
                return objReturn(200,'新增成功');
            }else{
                return objReturn(300,'新增失败');
            }
        }
            return objReturn(300,'新增失败，请上传图片！');
    }    

    /**
     * [selectCatagory 选择节点信息]
     * @Author   Mr.fang
     * @DateTime 2018-07-06
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function selectCatagory(Request $request){
        $cat_id = intval($request -> param('cat_id'));
        $catagory = new Catagory;
        $data = $catagory ->field('father_id,name,is_active,orderby,pic') ->where('catagory_id',$cat_id)->find();
        if($data){
            return objReturn(200,'success',$data);
        }else{
            return objReturn(300,'failed',$data);
        }
    }

    /**
     * [editCatagory 修改节点信息]
     * @Author   Mr.fang
     * @DateTime 2018-07-06
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function editCatagory(Request $request){
        // 是否存在session
        if(Session::has('miniprosrc')){
            // 取session值
            $source = ROOT_PATH.Session::get('miniprosrc');
            // dump($source);die;
            // 新的路径,取session值
            $str = substr_replace(Session::get('miniprosrc'),'catagory',13,9);
            $str3 = substr($str,0,31);            
            // dump($str3);die;
            // 创建文件夹
            if(!file_exists(ROOT_PATH . $str3)){
                mkdir(ROOT_PATH . $str3); 
            }            
            // 框架应用根目录/public/catagory/目录
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
            Session::delete('miniprosrc');
        }        
        if($request->param('catagory_father_id')!=10001){
            $update['catagory_id'] = intval($request->param('catagory_cat_id'));
            $update['father_id'] = intval($request->param('catagory_father_id'));
            $update['name'] = $request->param('catagory_cat_name');
            $update['orderby'] = intval($request->param('catagory_cat_orderby'));
            $update['is_active'] = intval($request->param('catagory_cat_active'));
            // 调用公共函数，参数true为更新
            $update = saveData('catagory',$update,true);        
            if($update){
                return objReturn(200,'修改成功！');
            }else{
                return objReturn(300,'修改失败！');
            }           
        }else{
            $update['catagory_id'] = intval($request->param('catagory_cat_id'));
            $update['name'] = $request->param('catagory_father_name');
            $update['orderby'] = intval($request->param('catagory_father_orderby'));
            $update['is_active'] = intval($request->param('catagory_father_active'));
            // 调用公共函数，参数true为更新
            $update = saveData('catagory',$update,true);        
            if($update){
                return objReturn(200,'修改成功！');
            }else{
                return objReturn(300,'修改失败！');
            } 
        }
    }

    /**
     * [delFather 删除父节点与子节点]
     * @Author   Mr.fang
     * @DateTime 2018-07-06
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function delFather(Request $request){
        $father_id = intval($request->param('catagory_cat_id'));
        $catagory = new Catagory;
        $dell['is_delete'] = 1;
        $res = $catagory ->where('father_id',$father_id) ->update($dell);

        $del['catagory_id'] = intval($request->param('catagory_cat_id'));
        $del['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $delete = saveData('catagory',$del,true);
        if($delete){
            return objReturn(200,'删除成功！');
        }else{
            return objReturn(300,'删除失败！');
        }
    }

    /**
     * [delCat 只删除子节点]
     * @Author   Mr.fang
     * @DateTime 2018-07-06
     * @version  V1.0.0
     * @return   [ary]     [返回值]
     */
    public function delCat(Request $request){
        $del['catagory_id'] = intval($request->param('catagory_cat_id'));
        $del['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $delete = saveData('catagory',$del,true);
        if($delete){
            return objReturn(200,'删除成功！');
        }else{
            return objReturn(300,'删除失败！');
        }       
    }    

    /**
     * [miniproorderby 小程序排行]
     * @Author   Mr.fang
     * @DateTime 2018-07-10
     * @version  V1.0.0
     * @return   [type]     [返回数据]
     */
    public function miniproorderby(){
        // 调用公共函数
        $orderbydata = getRank(true,null);
        // dump($orderbydata);die;
        $this->assign('orderbydata',$orderbydata);
        return $this->fetch();
    }

    /**
     * [startOrderby 启用]
     * @Author   Mr.fang
     * @DateTime 2018-07-10
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function startOrderby(Request $request){       
        if ($request->isPost()) {
            $where['idx'] = intval($request->param('id'));
            $where['is_active'] = 1;
           // 调用公共函数，参数true为更新
            $update = saveData('rank',$where,true);
            if($update){
                return objReturn(200,'启用成功');
            }else{
                return objReturn(300,'启用失败');
            }  
        }
    }

    /**
     * [stopOrderby 停用]
     * @Author   Mr.fang
     * @DateTime 2018-07-10
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function stopOrderby(Request $request){
        $where['idx'] = intval($request->param('id'));
        $where['is_active'] = 0;
       // 调用公共函数，参数true为更新
        $update = saveData('rank',$where,true);
        if($update){
            return objReturn(200,'停用成功');
        }else{
            return objReturn(300,'停用失败');
        }
    }

    /**
     * [delOrderby 删除]
     * @Author   Mr.fang
     * @DateTime 2018-07-10
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function delOrderby(Request $request){     
        $del['idx'] = intval($request->param('id'));
        $del['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $update = saveData('rank',$del,true);
        if($update){
            return objReturn(200,'删除成功');
        }else{
            return objReturn(300,'删除失败');
        }
    }

    /**
     * @return 添加miniproorderbyadd界面
     */
    public function miniproorderbyadd(){  
        // 调用公共函数
        $miniprodata = getAllMini('mini_id,name',false);
        $this->assign('miniprodata',$miniprodata);  
        return $this->fetch();
    } 

    /**
     * [addOrderby 添加]
     * @Author   Mr.fang
     * @DateTime 2018-07-10
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function addOrderby(Request $request){
        $where['mini_id'] = intval($request->param('mini_id'));
        $where['is_active'] = intval($request->param('active'));
        $where['orderby'] = intval($request->param('orderby'));
        $where['update_time'] = time();
        // 先查询数据是否超过50条
        $data = getRank(false,null);
        // dump($data);die;
        if(sizeof($data)<49){
           // 调用公共函数，参数false为新增
            $insert = saveData('rank',$where,false);
            if($insert){
                return objReturn(200,'添加成功');
            }else{
                return objReturn(300,'添加失败');
            }
        }
            return objReturn(300,'添加失败,数量超过50个！');
    }

    /**
     * [editOrderby 修改]
     * @Author   Mr.fang
     * @DateTime 2018-07-10
     * @version  V1.0.0
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function editOrderby(Request $request){
        $where['idx'] = intval($request -> param('idx'));
        $where['orderby'] = intval($request->param('orderby'));
        $where['update_time'] = time();
       // 调用公共函数，参数true为更新
        $update = saveData('rank',$where,true);
        if($update){
            return objReturn(200,'修改成功');
        }else{
            return objReturn(300,'修改失败');
        }        
    }



}


                        