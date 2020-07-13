<?php
namespace app\index\controller;

use \app\index\model\User as UserDb;
use \app\index\model\User_profile;
use \think\Controller;


class User extends Controller
{
    /**
     * userlist 用户信息列表界面
     */
    public function userlist()
    {
        $userDb = new UserDb;
        return $this->fetch();
    }

    /**
     * userDetail 获取用户信息列表数据
     * @return   array   用户信息列表数据
     */
    public function userDetail()
    {
        $userDb        = new UserDb;
        $userProfileDb = new User_profile;
        $userData      = $userDb->field('uid,is_auth,is_member,points,created_at')->select();
        $str           = '';
        foreach ($userData as $k => $v) {
            $str .= $v['uid'] . ',';
        }
        $str             = rtrim($str, ',');
        $userProfileData = $userProfileDb->field('uid,nickName,avatar_url,city,province,gender,update_at')->where(['uid' => ['in', $str]])->select();
        $newData         = [];
        foreach ($userData as $k => $v) {
            foreach ($userProfileData as $kk => $vv) {
                if ($v['uid'] == $vv['uid']) {
                    $v               = $v->toArray();
                    $vv              = $vv->toArray();
                    $v['created_at'] = date('Y-m-d H:i:s', $v['created_at']);
                    if ($vv['gender'] == 1) {
                        $vv['gender'] = '男';
                    } else if ($vv['gender'] == 2) {
                        $vv['gender'] = '女';
                    } else {
                        $vv['gender'] = '保密';
                    }
                    $vv['update_at']  = date('Y-m-d H:i:s', $vv['update_at']);
                    $vv['avatar_url'] = ltrim($vv['avatar_url'], 'https://');
                    $vv['avatar_url'] = ltrim($vv['avatar_url'], 'http://');
                    $vv['avatar_url'] = 'https://' . $vv['avatar_url'];
                    $newData[]        = array_merge($v, $vv);
                }
            }
        }
        return json($newData);
    }

}