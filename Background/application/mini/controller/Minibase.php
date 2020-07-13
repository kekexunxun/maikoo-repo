<?php

/**
 * @author Locked
 * createtime 2018-05-03
 */

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\Session;
use think\File;

use app\index\model\User;
use app\index\model\Banner;
use app\index\model\Course;
use app\index\model\Clause;
use app\index\model\Msg;

class Minibase extends Controller
{

    /**
     * 获取用户openID
     * 
     * @param string code 登陆时wx.login返回的code
     * @return json 用户openid
     */
    public function getUserOpenid($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . config('APPID') . "&secret=" . config('APPSECRET') . "&js_code=" . $code . "&grant_type=authorization_code";
        $info = file_get_contents($url);
        $info = json_decode($info);
        $info = get_object_vars($info);
        return $info['openid'];
    }

    /**
     * 获取用户信息
     *
     * @param Request $request
     * @return void
     */
    public function getUserAccount()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $openid = request()->param('openid');
        $code = request()->param('code');
        // 如果没有openid 则需要先获取openid
        if (empty($openid)) $openid = $this->getUserOpenid($code);
        
        // 根据openid 去后台数据库取值
        // 教师和用户是两张表
        $userInfo = Db::name('user')->where('openid', $openid)->field('uid, status, openid')->find();
        // 用户身份 0 是学生（家长） 1 是老师
        $userType = $userInfo ? 0 : 1;
        if ($userType == 1) {
            $userInfo = Db::name('teacher')->where('openid', $openid)->field('teacher_id as tid, openid, status')->find();
        }
        // 如果查到用户存在，但是用户已被删除
        if ($userInfo && $userInfo['status'] == 3) return objReturn(403, 'User Deleted', ['openid' => $openid]);
        // 如果未查询到用户 则该用户为新用户
        if (!$userInfo) {
            $userInfo = [];
            $userInfo['openid'] = $openid;
            return objReturn(0, 'New User', $userInfo);
        }
        // 有查到用户要判断用户认证状态
        $userInfo['isAuth'] = $userInfo['status'] == 2 ? true : false;
        // $userInfo['userType'] = 1;
        $userInfo['userType'] = $userType;
        return objReturn(0, 'Get UserInfo Success', $userInfo);
    }


    /**
     * 获取当前系统的用户协议
     *
     * @return void
     */
    public function getCaluse()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $uid = intval(request()->param('uid'));
        if (empty($uid)) return objReturn(400, 'Invaild Param');
        $clause = new Clause;
        $clauseInfo = $clause->where('idx', 1)->value('clause');
        $clauseInfo = htmlspecialchars_decode($clauseInfo);
        return objReturn(0, 'success', $clauseInfo);
    }

    /**
     * 获取门店详情
     *
     * @return void
     */
    public function getStoreInfo()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $openid = request()->param('openid');
        if (empty($openid)) return objReturn(400, 'Invaild Param');

        $storeInfo = Db::name('system_setting')->where('idx', 1)->value('store_info');
        return objReturn(0, 'success', $storeInfo);
    }

    /**
     * 获取小程序首页详情
     *
     * @param Request $request
     * @return void
     */
    public function getIndex()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $uid = intval(request()->param('uid'));
        if (empty($uid)) return objReturn(400, 'Invaild Param');

        $banner = new Banner;
        $bannerField = "img";
        $bannerList = getBanner($bannerField, false);
        if (!$bannerList) return objReturn(0, 'No banner');
        $bannerList = array_values($bannerList);

        // 获取 最近的 3 条公告
        $msg = new Msg;
        $notice = $msg->where('msg_type', 0)->where('status', 2)->field('send_at, msg_content')->limit(1)->order('created_at desc')->find();
        if($notice && time() - $notice['send_at'] < 86400 * 5){
            $noticeContent = $notice['msg_content'];
        }else{
            $noticeContent = "";
        }
        $data['banner'] = $bannerList;
        $data['notice'] = $noticeContent;

        return objReturn(0, 'success', $data);
    }

    /**
     * 获取系统设置
     *
     * @return void
     */
    public function getSystemSetting()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $openid = request()->param('openid');
        if (empty($openid)) return objReturn(400, 'Invaild Param');
        $setting = Db::name('system_setting')->where('idx', 1)->field('mini_name, share_text, service_phone, notice')->find();
        return objReturn(0, 'success', $setting);
    }

    public function test(){
        dump(Cache::get('prepayCache'));
    }

}