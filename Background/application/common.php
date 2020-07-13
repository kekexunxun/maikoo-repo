<?php


use \think\Request;
use \think\Cache;
use think\Db;

use app\index\model\User;
use app\index\model\Order;
use app\index\model\Course;
use app\index\model\Msg;
use app\index\model\Banner;
use app\index\model\Formid;
use app\index\model\Feedback;
use app\index\model\Classes;
use app\index\model\User_clock;
use app\index\model\Admin;
use app\index\model\Classes_user;
use app\index\model\Teacher;

/**
 * 通过用户ID获取用户相关信息
 *
 * @param int $uid 用户id
 * @param boolean $profile 是否需要查询用户资料
 * @param boolean $course 是否需要查询用户课程信息
 * @param boolean $clock 是否需要查询用户打卡记录
 * @return array 用户信息详情
 */
function getUserInfoById($uid)
{
    // 获取用户信息
    $user = new User;
    $field = "uid, openid, username, nickname, avatar_url, stu_no, grade, birth, phone, class_id, auth_name, user_gender";
    $userProfile = $user->field($field)->where('uid', $uid)->select();
    $userProfile = $userProfile && count($userProfile) > 0 ? collection($userProfile)->toArray() : null;
    $userInfo['profile'] = $userProfile[0];
    // 数据简单处理
    $userInfo['profile']['grade'] = $userInfo['profile']['grade'] == 1 ? '幼儿园' : '小学';
    $userInfo['profile']['user_gender'] = $userInfo['profile']['user_gender'] == 1 ? '男' : '女';

    return $userInfo;
}

/**
 * 构造返回数据
 *
 * @param int $code 返回码
 * @param string $msg 返回信息
 * @param array $data 返回的数据
 * @return json $data
 */
function objReturn($code, $msg, $data = null)
{
    if (!is_int($code) || !is_string($msg)) {
        return 'Invaild Param';
    }
    $res['code'] = $code;
    $res['msg'] = $msg;
    if ($data) {
        $res['data'] = $data;
    }
    return json($res);
}

/**
 * 更细数据库相关信息
 *
 * @param int $table 需要更新的表名
 * @param array $where 更新的字段
 * @param int $isUpdate 是更新还是新增
 * @return int $isSuccess 是否更新成功
 */
function saveData($table, $where, $isUpdate = true)
{
    if (!$table || !is_string($table) || !$where || !is_array($where) || $isUpdate && !is_bool($isUpdate)) {
        return 'Invaild Table';
    }

    // 表名
    $tableName = null;
    switch ($table) {
        case 'change':
            $tableName = new Course_change;
            break;
        case 'course':
            $tableName = new Course;
            break;
        case 'clock':
            $tableName = new User_clock;
            break;
        case 'user':
            $tableName = new User;
            break;
        case 'msg':
            $tableName = new Msg;
            break;
        case 'banner':
            $tableName = new Banner;
            break;
        case 'classes':
            $tableName = new Classes;
            break;
        case 'admin':
            $tableName = new Admin;
            break;
        case 'class_user':
            $tableName = new Classes_user;
            break;
        case 'user_clock':
            $tableName = new User_clock;
            break;
        case 'teacher':
            $tableName = new Teacher;
            break;
    }
    // 判断数据长度
    $isSuccess = $tableName->isUpdate($isUpdate)->save($where);
    // 结果返回
    return $isSuccess;
}

/**
 * 获取所有的课程分类
 *
 * @param string $field 需要查询的字段
 * @param boolean $isAll 是否查询全部的课程
 * @return void
 */
