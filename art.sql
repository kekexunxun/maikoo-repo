/*
 Navicat Premium Data Transfer

 Source Server         : maikoo
 Source Server Type    : MySQL
 Source Server Version : 50637
 Source Host           : 120.79.145.110:3306
 Source Schema         : art

 Target Server Type    : MySQL
 Target Server Version : 50637
 File Encoding         : 65001

 Date: 07/09/2018 17:57:13
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for art_admin
-- ----------------------------
DROP TABLE IF EXISTS `art_admin`;
CREATE TABLE `art_admin`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '管理员登录名',
  `password` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '管理员密码',
  `create_time` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1不启用2启用3删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_banner
-- ----------------------------
DROP TABLE IF EXISTS `art_banner`;
CREATE TABLE `art_banner`  (
  `banner_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Banner的ID',
  `img` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Banner的地址',
  `sort` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Banner排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1不展示2展示3删除',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`banner_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for art_class
-- ----------------------------
DROP TABLE IF EXISTS `art_class`;
CREATE TABLE `art_class`  (
  `class_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '班级id',
  `class_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '班级名称',
  `teacher_id` int(11) NOT NULL COMMENT '教师id',
  `course_id` int(11) NOT NULL COMMENT '课程id',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1未开班 2已开班 3已删除',
  PRIMARY KEY (`class_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_clause
-- ----------------------------
DROP TABLE IF EXISTS `art_clause`;
CREATE TABLE `art_clause`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `clause` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户协议',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_course
-- ----------------------------
DROP TABLE IF EXISTS `art_course`;
CREATE TABLE `art_course`  (
  `course_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '课程名称',
  `course_brief` varchar(140) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '课程简介',
  `course_desc` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '课程详情最多15张图',
  `course_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '课程价格',
  `course_period` smallint(6) NOT NULL DEFAULT 0 COMMENT '课程周期 天',
  `course_time` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '课程时间',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1正常2暂停展示3已删除',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '上一次更新时间',
  `delete_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '删除时间',
  `subject_id` int(11) NOT NULL COMMENT '科目id',
  PRIMARY KEY (`course_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for art_course_user
-- ----------------------------
DROP TABLE IF EXISTS `art_course_user`;
CREATE TABLE `art_course_user`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL COMMENT '课程id',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '加入课程时间',
  `course_start_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '当前课程开始时间',
  `course_end_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '当前课程结束时间',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_feedback
-- ----------------------------
DROP TABLE IF EXISTS `art_feedback`;
CREATE TABLE `art_feedback`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `message` varchar(140) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户反馈内容',
  `img` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '反馈的图片地址',
  `reply` varchar(140) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '回复的内容',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `reply_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '回复的时间',
  `reply_by` int(11) NOT NULL DEFAULT 0 COMMENT '回复的管理员ID',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1待回复2已回复3已删除',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_formid
-- ----------------------------
DROP TABLE IF EXISTS `art_formid`;
CREATE TABLE `art_formid`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `formid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否已使用',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_menu
-- ----------------------------
DROP TABLE IF EXISTS `art_menu`;
CREATE TABLE `art_menu`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `parent_id` tinyint(4) NOT NULL COMMENT '父级id',
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '选项名称',
  `is_admin` int(2) NOT NULL DEFAULT 0 COMMENT '是否超级管理员0否 1是',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 21 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_msg
-- ----------------------------
DROP TABLE IF EXISTS `art_msg`;
CREATE TABLE `art_msg`  (
  `msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `msg_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0公告1对指定用户发送',
  `msg_content` varchar(140) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '消息内容',
  `msg_img` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `course_id` int(11) NOT NULL,
  `target_uid` int(11) NOT NULL COMMENT '目标对象的uid 当type为1时有效',
  `target_openid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '目标对象的openid 当type为1时有效',
  `send_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '发送时间',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1不发送 2发送 3删除',
  PRIMARY KEY (`msg_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_order
-- ----------------------------
DROP TABLE IF EXISTS `art_order`;
CREATE TABLE `art_order`  (
  `order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单号',
  `order_sn` char(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单编号',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `course_id` int(11) NOT NULL COMMENT '购买的课程ID',
  `fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '订单价格',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未支付1已支付2已取消',
  `pay_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '付款时间',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `cancel_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单取消时间',
  PRIMARY KEY (`order_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for art_power
-- ----------------------------
DROP TABLE IF EXISTS `art_power`;
CREATE TABLE `art_power`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `admin_id` int(11) NOT NULL COMMENT '管理员id',
  `menu_id` tinyint(4) NOT NULL COMMENT '菜单id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 37 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_sms_log
-- ----------------------------
DROP TABLE IF EXISTS `art_sms_log`;
CREATE TABLE `art_sms_log`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户openid',
  `mobile` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '发送时间',
  `code` char(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '验证码',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 82 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_subject
-- ----------------------------
DROP TABLE IF EXISTS `art_subject`;
CREATE TABLE `art_subject`  (
  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '课目名称',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  PRIMARY KEY (`subject_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_system_setting
-- ----------------------------
DROP TABLE IF EXISTS `art_system_setting`;
CREATE TABLE `art_system_setting`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `mini_name` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '小程序名称',
  `service_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '客服电话',
  `share_text` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '小程序分享的文字',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '更新的管理员ID',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_teacher
-- ----------------------------
DROP TABLE IF EXISTS `art_teacher`;
CREATE TABLE `art_teacher`  (
  `teacher_id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `teacher_phone` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '电话',
  `teacher_gender` tinyint(4) NOT NULL DEFAULT 0 COMMENT '性别0女1男',
  `teacher_birth` char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '生日2018-09-08',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `class_id` int(11) NOT NULL COMMENT '班级id',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1正常 2删除',
  PRIMARY KEY (`teacher_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for art_user
-- ----------------------------
DROP TABLE IF EXISTS `art_user`;
CREATE TABLE `art_user`  (
  `uid` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `openid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户openid',
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户真实姓名',
  `user_gender` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0女1男 用户认证时所选择的性别',
  `nickname` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户昵称',
  `avatar_url` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户头像地址',
  `city` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `province` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在省份',
  `country` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在国家',
  `gender` tinyint(1) NOT NULL DEFAULT 0 COMMENT '用户性别',
  `language` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户语言',
  `stu_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '学号',
  `grade` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1幼儿园2小学',
  `birth` char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '生日2018-09-07',
  `phone` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `auth_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '认证时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '当前用户状态0未认证1已认证',
  `auth_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '认证时使用的姓名',
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for art_user_clock
-- ----------------------------
DROP TABLE IF EXISTS `art_user_clock`;
CREATE TABLE `art_user_clock`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `course_id` int(11) NOT NULL COMMENT '课程ID',
  `clock_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '打卡时间',
  `clock_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0正常打卡1迟到2旷课3调课',
  `clock_by` int(11) NOT NULL COMMENT '打卡人员',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

SET FOREIGN_KEY_CHECKS = 1;
