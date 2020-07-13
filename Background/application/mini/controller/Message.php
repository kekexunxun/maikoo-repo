<?php

/**
 * 吸铁石美术小程序 消息通知有关方法
 * @author Locked
 * createtime 2018-05-03
 */

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;


class Message extends Controller
{

    public function getMsgInfo()
    {

        $msgId = intval(request()->param('msgId'));
        if (!$msgId) {
            return objReturn(401, 'Invaild MsgId');
        }

        $msgInfo = getMsgById($msgId, null, true);
        return objReturn(0, 'success', $msgInfo);
    }

    public function getMsgList()
    {
        $uid = intval(request()->param('uid'));
        $pageNum = intval(request()->param('pageNum'));
        $msgType = intval(request()->param('msgType'));
        $msgList = getMessage($msgType, null, false, $uid, $pageNum);

        return objReturn(0, 'success', $msgList);
    }


}