function getCourse($field = null, $isAll = true, $pageNum = null)
{
    $field = $field ? $field : 'course_id, course_name, course_brief, course_price, course_period, course_times, created_at, status, subject_id, sort';
    $status = $isAll ? [1, 2] : [1];
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    $course = new Course;
    $courseList = $course->where('status', 'in', $status)->field($field)->order('sort asc')->limit($limit)->select();
    if (!$courseList || count($courseList) == 0) {
        return null;
    }
    $courseList = collection($courseList)->toArray();

    // 获取所有科目
    $subjectList = Db::name('subject')->field('subject_id, subject_name')->select();

    foreach ($courseList as &$info) {
        if (isset($info['created_at'])) {
            $info['created_at'] = date('Y-m-d H:i:s', $info['created_at']);
        }
        if (isset($info['course_price'])) {
            $info['course_price'] = number_format($info['course_price'], 2);
        }
        if ($subjectList && isset($info['subject_id'])) {
            foreach ($subjectList as $k => $v) {
                if ($info['subject_id'] == $v['subject_id']) {
                    $info['subject_name'] = $v['subject_name'];
                    break 1;
                }
            }
        }
        
        // 获取课程所拥有的班级
        $info['course_classes'] = Db::name('classes')->where('course_id', $info['course_id'])->where('status', 'in', [1, 2])->count();
    }
    return $courseList;
}

/**
 * 获取所有的课程名称
 *
 * @param boolean $isAll 获取课程的状态
 * @param int $teacherId 教师ID
 * @return void
 */
function getClasses($isAll = true, $teacherId = null)
{
    $classes = new Classes;
    $status = $isAll ? [1, 2] : [2];
    $field = "class_id, class_name, class_time, class_day, course_id";
    $classList = $classes->where('teacher_id', $teacherId)->where('status', '<>', 3)->field($field)->select();
    if (!$classList || count($classList) == 0) {
        return null;
    }
    $classList = collection($classList)->toArray();
    // 对日期做处理
    foreach ($classList as &$info) {
        $info['day_conv'] = convertDay($info['class_day']);
    }
    return $classList;
}

function convertDay($day)
{
    switch ($day) {
        case 0:
            $dayConvert = "星期日";
            break;
        case 1:
            $dayConvert = "星期一";
            break;
        case 2:
            $dayConvert = "星期二";
            break;
        case 3:
            $dayConvert = "星期三";
            break;
        case 4:
            $dayConvert = "星期四";
            break;
        case 5:
            $dayConvert = "星期五";
            break;
        case 6:
            $dayConvert = "星期六";
            break;
        default:
            $dayConvert = "Not A Day";
    }
    return $dayConvert;
}

/**
 * 获取指定班级的学生
 *
 * @return void
 */
function getClassesStudent($classId = null, $isAll = true)
{
    $field = "uid, username";
    $classes_user = new User;
    $status = $isAll ? [1, 2] : [1];
    $stuList = $user->where('class_id', $classId)->field($field)->select();
    if (!$stuList || count($stuList) == 0) {
        return null;
    }
    $stuList = collection($stuList)->toArray();
    return $stuList;
}

/**
 * 根据课程ID查找课程详情
 *
 * @param int $courseId 课程ID
 * @param boolean $isAll 是否为有效课程
 * @return void
 */
function getCourseById($courseId, $isAll = true)
{
    if (!isset($courseId)) {
        return "Invaild CourseId";
    }

    $field = 'course_id, course_name, course_brief, course_desc, course_price, course_period, course_times, created_at, status, subject_id, sort';
    $status = $isAll ? [1] : [1, 2];

    $course = new Course;
    $courseInfo = $course->where('course_id', $courseId)->where('status', 'in', $status)->field($field)->select();
    if (!$courseInfo || count($courseInfo) == 0) {
        return null;
    }
    $courseInfo = collection($courseInfo)->toArray();

    $courseInfo = $courseInfo[0];
    $courseInfo['course_brief'] = $courseInfo['course_brief'];
    $courseInfo['course_name'] = $courseInfo['course_name'];
    $courseInfo['course_price'] = $courseInfo['course_price'];
    // 处理课程详情
    if (!empty($courseInfo['course_desc'])) {
        $descTemp = $courseInfo['course_desc'];
        $descTemp = explode(',', $descTemp);
        $descSort = [];
        foreach ($descTemp as &$desc) {
            $temp = explode(':', $desc);
            $desc = [];
            $desc['img'] = $temp[0];
            $desc['sort'] = $temp[1];
            $descSort[] = $temp[1];
        }
        array_multisort($descTemp, SORT_ASC, SORT_NUMERIC, $descSort);
        $courseInfo['course_desc'] = $descTemp;
    }
    return $courseInfo;
}

