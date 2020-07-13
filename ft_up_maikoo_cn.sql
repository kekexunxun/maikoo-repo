/*
 Navicat Premium Data Transfer

 Source Server         : maikoo
 Source Server Type    : MySQL
 Source Server Version : 50637
 Source Host           : 120.79.145.110:3306
 Source Schema         : ft_up_maikoo_cn

 Target Server Type    : MySQL
 Target Server Version : 50637
 File Encoding         : 65001

 Date: 17/10/2018 13:44:35
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ft_activity
-- ----------------------------
DROP TABLE IF EXISTS `ft_activity`;
CREATE TABLE `ft_activity`  (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '活动名称',
  `brief` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '活动简介',
  `pic` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '活动封面',
  `detail` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '活动详情',
  `start_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '活动开始时间',
  `end_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '活动结束时间',
  `first_price_num` tinyint(4) NOT NULL DEFAULT 0 COMMENT '一等奖人数',
  `first_price` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '一等奖对应的产品',
  `second_price_num` tinyint(4) NOT NULL DEFAULT 0 COMMENT '二等奖获奖人数',
  `second_price` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '二等奖对应的产品',
  `third_price_num` tinyint(4) NOT NULL DEFAULT 0 COMMENT '三等奖人数',
  `third_price` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '三等奖对应的产品',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `is_active` tinyint(4) NOT NULL DEFAULT 0 COMMENT '当前状态是否生效',
  `state` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0报名中1未开奖2已开奖3已暂停4已结束5正在抽奖',
  `is_delete` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `activity_poster` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '活动海报地址',
  `qrcode` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '活动二维码',
  PRIMARY KEY (`activity_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 72 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_activity_pride
-- ----------------------------
DROP TABLE IF EXISTS `ft_activity_pride`;
CREATE TABLE `ft_activity_pride`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` smallint(6) NOT NULL COMMENT '活动ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `level` tinyint(4) NOT NULL COMMENT '中奖等级',
  `level_price` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '当前等级的奖励',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 124 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_activity_user
-- ----------------------------
DROP TABLE IF EXISTS `ft_activity_user`;
CREATE TABLE `ft_activity_user`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` smallint(6) NOT NULL COMMENT '活动ID',
  `user_openid` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '参与活动的用户Openid',
  `join_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户参与的时间',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `user_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `pic` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户头像',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 76 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_admin
-- ----------------------------
DROP TABLE IF EXISTS `ft_admin`;
CREATE TABLE `ft_admin`  (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` tinyint(4) NOT NULL COMMENT '用户ID 对应用户表',
  `user_openid` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `account_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '详见Reademe文档',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `is_active` tinyint(4) NOT NULL DEFAULT 1 COMMENT '当前用户管理员状态是否有效',
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '管理员用户名',
  `password` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '管理员密码',
  `is_delete` tinyint(4) NOT NULL DEFAULT 0 COMMENT '当前用户是否被删除',
  `delete_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '当前用户被删除的时间',
  `last_stop_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '上一次恢复的时间',
  `last_continue_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '上一次继续的时间',
  `tel_num` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '管理员登录名（手机号）',
  PRIMARY KEY (`admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_banner
-- ----------------------------
DROP TABLE IF EXISTS `ft_banner`;
CREATE TABLE `ft_banner`  (
  `banner_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Banner的ID',
  `banner_src` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Banner的地址',
  `goods_id` smallint(6) NOT NULL COMMENT '该Banner需要跳转的产品ID 0为不跳转',
  `is_active` tinyint(1) NOT NULL COMMENT '当前Banner是否展示',
  `is_delete` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Banner是否删除',
  `orderby` smallint(6) NOT NULL DEFAULT 0 COMMENT 'Banner排序',
  `type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '图片展示位置0是banner 1为票卷经典2尝鲜3刺激4休闲9无分类',
  PRIMARY KEY (`banner_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 67 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_cart
-- ----------------------------
DROP TABLE IF EXISTS `ft_cart`;
CREATE TABLE `ft_cart`  (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_openid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户openid',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `quantity` int(11) NOT NULL COMMENT '商品数量',
  `goods_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '商品类型',
  `detail_id` int(11) NULL DEFAULT NULL COMMENT '商品详情ID',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `update_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `delete_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`cart_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 124 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_catagory
-- ----------------------------
DROP TABLE IF EXISTS `ft_catagory`;
CREATE TABLE `ft_catagory`  (
  `catagory_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品分类名称',
  `catagory_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品分类名称',
  `father_catagory_id` smallint(6) NOT NULL DEFAULT 0 COMMENT '父级分类ID',
  `orderby` smallint(6) NOT NULL DEFAULT 0 COMMENT '默认商品分类排序值',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分类创建时间',
  `is_delete` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否处于激活(展示)状态',
  `delete_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`catagory_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 33 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_clause
-- ----------------------------
DROP TABLE IF EXISTS `ft_clause`;
CREATE TABLE `ft_clause`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `clause` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户协议',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_code_log
-- ----------------------------
DROP TABLE IF EXISTS `ft_code_log`;
CREATE TABLE `ft_code_log`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'code',
  `user_openid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_distribution
-- ----------------------------
DROP TABLE IF EXISTS `ft_distribution`;
CREATE TABLE `ft_distribution`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` smallint(6) NOT NULL COMMENT '用户ID',
  `user_openid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户的openid',
  `parent_id` smallint(6) NOT NULL DEFAULT 0 COMMENT '用户的上一级ID',
  `grand_id` smallint(6) NOT NULL DEFAULT 0 COMMENT '用户的上上级ID',
  `gen_parent_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '产生上级的时间',
  `gen_grand_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '产生上上级的时间',
  `goods_id` smallint(6) NOT NULL COMMENT '商品ID',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 264 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_distribution_fee
-- ----------------------------
DROP TABLE IF EXISTS `ft_distribution_fee`;
CREATE TABLE `ft_distribution_fee`  (
  `dis_fee_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '分销明细',
  `order_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '产生该笔消费的用户ID',
  `user_openid` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户openid',
  `totalFee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '该订单该商品产生的消费总额',
  `parent_id` int(11) NOT NULL COMMENT '用户的上一级ID',
  `parent_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '用户的上一级得到的返佣金额',
  `parent_percent` tinyint(4) NOT NULL DEFAULT 0 COMMENT '父级获得的返佣比例',
  `grand_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户上上级的ID',
  `grand_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '用户上上级获得的返佣费用',
  `grand_percent` tinyint(4) NOT NULL DEFAULT 0 COMMENT '上上级的返佣比例',
  `dis_percent` tinyint(4) NOT NULL DEFAULT 0 COMMENT '当前返佣比例分配设置',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `detail_id` int(11) NOT NULL DEFAULT 0 COMMENT '商品详情ID',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `is_success` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否有效',
  PRIMARY KEY (`dis_fee_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 69 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_express
-- ----------------------------
DROP TABLE IF EXISTS `ft_express`;
CREATE TABLE `ft_express`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `express_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '快递公司名称',
  `express_fee` int(11) NOT NULL DEFAULT 0 COMMENT '快递价格（单位分）',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 60 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_goods
-- ----------------------------
DROP TABLE IF EXISTS `ft_goods`;
CREATE TABLE `ft_goods`  (
  `goods_id` int(11) NOT NULL AUTO_INCREMENT,
  `catagory_id` int(11) NOT NULL DEFAULT 0 COMMENT '商品分类ID',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `pic` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品图片地址(后台限制1张)',
  `spec` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品详情(后台使用ueditor进行编辑)',
  `intro` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '当前商品购买须知',
  `orderby` tinyint(4) NOT NULL DEFAULT 0 COMMENT '商品排序',
  `is_active` tinyint(4) NOT NULL DEFAULT 0 COMMENT '商品是否上架',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品创建时间',
  `last_modify_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品上次修改时间',
  `delete_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品删除时间',
  `is_on_promotion` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否参与促销活动(一个商品只能参与一个促销活动)',
  `promotion_id` smallint(6) NOT NULL DEFAULT 0 COMMENT '促销活动ID',
  `is_distri` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否参加分销',
  `dis_percent` tinyint(4) NOT NULL DEFAULT 20 COMMENT '分销占总费用百分比',
  `parent_dis_percent` tinyint(4) NOT NULL DEFAULT 80 COMMENT '上一级的分销比例',
  `grand_dis_percent` tinyint(4) NOT NULL DEFAULT 20 COMMENT '上上级的分销比例',
  `last_down_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '最近一次下架的时间',
  `last_up_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '最近一次上架的时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '商品是否删除',
  PRIMARY KEY (`goods_id`) USING BTREE,
  UNIQUE INDEX `goods_id`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 73 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_goods_detail
-- ----------------------------
DROP TABLE IF EXISTS `ft_goods_detail`;
CREATE TABLE `ft_goods_detail`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `detail_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `market_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '原价',
  `shop_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '售价',
  `sellnum` int(11) NOT NULL DEFAULT 0 COMMENT '已售数量',
  `stock` int(11) NOT NULL DEFAULT 0 COMMENT '当前库存',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `detail_intro` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '简介',
  `keywords` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '关键词',
  `is_delete` tinyint(4) NOT NULL COMMENT '是否删除（默认0未删除）',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 208 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_goods_kw
-- ----------------------------
DROP TABLE IF EXISTS `ft_goods_kw`;
CREATE TABLE `ft_goods_kw`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `keywords` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '关键词名称',
  `color` char(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '十六进制颜色代码',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_invite_code
-- ----------------------------
DROP TABLE IF EXISTS `ft_invite_code`;
CREATE TABLE `ft_invite_code`  (
  `code_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '邀请码ID',
  `invite_code` char(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `code_total_num` smallint(6) NOT NULL DEFAULT 0 COMMENT '该邀请码可邀请的总人数',
  `code_active_num` smallint(6) NOT NULL DEFAULT 0 COMMENT '该邀请码已被激活人数',
  `is_active` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否激活使用(状态为2时是用尽状态)',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '该邀请码的创建时间',
  PRIMARY KEY (`code_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_logi
-- ----------------------------
DROP TABLE IF EXISTS `ft_logi`;
CREATE TABLE `ft_logi`  (
  `logi_id` int(11) NOT NULL AUTO_INCREMENT,
  `logi_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '快递公司名称',
  `logi_fee` int(11) NOT NULL COMMENT '快递费用',
  `is_delete` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否被删除',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `delete_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '删除时间',
  `is_active` tinyint(4) NOT NULL DEFAULT 1 COMMENT '是否启用',
  PRIMARY KEY (`logi_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_menu
-- ----------------------------
DROP TABLE IF EXISTS `ft_menu`;
CREATE TABLE `ft_menu`  (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `parent_id` tinyint(4) NOT NULL DEFAULT 0 COMMENT '菜单级别（0一级菜单）',
  `menu_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '菜单名称',
  `is_admin` int(2) NULL DEFAULT 0 COMMENT '最高管理员(1) ',
  PRIMARY KEY (`menu_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 25 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_order
-- ----------------------------
DROP TABLE IF EXISTS `ft_order`;
CREATE TABLE `ft_order`  (
  `order_id` char(12) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '订单状态 1待付款 2待发货 3已发货 4已完成 5已取消 6 售后中 7 售后完成 8 申请退款 9 退款完成',
  `user_openid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户的openid',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户ID',
  `total_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '该订单消费价格(实际价格)',
  `pay_time` int(10) NOT NULL DEFAULT 0 COMMENT '订单支付时间',
  `tel_num` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户电话',
  `address` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户地址',
  `express_co` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '快递公司名称',
  `express_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '快递费用',
  `express_num` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '快递号',
  `message` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户留言',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单生成时间',
  `is_delete` tinyint(4) NOT NULL DEFAULT 0 COMMENT '订单是否删除',
  `delete_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单删除时间',
  `cancel_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单取消的时间',
  `confirm_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '收货确认时间',
  `onas_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '发起售后时间',
  `express_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '发货时间',
  `finish_as_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '售后结束时间',
  `promotion_id` int(11) NOT NULL DEFAULT 0 COMMENT '参加的促销活动ID',
  `user_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `verify_url` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户核销QRCode路径',
  `verify_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户核销码',
  `is_verify` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否已核销 0 未核销 1核销',
  `verify_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '核销时间',
  `apply_refound_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '申请退款时间',
  `accept_refound_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '退款成功时间',
  `is_refound` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否退款成功',
  PRIMARY KEY (`order_id`) USING BTREE,
  UNIQUE INDEX `order_id`(`order_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_order_detail
-- ----------------------------
DROP TABLE IF EXISTS `ft_order_detail`;
CREATE TABLE `ft_order_detail`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` char(12) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `goods_id` int(11) NOT NULL,
  `detail_id` int(11) NOT NULL COMMENT '对应goods_detail表中的id',
  `quantity` smallint(6) NOT NULL DEFAULT 1 COMMENT '商品数量',
  `market_price` decimal(10, 2) NOT NULL COMMENT '商品原价(对应为goods表shop_price)',
  `shop_price` decimal(10, 2) NOT NULL COMMENT '该订单消费价格(实际价格)',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `promotion_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
  `is_distri` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否参加分销',
  `dis_percent` tinyint(4) NOT NULL DEFAULT 0 COMMENT '分销金额占商品金额的比例',
  `parent_dis_percent` tinyint(4) NOT NULL DEFAULT 0 COMMENT '上级分销金额占比',
  `grand_dis_percent` tinyint(4) NOT NULL DEFAULT 0 COMMENT '上级分销金额占比',
  PRIMARY KEY (`idx`) USING BTREE,
  UNIQUE INDEX `idx`(`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 185 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_power
-- ----------------------------
DROP TABLE IF EXISTS `ft_power`;
CREATE TABLE `ft_power`  (
  `power_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `menu_id` int(11) NOT NULL COMMENT '菜单id',
  PRIMARY KEY (`power_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 106 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_promotion
-- ----------------------------
DROP TABLE IF EXISTS `ft_promotion`;
CREATE TABLE `ft_promotion`  (
  `promotion_id` int(11) NOT NULL AUTO_INCREMENT,
  `count` int(11) NOT NULL DEFAULT 100 COMMENT '促销活动的折扣',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '促销活动名称',
  `start_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '活动开始时间',
  `end_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '活动结束时间',
  `is_active` tinyint(4) NOT NULL DEFAULT 0 COMMENT '促销活动创建时默认不上线',
  `last_paused_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '最近一次暂停的时间',
  `last_continue_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '最近一次恢复的时间',
  PRIMARY KEY (`promotion_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_sms_log
-- ----------------------------
DROP TABLE IF EXISTS `ft_sms_log`;
CREATE TABLE `ft_sms_log`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `user_openid` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `tel_num` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `code` char(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '验证码',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 90 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_system_setting
-- ----------------------------
DROP TABLE IF EXISTS `ft_system_setting`;
CREATE TABLE `ft_system_setting`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `mini_name` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '小程序名称',
  `mini_color` char(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '顶部TAB颜色 十六进制6位',
  `logi_fee` decimal(10, 2) NOT NULL COMMENT '邮费',
  `logi_free_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '满金额包邮费用',
  `service_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '客服电话',
  `mch_distance` int(11) NOT NULL DEFAULT 3000 COMMENT '门店配送距离(米)',
  `is_layer_show` tinyint(4) NOT NULL DEFAULT 0 COMMENT '小程序首页弹出是否展示0不展示1展示',
  `layer_img` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '小程序首页弹出层图片',
  `layer_nav_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0不跳转1跳转商品2跳转文章3跳转优惠券',
  `layer_nav_id` int(11) NOT NULL DEFAULT 0 COMMENT '跳转对应的id',
  `logi_city_ids` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '可配送的城市ID 英文逗号拼接',
  `tencent_lbs_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '腾讯LBS的KEY',
  `share_text` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '小程序分享的文字',
  `member_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '成为会员的价格',
  `member_period` int(11) NOT NULL DEFAULT 360 COMMENT '会员周期',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '更新的管理员ID',
  `user_rebate_min` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '用户最低可提现金额',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_user_rebate
-- ----------------------------
DROP TABLE IF EXISTS `ft_user_rebate`;
CREATE TABLE `ft_user_rebate`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT COMMENT ' ',
  `rebate` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '发起提现的金额',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户ID',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '发起提现时间',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1待提现2已提现',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '操作该条记录的管理员ID',
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作该条记录的时间',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_usercount
-- ----------------------------
DROP TABLE IF EXISTS `ft_usercount`;
CREATE TABLE `ft_usercount`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `user_openid` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `create_time` char(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 920 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for ft_userinfo
-- ----------------------------
DROP TABLE IF EXISTS `ft_userinfo`;
CREATE TABLE `ft_userinfo`  (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_openid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户的openid',
  `nickname` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户昵称',
  `avatar_url` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户头像地址',
  `city` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `province` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在省份',
  `country` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在国家',
  `gender` tinyint(1) NOT NULL DEFAULT 0 COMMENT '用户性别',
  `ident_id` char(18) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户身份证号',
  `tel_num` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `is_auth` tinyint(1) NOT NULL DEFAULT 0 COMMENT '用户是否实名认证',
  `invite_code_id` int(11) NOT NULL DEFAULT 0 COMMENT '当前用户注册时使用的邀请码ID',
  `language` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户真实姓名',
  `create_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `rebate` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '用户返佣总额',
  `invite_check_time` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 281 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_merch_comment
-- ----------------------------
DROP TABLE IF EXISTS `sm_merch_comment`;
CREATE TABLE `sm_merch_comment`  (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT ' ',
  `mch_id` int(11) NOT NULL DEFAULT 0 COMMENT '商户ID',
  `describ_rate` tinyint(4) NOT NULL DEFAULT 5 COMMENT '商品描述评分',
  `logi_rate` tinyint(4) NOT NULL DEFAULT 5 COMMENT '物流评分',
  `service_rate` tinyint(4) NOT NULL DEFAULT 5 COMMENT '服务评分',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT 0 COMMENT '创建的用户ID',
  PRIMARY KEY (`comment_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

SET FOREIGN_KEY_CHECKS = 1;
