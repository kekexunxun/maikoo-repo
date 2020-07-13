<?php 
namespace  app\index\controller;

use \think\Controller;

use \think\Request;

use \think\Cache;

use \think\Db;

use \think\Session;

use \think\File;

use app\index\model\Bannerlist;

use app\index\model\Minipro;

use app\index\model\Column;

use app\index\model\Rate;

class Banner extends Controller{

    /**
     * @return bannerlist 界面
     */
    public function bannerlist(){
        $bannerlist = new Bannerlist();
        $banner = $bannerlist -> where('is_delete',0) -> select();
        $this->assign('banner',$banner);
        return $this->fetch();
    }


    /**
     * 获取当前banner id
     * @return json banner 启用结果
     */
    public function bannerStart(Request $request){
        if ($request->isPost()) {
            $where['idx'] = intval($request->param('id'));
            $where['is_active'] = 1;
            // 调用公共函数，参数true为更新
            $update = saveData('banner',$where,true);
            if($update){
                return objReturn(200,'启用成功');
            }else{
                return objReturn(300,'启用失败');
            }
        }
    }


    /**
     * 获取当前banner id
     * @return json banner 停用结果
     */
    public function bannerStop(Request $request){
        $where['idx'] = intval($request->param('id'));
        $where['is_active'] = 0;
        // 调用公共函数，参数true为更新
        $update = saveData('banner',$where,true);
        if($update){
            return objReturn(200,'停用成功');
        }else{
            return objReturn(300,'停用失败');
        }
    }


    /**
     * 获取当前banner id
     * @return json 删除banner结果
     */
    public function bannerDel(Request $request){
        $where['idx'] = intval($request->param('id'));
        $where['delete_time'] = time();
        $where['is_delete'] = 1;
        // 调用公共函数，参数true为更新
        $update = saveData('banner',$where,true);
        if($update){
            return objReturn(200,'删除成功');
        }else{
            return objReturn(300,'删除失败');
        }
    }


    /**
     * @return 添加banner 界面
     */
    public function banneradd(){
        // 调用公共函数
        $minipro_data = getAllMini('mini_id,name',true);
        $this->assign('minipro_data',$minipro_data);    
        // 调用公共函数
        $column_data = getAllColumn('idx,name',true,'','');
        $this->assign('column_data',$column_data);
        return $this->fetch();
    }


    /**
     * banner 添加
     * @return json 添加结果
     */
    public function BannerChange(Request $request){
        // 接收前端数据
        $add['is_active'] = intval($request -> param('banner_active'));
        $add['orderby'] = intval($request -> param('banner_order'));
        $add['navigate'] = intval($request -> param('banner_catagory'));
        // 判断类型
        $minipro = new Minipro();
        $column = new Column();
        if($add['navigate']==1){
            $add['navigate_id'] = intval($request -> param('banner_link'));
            $add['navigate_name'] = $minipro -> where('mini_id',$add['navigate_id']) ->value('name');
        }else if($add['navigate']==2){
            $add['navigate_id'] = intval($request -> param('banner_link2'));
            $add['navigate_name'] = $column-> where('idx',$add['navigate_id']) ->value('name');
        }
        $add['create_time'] = time();
        $banner = new Bannerlist(); 
        $file = request()->file('file');
        // 移动到框架应用根目录/public/image/banner目录下
            $info = $file->move(ROOT_PATH . 'public' .DS.'image'.DS. 'banner');
            if ($info) {
                $str = $info->getSaveName();
                // 路径
                $bannersrc = DS.'image'.DS.'banner'.DS. $str;
                $add['pic'] = $bannersrc;
                // $banner->insert($add);
                // 调用公共函数保存，参数false为新增
                $res = saveData('banner',$add,false);        
                if($res){
                    return objReturn(200,'success');              
                }else{
                    return objReturn(300,'error');
                }
            } else {
                return objReturn(300,'error'); 
            }
        }    




}

?>