/**
 * 获取管理员发送的消息
 * 如果有传用户的uid则为查找系统消息以及该用户的相关信息
 *
 * @param string $msgType 需要查询的信息分类 0公告 1对指定用户发送
 * @param string $field 需要查询的字段
 * @param boolean $isAll 是否查看全部的消息
 * @param int $uid 用户的uid
 * @param int $pageNum 需要查看的页码
 * @return void
 */
function getMessage($msgType = 0, $field = null, $isAll = true, $uid = null, $pageNum = null)
{
    // if ($msgType == 1 && !$uid) {
    //     return "Invaild Param";
    // }
    $field = $field ? $field : 'msg_id, msg_type, msg_content, msg_img, target_uid, target_openid, send_at';
    $status = $isAll ? [1, 2] : [2];
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : "";

    $msg = new Msg;

    if ($msgType == 0) {
        $msgList = $msg->where('msg_type', $msgType)->where('status', 'in', $status)->field($field)->limit($limit)->order('created_at desc')->select();
    } elseif ($msgType == 1) {
        if (isset($uid)) {
            $msgList = $msg->where('msg_type', $msgType)->where('target_uid', $uid)->where('status', 'in', $status)->field($field)->order('created_at desc')->select();
        } else {
            $msgList = $msg->where('msg_type', $msgType)->where('status', 'in', $status)->field($field)->limit($limit)->order('created_at desc')->select();
        }
    }

    if (!$msgList || count($msgList) == 0) {
        return null;
    }
    $msgList = collection($msgList)->toArray();

    if (is_array($msgList) && count($msgList) > 0) {
        foreach ($msgList as &$info) {
            $info['msg_img'] = config('SITEROOT') . $info['msg_img'];
            $info['send_at'] = isset($info['send_at']) && !empty($info['send_at']) ? date('Y-m-d H:i', $info['send_at']) : '';
        }
    }

    return $msgList;
}

/**
 * 获取指定的MSG
 *
 * @param int $msgId
 * @param string $field
 * @param boolean $isInUse
 * @return void
 */
function getMsgById($msgId, $isAll = true)
{
    $status = $isAll ? [2] : [1, 2];
    $msg = new Msg;
    $msgInfo = $msg->alias('m')->join('art_admin a', 'm.send_by = a.id', 'LEFT')->join('art_classes c', 'm.class_id = c.class_id', 'LEFT')->where('m.msg_id', $msgId)->where('m.status', 'in', $status)->field('m.msg_id, m.msg_type, m.msg_content, m.msg_img, m.class_id, m.send_at, m.send_by, m.status, a.name as send_by_name, c.class_name, c.class_id, c.class_time, c.class_day')->select();
    if (!$msgInfo || count($msgInfo) == 0) {
        return null;
    }
    $msgInfo = collection($msgInfo)->toArray();
    $msgInfo = $msgInfo[0];
    $msgInfo['msg_img'] = config('SITEROOT') . $msgInfo['msg_img'];
    $msgInfo['send_at'] = date('Y-m-d H:i', $msgInfo['send_at']);
    $msgInfo['class_day_conv'] = convertDay($msgInfo['class_day']);
    return $msgInfo;
}

/**
 * 获取指定用户的班级详情
 *
 * @param int $uid 用户ID
 * @return void
 */
function getUserCourse($uid, $isAll = false)
{
    $status = $isAll ? [0, 1] : [1];
    $class_user = new Classes_user;
    $classList = $class_user->alias('cu')->join('classes c', 'cu.class_id = c.class_id', 'LEFT')->join('art_teacher t', 'c.teacher_id = t.teacher_id', 'LEFT')->join('art_course cse', 'c.course_id = cse.course_id', 'LEFT')->join('art_subject sub', 'cse.subject_id = sub.subject_id', 'LEFT')->where('cu.uid', $uid)->where('cu.status', 'in', $status)->field('cu.course_left_times, cu.renew_times, cu.course_end_at, cu.status as user_class_status, c.class_id, c.class_name, c.class_day, c.class_time, c.teacher_id, c.status as class_status, cse.course_name, cse.course_times, cse.course_price, cse.course_period, cse.status as course_status, sub.subject_name, t.teacher_name, t.teacher_phone, t.avatar_url, t.status as teacher_status')->select();
    if (!$classList) {
        return null;
    }
    $classList = collection($classList)->toArray();
    foreach ($classList as &$info) {
        // 判断课程结束时间
        $info['class_end_day_count'] = ceil(($info['course_end_at'] - time()) / 86400);
        // 判断是否显示续费按钮
        $info['isShowRenew'] = $info['class_status'] == 2 ? ((time() > $info['course_end_at'] - 86400 * 2) || ($info['course_left_times'] < 5)) : false;
        // 课程结束时间转换
        $info['course_end_at'] = date('Y-m-d', $info['course_end_at']);
        // 课程上课时间
        $info['class_day_conv'] = convertDay($info['class_day']);
        // 已打卡次数
        $info['class_clock_times'] = Db::name('user_clock')->where('uid', $uid)->where('class_id', $info['class_id'])->count();
        // 班级人数
        $info['class_stu_num'] = Db::name('classes_user')->where('class_id', $info['class_id'])->count();
        // 计算总课时
        $info['course_times'] = $info['course_times'] * ($info['renew_times'] + 1);
    }
    return $classList;
}

