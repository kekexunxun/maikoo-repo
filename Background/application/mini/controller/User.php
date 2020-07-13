<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\File;
use app\index\model\Classes_user;
use app\index\model\Teacher;

class User extends Controller
{
    /**
     * 用户提交反馈
     *
     * @return void
     */
    public function submitFeedback()
    {
        if (!request()->isPost()) {
            return objReturn(400, 'Invaild Method');
        }
        $uid = intval(request()->param('uid'));
        if (empty($uid)) {
            return objReturn(400, 'Invaild Param');
        }
        
        // 判断是否有文件上传
        $file = request()->file('file');
        if ($file) {
            $targetDir = ROOT_PATH . 'public' . DS . 'feedback';
            // dump($targetDir);die;
            $save = $file->move($targetDir);
            if (!$save) {
                return objReturn(400, 'System Error', $save);
            }
        }

        // 数据上传
        $feedback['message'] = htmlspecialchars(request()->param('message'));
        $feedback['uid'] = $uid;
        $feedback['created_at'] = time();
        $feedback['user_type'] = intval(request()->param('usertype')) + 1;
        $feedback['img'] = $file ? DS . 'feedback' . DS . $save->getSaveName() : '';

        $insert = Db::name('feedback')->insert($feedback);
        if (!$insert) {
            return objReturn(400, 'Insert Failed', $insert);
        }
        return objReturn(0, 'Success', $insert);
    }

    /**
     *
     *
     * @return void
     */
    public function getUserFeedback()
    {
        if (!request()->isPost()) {
            return objReturn(400, 'Invaild Method');
        }
        $uid = intval(request()->param('uid'));
        if (empty($uid)) {
            return objReturn(400, 'Invaild Param');
        }
        $pageNum = intval(request()->param('pageNum'));
        $userType = intval(request()->param('usertype')) + 1;
        $feedbackList = getFeedBack($uid, $userType, $pageNum);
        return objReturn(0, 'success', $feedbackList);
    }

    /**
     * 获取指定用户信息
     *
     * @return void
     */
    public function getUserInfo()
    {
        if (!request()->isPost()) {
            return objReturn(400, 'Invaild Method');
        }
        $uid = intval(request()->param('uid'));
        if (empty($uid)) {
            return objReturn(400, 'Invaild Param');
        }

        $userType = request()->param('usertype');
        if ($userType == 0) {
            $userInfo = getUserInfoById($uid);
        } elseif ($userType == 1) {
            $userInfo = Db::name('teacher')->where('teacher_id', $uid)->field('teacher_id, teacher_name, teacher_phone, teacher_gender, teacher_birth, avatar_url, nickname, status')->find();
        }

        if (!$userInfo) {
            return objReturn(400, 'failed');
        }
        return objReturn(0, 'success', $userInfo);
    }

    /**
     * 获取用户信息
     * @param array userInfo
     * @param int $uid 用户uid
     * @return json 是否插入成功成功
     */
    public function setUserInfo()
    {
        if (!request()->isPost()) {
            return objReturn(400, 'Invaild Method');
        }
        $openid = request()->param('openid');
        if (empty($openid)) {
            return objReturn(400, 'Invaild Param');
        }
        $userInfo = request()->param('userInfo/a');
        $userType = request()->param('usertype');
        $userInfo['avatarUrl'] = urlencode(htmlspecialchars($userInfo['avatarUrl']));
        // 根据userType来构造数据
        if ($userType == 0) {
            $user['created_at'] = time();
            $user['nickname'] = $userInfo['nickName'];
            $user['avatar_url'] = $userInfo['avatarUrl'];
            $user['city'] = $userInfo['city'];
            $user['province'] = $userInfo['province'];
            $user['country'] = $userInfo['country'];
            $user['gender'] = $userInfo['gender'];
            $user['language'] = $userInfo['language'];
            $user['auth_at'] = time();
            $user['status'] = 2;
            $user['auth_name'] = htmlspecialchars(request()->param('authname'));
            $user['openid'] = $openid;
            $update = Db::name('user')->where('phone', request()->param('telnum'))->where('status', 1)->update($user);
        } elseif ($userType == 1) {
            $teacherInfo['avatar_url'] = $userInfo['avatarUrl'];
            $teacherInfo['nickname'] = $userInfo['nickName'];
            $teacherInfo['auth_name'] = htmlspecialchars(request()->param('authname'));
            $teacherInfo['openid'] = $openid;
            $teacherInfo['status'] = 2;
            $teacherInfo['auth_at'] = time();
            $update = Db::name('teacher')->where('teacher_phone', request()->param('telnum'))->where('status', 1)->update($teacherInfo);
        } else {
            return objReturn(400, 'Invaild Param');
        }

        return objReturn(0, 'success', $update);
    }

    /**
     * 获取用户的打卡记录
     *
     * @return void
     */
    public function getUserClock()
    {
        if (!request()->isPost()) {
            return objReturn(400, 'Invaild Method');
        }
        $uid = intval(request()->param('uid'));
        if (empty($uid)) {
            return objReturn(400, 'Invaild Param');
        }

        $pageNum = intval(request()->param('pageNum'));

        $clockList = getClockList($uid, null, $pageNum);

        return objReturn(0, 'success', $clockList);
    }

    /**
     * 获取用户课程相关信息
     *
     * @return void
     */
    public function getCourseInfo()
    {
        if (!request()->isPost()) {
            return objReturn(400, 'Invaild Method');
        }
        $uid = intval(request()->param('uid'));
        if (empty($uid)) {
            return objReturn(400, 'Invaild Param');
        }

        $classList = getUserCourse($uid);

        if (!$classList) {
            return objReturn(400, 'No Course');
        }
        return objReturn(0, 'success', $classList);
    }

    /**
     * 用户手机号修改
     *
     * @return void
     */
    public function changePhone()
    {
        if (!request()->isPost()) {
            return objReturn(400, 'Invaild Method');
        }
        $uid = intval(request()->param('uid'));
        if (empty($uid)) {
            return objReturn(400, 'Invaild Param');
        }

        $mobile = request()->param('mobile');
        $userType = request()->param('usertype');
        // 0 学生 1 老师
        if ($userType == 1) {
            $update = Db::name('teacher')->where('teacher_id', $uid)->update(['teacher_phone' => $mobile]);
        } elseif ($userType == 0) {
            $update = Db::name('user')->where('uid', $uid)->update(['phone' => $mobile, 'update_at' => time()]);
        } else {
            return objReturn(400, 'Invaild Param');
        }

        if ($update) {
            return objReturn(0, 'success');
        }
        return objReturn(400, 'failed');
    }
}
