<?php 
namespace  app\index\controller;

use \think\Controller;

use \think\Request;

use \think\Cache;

use \think\Db;

use \think\Session;

use \think\File;

use app\index\model\Banner;

class MiniBanner extends Controller{

    public function getIndexsad(){
        return "123";
    }

    /**
     * @return banner 界面
     */
    public function bannerlist(){

        $banner = new Banner();

        $data = $banner -> where('is_delete', 0)-> select();

        $this->assign('banner', $data);

        return $this->fetch();
    }


    /**

     * 获取当前banner id

     * @return json banner 启用结果

     */

    public function bannerStart(Request $request){

        $banner = new Banner();
        if ($request->isPost()) {

            $id = $request->param('id');

            $update = $banner->where('idx', $id)->update(['is_active' => '1']);

            if ($update) {

                $res['code'] = "10001";

                $res["msg"] = "启用成功";

            } else {

                $res['code'] = "10002";

                $res["msg"] = "启用失败";

            }

            return json($res);

        }
    }



    /**

     * 获取当前banner id

     * @return json banner 停用结果

     */

    public function bannerStop(Request $request){

        $banner = new Banner();
        $id = $request->param('id');

        $update = $banner->where('idx', $id)->update(['is_active' => '0']);

        if ($update) {

            $res['code'] = "10001";

            $res["msg"] = "停用成功";

        } else {

            $res['code'] = "10002";

            $res["msg"] = "停用失败";

        }

        return json($res);
    }



    /**

     * 获取当前banner id

     * @return json 删除banner结果

     */

    public function bannerDel(Request $request){

        $banner = new Banner();
        $id = $request->param('id');

        $update = $banner->where('idx', $id)->update(['is_delete' => '1']);

        if ($update) {

            $res['code'] = "10001";

            $res["msg"] = "删除成功";

        } else {

            $res['code'] = "10002";

            $res["msg"] = "删除失败";

        }

        return json($res);
    }



    /**

     * 获取bannner图片

     * @return json 图片地址

     */

    // public function getBanner(Request $request)

    // {

    //     $banner= Cache::get('banner');

    //          if (!$banner) {

    //              $bannerInfo = new Banner;

    //              $bannerList = $bannerInfo->find();

    //              $banner = $bannerList['banner'];

    //              Cache::set('banner', $banner, 0);

    //          }

    //      //获取bamner

    //     $res['banner'] = $banner;

    //     return json_encode($res);

    // }



    /**

     * banner 修改

     * @return json 修改结果

     */

    public function miniBannerChange(Request $request){
        $banner = new Banner();
        $add['goods_id'] = intval($request -> param('banner_link'));
        $add['is_active'] = intval($request -> param('banner_active'));
        $add['orderby'] = intval($request -> param('banner_order'));
        $add['type'] = intval($request -> param('banner_catagory'));

        $file = request()->file('file');

        // 移动到框架应用根目录/public/uploads/ 目录下

            $info = $file->move(ROOT_PATH . 'public' . DS . 'banner');

            if ($info) {

                $str = $info->getSaveName();

                $bannersrc = "/public/banner/" . $str;

//                
                $add['banner_src'] = $bannersrc;        

                $banner->insert($add);

                // $banner -> update(['banner_id' => 4,'banner_src' => $bannersrc]);

                $res["code"] = 200 ;

                $res["src"] = $bannersrc;

                $res["msg"] = "success";

            } else {

                $res["code"] = 400 ;

                $res["msg"] = "error";

            }

//                // 成功上传后 获取上传信息

//                // 输出 jpg

//                echo $info->getExtension();

//                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg

//                echo $info->getSaveName();

//                // 输出 42a79759f284b767dfcb2a0197904287.jpg

//                echo $info->getFilename();

            return json_encode($res);

    }    




}

?>