/**
 * 获取banner信息
 *
 * @param boolean $isAll 是否需要查询全部的banner
 * @return void
 */
function getBanner($isAll = true)
{
    $field = 'banner_id, img, status, sort';
    $status = $isAll ? [1, 2] : [1];
    $banner = new Banner;
    $bannerList = $banner->where('status', 'in', $status)->field($field)->order('created_at desc')->select();
    if (!$bannerList || count($bannerList) == 0) {
        return null;
    }
    $bannerList = collection($bannerList)->toArray();
    foreach ($bannerList as &$info) {
        $info['img'] = config('SITEROOT') . $info['img'];
    }
    return $bannerList;
}

/**
 * 获取打卡列表
 * 可通过courseId获取制定课程的用户打卡记录
 * 或通过uid获取用户的所有打卡记录
 *
 * @param int $uid 用户ID
 * @param int $classId 班级ID
 * @return void
 */
function getClockList($uid = null, $classId = null, $pageNum = null)
{
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    $user_clock = new User_clock;
    if (isset($uid) && isset($courseId)) {
        $clockList = $user_clock->alias('u')->join('art_classes c', 'u.class_id = c.class_id', 'LEFT')->join('art_teacher t', 'u.clock_by = t.teacher_id', 'LEFT')->field('u.idx, u.uid, u.class_id, u.clock_at, u.clock_type, u.clock_by, c.class_name, t.teacher_name, t.avatar_url')->where('u.uid', $uid)->where('u.class_id', $courseId)->limit($limit)->order('u.clock_at desc')->select();
    } elseif (isset($uid) && !isset($courseId)) {
        $clockList = $user_clock->alias('u')->join('art_classes c', 'u.class_id = c.class_id', 'LEFT')->join('art_teacher t', 'u.clock_by = t.teacher_id', 'LEFT')->field('u.idx, u.uid, u.class_id, u.clock_at, u.clock_type, u.clock_by, c.class_name, t.teacher_name, t.avatar_url')->where('u.uid', $uid)->limit($limit)->order('u.clock_at desc')->select();
    } elseif (!isset($uid) && isset($courseId)) {
        $clockList = $user_clock->alias('u')->join('art_classes c', 'u.class_id = c.class_id', 'LEFT')->join('art_teacher t', 'u.clock_by = t.teacher_id', 'LEFT')->field('u.idx, u.uid, u.class_id, u.clock_at, u.clock_type, u.clock_by, c.class_name, t.teacher_name, t.avatar_url')->where('u.class_id', $classId)->limit($limit)->order('u.clock_at desc')->select();
    } else {
        return null;
    }
    if (!$clockList || count($clockList) == 0) {
        return null;
    }
    foreach ($clockList as &$info) {
        switch ($info['clock_type']) {
            case 1:
                $info['clock_type_conv'] = '正常';
                break;
            case 2:
                $info['clock_type_conv'] = '迟到';
                break;
            case 3:
                $info['clock_type_conv'] = '旷课';
                break;
        }
        $info['clock_at'] = date('Y-m-d H:i:s', $info['clock_at']);
    }
    return $clockList;
}

/**
 * 给用户打卡操作
 *
 * @param array $clockArr 用户打卡的数组 其中包含uid, clock_by, clock_at, class_id, clock_type
 * @return boolean 是否打卡成功
 */
