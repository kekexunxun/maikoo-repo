<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;

use app\index\model\User;
use app\index\model\User_profile;
use app\index\model\Clause;
use app\index\model\Merchant;
use app\index\model\System_setting;

class Minibase extends Controller
{

    /**
     * 用户微信信息入库
     * 
     * @param array userInfo
     * @param string openid
     * @return json 是否插入成功成功
     */
    public function setUserInfo(Request $request)
    {
        $uid = intval($request->param('uid'));
        if (!isset($uid)) {
            return objReturn(401, "Invaild Param");
        }
        // 有一个Openid 的缓存array，如果已经将该用户数据插入过，在缓存中就会体现
    	// 判断缓存库中是否有该openid
    	// 获取用户信息并入库
        $user_profile = new User_profile;

        $userProFileExist = $user_profile->where('uid', $uid)->count();
        // if ($userProFileExist == 1) {
        //     return objReturn(0, 'User Already Authed');
        // }

        $userInfo = $request->param('userInfo/a');
        $userInfo['created_at'] = time();
        $userInfo['uid'] = $uid;
        $userInfo['nickname'] = $userInfo['nickName'];
        $userInfo['avatar_url'] = $userInfo['avatarUrl'];
        unset($userInfo['nickName']);
        unset($userInfo['avatarUrl']);
        if ($userProFileExist) {
            $userInfo['update_at'] = time();
            $update = $user_profile->update($userInfo);
            unset($userInfo['update_at']);
        } else {
            $update = $user_profile->insert($userInfo);
        }
        if (!$update) {
            return objReturn(402, 'failed', $update);
        }
        // 更新user表
        $user = new User;
        $user->update(['uid' => $uid, 'is_auth' => 1]);
        // 更新用户信息到缓存
        $userAccount = Cache::get('userAccount');
        foreach ($userAccount as &$info) {
            if ($info['uid'] == $uid) {
                $info['isAuth'] = true;
                $info['userInfo'] = $userInfo;
                break 1;
            }
        }
        Cache::set('userAccount', $userAccount, 0);
        return objReturn(0, 'success');
    }

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
     * 将用户登陆信息插入数据库中
     * 将用户点击小程序的信息插入到数据库中
     * 
     * @param Request $request
     * @return void
     */
    public function setUserLog(Request $request)
    {
        $logs = $request->param('logs/a');
        $miniLogs = $request->param('miniLogs/a');
        $columnLogs = $request->param('columnLogs/a');
        $catLogs = $request->param('catLogs/a');
        $articleLogs = $request->param('articleLogs/a');
        $openid = $request->param('openid');
        $logArr = array();
        arsort($logs);
        foreach ($logs as $k => $v) {
            $array['open_time'] = date('Y-m-d H:i:s', $v);
            $array['openid'] = $openid;
            $logArr[] = $array;
        }
        $usercount = new Usercount;
        $usercount->saveAll($logArr);
        // 如果有小程序点击的log就存入对应数据库
        if ($miniLogs && count($miniLogs) > 0) {
            foreach ($miniLogs as $k => $v) {
                $miniLogs[$k]['create_time'] = time();
                $miniLogs[$k]['openid'] = $openid;
            }
            $mini_click_count = new Mini_click_count;
            $mini_click_count->saveAll($miniLogs);
        }
    }

    /**
     * 获取用户信息
     *
     * @param Request $request
     * @return void
     */
    public function getUserAccount()
    {
        $openid = request()->param('openid');
        $code = request()->param('code');

        // 如果没有openid 则需要先获取openid
        if (empty($openid)) $openid = $this->getUserOpenid($code);

        // 根据openid查询对应的信息
        $userAccount = Cache::get('userAccount');
        if ($userAccount) {
            $userInfo = [];
            foreach ($userAccount as $k => $v) {
                if ($v['openid'] == $openid) {
                    if (isset($v['userInfo'])) unset($v['userInfo']);
                    // 获取用户的会员信息
                    // if ($v['isMember'] && $v['member_expire_time'] < time()) {
                    //     $userAccount[$k]['isMember'] = false;
                    //     Db::name('user')->where('uid', $v['uid'])->update(['is_member' => 0]);
                    // }
                    // Cache::set('userAccount', $userAccount, 0);
                    $userInfo = $v;
                    return objReturn(0, 'Get UserInfo Success', $userInfo);
                }
            }
        } else {
            $userAccount = [];
        }
        // 如果没有找到用户信息就新建
        $user['isAuth'] = false;
        $user['isMember'] = false;
        $user['points'] = 0;
        $user['openid'] = $openid;
        $user['expire_time'] = time() + 7200;
        $user['uid'] = Db::name('user')->insertGetId(['openid' => $user['openid'], 'created_at' => time()]);
        $userAccount[] = $user;
        Cache::set('userAccount', $userAccount, 0);
        return objReturn(0, 'New User', $user);
    }

    /**
     * 获取当前系统的用户协议
     *
     * @return void
     */
    public function getCaluse()
    {
        $clause = new Clause;
        $clauseDetail = $clause->where('idx', 1)->value('clause');
        $clauseInfo['clause'] = htmlspecialchars_decode($clauseDetail);
        return objReturn(0, 'success', $clauseInfo);
    }

    /**
     * 获取系统问答列表
     *
     * @return void
     */
    public function getQuestion()
    {
        $uid = intval(request()->param('uid'));
        if (!$uid) {
            return objReturn(401, 'failed');
        }
        $qaList = getSysQA(false);
        return objReturn(0, 'success', $qaList);
    }

    /**
     * 根据用户传递回来的 lat 和 lun 对商户地址进行距离计算
     * 根据 系统的setting表 获取周围指定X Km的店铺信息
     * mch状态 0未营业 1正常营业 2暂停营业 3已删除
     *
     * @return void
     */
    public function getMchAround()
    {
        if (request()->isGet()) return objReturn(400, 'Invaild Method');
        $userLat = request()->param('lat');
        $userLng = request()->param('lng');

        $system_setting = new System_setting;
        $distanceLimit = $system_setting->where('idx', 2)->value('mch_distance');

        $merchant = new Merchant;
        $mchList = $merchant->where('status', 2)->field('mch_id, mch_name, location')->select();
        if (!$mchList || count($mchList) == 0) return objReturn(400, 'No Mch');
        $mchList = collection($mchList)->toArray();
        // 获取用户定位 指定范围内的经纬度数据
        $range = getRange($userLng, $userLat, $distanceLimit);

        foreach ($mchList as $k => $v) {
            if (empty($v['location'])) {
                unset($mchList[$k]);
                continue;
            }
            // 拆分商家location
            $location = explode(',', $v['location']);
            if ($location[0] >= $range['minLat'] && $location[0] <= $range['maxLat'] && $location[1] >= $range['minLng'] && $location[1] <= $range['maxLng']) {
                $mchList[$k]['distance'] = getDistance($location[1], $location[0], $userLng, $userLat, 1, 0);
            } else {
                unset($mchList[$k]);
            }
        }
        return objReturn(0, 'success', $mchList);
    }

    public function test()
    {
        $userAccount = Cache::get('userAccount');
        dump($userAccount);
        // Cache::rm('userAccount');
        // $cartList = Cache::get('cartList');
        // dump($cartList);
        // Cache::rm('cartList');
        // $prepayCache = Cache::get('prepayCache');
        // $data = Cache::get('data');
        // dump($prepayCache);
        // dump($data);
        // Cache::rm('cartList');
    }

}