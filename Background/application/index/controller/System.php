<?php
namespace app\index\controller;

use \think\Controller;
use \think\File;
use \think\Request;
use \think\Session;

use app\index\model\System_setting;

class System extends Controller
{
    /**
     * 插入或更新小程序基本信息
     * @param   int $idx            ID
     * @param   string $mini_name   小程序名字
     * @param   string $mini_color  导航颜色
     * @param   int $logifee        邮费
     * @param   int $logi_free_fee  满金额包邮费用
     * @param   int $service_phone  客服电话
     * @param   int $mch_distance   门店配送距离(米)
     * @param   file $layer_img     小程序首页弹出层图片
     * @param   int $layer_nav_type 跳转类型
     * @param   int $layer_nav_id   跳转对应的id
     * @return  result              更新结果
     */
    public function editProgram(Request $request)
    {
        $system_setting = new System_setting;
        $data = $request->except(['idx', 'old_layer_img', 'layer_img'], 'post');
        $idx = $request->param('idx');
        if ($data['is_layer_show'] == 1) {
            //图片上传
            $file = $request->file('layer_img');
            //判断有没传图片,没传使用原来的图片
            if (!$file) {
                $oldImg = $request->param('old_layer_img');
                if (empty($oldImg)) {
                    return objReturn(400, '请选择首页弹出界面封面');
                }
                $dir = pathinfo(pathinfo($oldImg, PATHINFO_DIRNAME), PATHINFO_BASENAME);
                $data['layer_img'] = '/img/setting/' . $dir . '/' . pathinfo($oldImg, PATHINFO_BASENAME);
            } else {
                //专栏封面图片存储目录
                $dir  = '.' . DS . 'static' . DS . 'img' . DS . 'setting' . DS;
                $info = $file->move($dir);
                if ($info) {
                    $saveName          = $info->getSaveName();
                    $data['layer_img'] = '/img/setting/' . $saveName;
                } else {
                    return objReturn(400, $file->getError());
                }
            }
        } else {
            $data['layer_img'] = '';
            $data['layer_nav_type'] = 0;
            $data['layer_nav_id'] = 0;
        }
        $data['update_at'] = time();
        $data['update_by'] = Session::get('admin_id');
        $result = $system_setting->where(['idx' => $idx])->update($data);
        if ($result) {
            return objReturn(0, '保存成功!');
        } else {
            return objReturn(0, '保存失败!');
        }
    }

    /**
     * 小程序编辑界面
     * @return  array  小程序基本信息数据
     */
    public function setprogram(Request $request)
    {
        $system_setting = new System_setting;
        $systemSettingData = $system_setting->order('idx asc')->find();
        $systemSettingData['layer_img'] = empty($systemSettingData['layer_img']) ? '' : '/static' . $systemSettingData['layer_img'];
        $systemSettingData['logi_fee'] = number_format($systemSettingData['logi_fee'], 2);
        $this->assign('data', $systemSettingData);
        return $this->fetch();
    }
}