function makeClock($clockArr)
{
    if (!is_array($clockArr)) return false;
    $classIds = [];
    $uids = [];
    foreach ($clockArr as &$info) {
        $info['created_at'] = time();
        $classIds[] = $info['class_id'];
        $uids[] = $info['uid'];
    }
    // 判断当天是否为设置的打卡时间
    $currentDay = date('w', time());
    $classDay = Db::name('classes')->where('class_id', $clockArr[0]['class_id'])->value('class_day');
    if ($currentDay != $classDay) {
        return 603;
    }
    // 每人每天只能打卡一次
    $todayStartTime = strtotime('today');
    $todayEndTime = $todayStartTime + 86399;
    $isHaveClock = Db::name('user_clock')->where('uid', 'in', $uids)->where('clock_at', 'between', [$todayStartTime, $todayEndTime])->select();
    // 601代表今日已打卡
    if ($isHaveClock) return 601;
    // 1 获取用户原有课程
    $classUser = Db::name('classes_user')->whereOr('class_id', 'in', $classIds)->whereOr('uid', 'in', $uids)->field('idx, uid, class_id, course_left_times, course_end_at')->select();
    // 602代表无当前课程
    if (!$classUser) return 602;

    // 2 用户打卡后课程的处理
    foreach ($classUser as $k => $v) {
        foreach ($clockArr as $ke => $va) {
            if ($v['uid'] == $va['uid'] && $v['class_id'] == $va['class_id']) {
                if ($v['course_end_at'] > time() && $v['course_left_times'] > 0) {
                    $classUser[$k]['course_left_times'] -= 1;
                } else {
                    $va['courseOutOfTime'] = $v['course_end_at'] < time() ? true : false;
                    $va['noCourseLeftTimes'] = $v['course_left_times'] == 0 ? true : false;
                    $va['course_end_at'] = date('Y-m-d', $v['course_end_at']);
                    $va['course_left_times'] = $v['course_left_times'];
                    $notClockArr[] = $va;
                    unset($clockArr[$k]);
                }
                break 1;
            }
        }
    }
    // 事务处理
    $notClockArr = [];
    Db::startTrans();
    try {
        // 3 插入打卡记录
        $insert = Db::name('user_clock')->insertAll($clockArr);
        // dump($insert);die;
        // 4 更新课时记录
        $classes_user = new Classes_user;
        $update = $classes_user->isUpdate()->saveAll($classUser);
        // 提交事务
        Db::commit();
        if (!$classUser || !$insert || !$update) {
            throw new \Exception('Update Failed');
        }
    } catch (\Exception $e) {
        // 回滚事务
        Db::rollback();
        return false;
    }
    if (count($notClockArr) > 0) {
        return $notClockArr;
    }
    return true;
}

/**
 * 会员补卡操作
 *
 * @param obj $clockInfo
 * @return void
 */
function makeupClock($clockInfo)
{
    // 判断在制定日期内是否有进行打卡操作
    $searchStart = strtotime(date('Y-m-d', $clockInfo['clock_at']));
    $searchEnd = $searchStart + 86399;
    $isHaveClock = Db::name('user_clock')->where('uid', $clockInfo['uid'])->where('clock_at', 'between', [$searchStart, $searchEnd])->select();
    // 901代表补卡日期已打过卡，不允许补卡
    if ($isHaveClock) return 901;
    // 1 获取用户原有课程
    $classUser = Db::name('classes_user')->where('class_id', $clockInfo['class_id'])->where('uid', $clockInfo['uid'])->field('idx, uid, class_id, course_left_times, course_end_at')->find();
    // 902代表无当前课程
    if (!$classUser) return 902;
    // 903代表当前用户课程剩余打卡次数不足
    if ($classUser['course_left_times'] <= 0) {
        return 903;
    }
    // 2 用户补卡
    // 事务处理
    Db::startTrans();
    try {
        $classUser['course_left_times']--;
        $userClassStatus = $classUser['course_end_at'] < time() ? 2 : 1;
        // 3 插入补卡记录
        $insert = Db::name('user_clock')->insert($clockInfo);
        // 4 更新课时记录
        $classes_user = new Classes_user;
        $update = $classes_user->where('idx', $classUser['idx'])->update(['course_left_times' => $classUser['course_left_times'], 'status' => $userClassStatus]);
        // 提交事务
        Db::commit();
        if (!$insert || !$update) {
            throw new \Exception('Update Failed');
        }
    } catch (\Exception $e) {
        // 回滚事务
        Db::rollback();
        return $e->getMessage();
    }
    return true;
}

/**
 * 获取用户反馈
 *
 * @param boolean $isAll 是否获取全部状态的反馈
 * @param int $uid 用户ID
 * @param int $pageNum 需要查询的页码
 * @return void
 */
function getFeedBack($uid = null, $userType = 1, $pageNum = null)
{
    $field = "idx, uid, message, img, reply, created_at, reply_at, reply_by, status";
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    // 如果有传用户ID 则查询指定用户的反馈记录
    $feedback = new Feedback;
    if (isset($uid)) {
        $feedbackList = $feedback->where('uid', $uid)->where('status', 'in', [1, 2])->where('user_type', $userType)->field($field)->limit($limit)->order('created_at desc')->select();
    } else {
        $feedbackList = $feedback->where('status', 'in', [1, 2])->field($field)->limit($limit)->order('created_at desc')->select();
    }
    if (!$feedbackList || count($feedbackList) == 0) {
        return null;
    }
    $feedbackList = collection($feedbackList)->toArray();
    foreach ($feedbackList as &$info) {
        if (!empty($info['reply_at'])) {
            $info['reply_at'] = date('Y-m-d H:i:s', $info['reply_at']);
        }
        if (!empty($info['img'])) {
            $info['img'] = config('SITEROOT') . $info['img'];
        }
        if (!empty($info['reply'])) {
            $info['reply'] = htmlspecialchars_decode($info['reply']);
        }
        $info['created_at'] = date('Y-m-d H:i:s', $info['created_at']);
        $info['message'] = htmlspecialchars_decode($info['message']);
        $info['status_conv'] = $info['status'] == 1 ? '待回复' : '已回复';
    }
    return $feedbackList;
}

/**
 * 获取小程序AccessToken
 *
 * @return string $accessToken
 */
function getAccessToken()
{
    $accessToken = Cache::get('accessToken');
    if (!$accessToken) {
        $appid = "wx5556a337614ec0f6";
        $appsecret = "6febbeb04af3f26ab65c086ef847df4b";
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
        $info = file_get_contents($url);
        $info = json_decode($info);
        $info = get_object_vars($info);
        $accessToken = $info['access_token'];
        // $expirs_in = $info['expires_in'] - 100;
        // 将accessToken的有效期设置为3600s（一般情况下有效期7200s）
        Cache::set('accessToken', $accessToken, 6800);
    }

    return $accessToken;
}

/**
 * 获取用户的模板消息
 *
 * @param int $uid 用户id
 * @param int $courseId 课程id
 * @return string fromid
 */
function getFormId($uid)
{
    $formid = new Formid;
    $formID = $formid->where('uid', $uid)->where('is_active', 0)->field('idx, formid')->limit(1)->select();
    if (!$formID || count($formID) == 0) {
        return null;
    }
    $formID = collection($formID)->toArray();
    return $formID[0];
}

/**
 * 发送模板消息
 *
 * @param array $msgType 需要发送的 消息类型
 * @param array $msgId 需要发送的msgId
 * @param string $formId 模板消息ID
 * @param string $openid 用户的openid
 * @param string $content 发送的消息内容
 *
 * @param json $msg 发送的消息内容
 * @return void
 */
function sendTemplateMessage($msg)
{
    $accessToken = getAccessToken();
    $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $accessToken;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    // 这句话很重要 因为是SSL加密协议
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
    $output = curl_exec($ch);
    curl_close($ch);
    $info = json_decode($output);
    $info = get_object_vars($info);
    return $info['errcode'];
}

function genOrderSn()
{
    $typeSn = "";
    // 获取通用的时间字段 180827
    $timeStr = substr(date('Ymd', time()), 2);
    $microTime = explode('.', microtime());
    $microTime = substr($microTime[1], 0, 3);
    $typeSn = $timeStr . $microTime;
    return $typeSn;
}
