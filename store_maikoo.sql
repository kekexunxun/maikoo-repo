/*
 Navicat Premium Data Transfer

 Source Server         : maikoo
 Source Server Type    : MySQL
 Source Server Version : 50637
 Source Host           : 120.79.145.110:3306
 Source Schema         : store_maikoo

 Target Server Type    : MySQL
 Target Server Version : 50637
 File Encoding         : 65001

 Date: 23/08/2018 18:07:53
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sm_admin
-- ----------------------------
DROP TABLE IF EXISTS `sm_admin`;
CREATE TABLE `sm_admin`  (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '管理员登录名',
  `password` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '管理员密码',
  `mch_id` int(11) NOT NULL DEFAULT 0 COMMENT '店铺id',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未启用1已启用2已删除',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '更新的用户ID',
  `goods_check` tinyint(4) NOT NULL DEFAULT 0 COMMENT '商品审核权限默认0无 1有权限',
  PRIMARY KEY (`admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_area
-- ----------------------------
DROP TABLE IF EXISTS `sm_area`;
CREATE TABLE `sm_area`  (
  `area_id` int(11) NOT NULL COMMENT '地区ID',
  `area` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '地区名称',
  `parent_id` int(11) NOT NULL COMMENT '上级ID',
  `level` tinyint(4) NOT NULL,
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '修改时间',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`area_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_article
-- ----------------------------
DROP TABLE IF EXISTS `sm_article`;
CREATE TABLE `sm_article`  (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `article_title` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `article_brief` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '简介',
  `article_desc` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '内容  最多 十五张图片',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `created_by` int(11) NOT NULL DEFAULT 0 COMMENT '创建人员id',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '更新人员id',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未发布1已发布2已删除',
  PRIMARY KEY (`article_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_banner
-- ----------------------------
DROP TABLE IF EXISTS `sm_banner`;
CREATE TABLE `sm_banner`  (
  `img_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Banner的ID',
  `img_src` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Banner的地址',
  `img_type` tinyint(6) NOT NULL DEFAULT 0 COMMENT '图片类型0banner1ad',
  `nav_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0不跳转1跳转指定商品2跳转到文章3跳转到分类',
  `nav_id` int(11) NOT NULL DEFAULT 0 COMMENT '跳转的id',
  `sort` smallint(6) NOT NULL DEFAULT 0 COMMENT '排序 仅当为banner时有效',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `pause_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '暂停时间',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '修改人员id',
  `location` int(11) NOT NULL DEFAULT 0 COMMENT '当img_type为1时有效 默认为0在首页，其它地方存catid',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未展示1已展示2已暂停3已删除',
  PRIMARY KEY (`img_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 54 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_catagory
-- ----------------------------
DROP TABLE IF EXISTS `sm_catagory`;
CREATE TABLE `sm_catagory`  (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `parent_id` int(11) NOT NULL DEFAULT 0 COMMENT '分类上级上级id 0为顶级',
  `cname` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分类名称(不超过4个字)',
  `sort` tinyint(4) NOT NULL DEFAULT 0 COMMENT '分类排序',
  `img` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分类图片,非顶级分类',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `delete_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '当前分类删除时间',
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) NOT NULL COMMENT '更新人员的ID',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未展示1展示2已删除',
  `is_index` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否在首页展示',
  PRIMARY KEY (`cat_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_clause
-- ----------------------------
DROP TABLE IF EXISTS `sm_clause`;
CREATE TABLE `sm_clause`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `clause` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户协议',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_column
-- ----------------------------
DROP TABLE IF EXISTS `sm_column`;
CREATE TABLE `sm_column`  (
  `column_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '专题ID',
  `column_color` char(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '专题主题颜色',
  `column_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '专栏名称',
  `column_img` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '专题的banner',
  `sort` tinyint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT 0 COMMENT '创建的管理员ID',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '更新的管理员ID',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0不展示1展示在首页2展示在会员3删除',
  `is_top` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否展示在首页的顶部',
  PRIMARY KEY (`column_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_column_goods
-- ----------------------------
DROP TABLE IF EXISTS `sm_column_goods`;
CREATE TABLE `sm_column_goods`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `column_id` int(11) NOT NULL COMMENT '专栏ID',
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `sort` tinyint(4) NOT NULL DEFAULT 0 COMMENT '商品排序',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0不展示1已展示2已删除',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `created_by` int(11) NOT NULL DEFAULT 0 COMMENT '创建的管理员ID',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '更新的管理员ID',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 120 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_coupon
-- ----------------------------
DROP TABLE IF EXISTS `sm_coupon`;
CREATE TABLE `sm_coupon`  (
  `coupon_id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_sn` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '卡券编号',
  `coupon_name` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '优惠券名称 不超过6个字',
  `money` double(5, 2) NOT NULL DEFAULT 0.00 COMMENT '优惠券面额 单位元',
  `send_start_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '优惠券开始发放时间',
  `send_end_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '优惠券结束发放时间',
  `total_num` int(11) NOT NULL DEFAULT 0 COMMENT '发放总量 0为不限',
  `send_num` int(11) NOT NULL DEFAULT 0 COMMENT '已发出数量',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '优惠券创建时间',
  `condition` decimal(5, 2) NOT NULL DEFAULT 0.00 COMMENT '使用条件 满足多少钱可以使用 这里存放的是钱 如果是0 则为无门槛优惠券',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未上线1已上线2已暂停3已结束',
  `pause_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `pause_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`coupon_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_feedback
-- ----------------------------
DROP TABLE IF EXISTS `sm_feedback`;
CREATE TABLE `sm_feedback`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `message` varchar(140) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户反馈内容',
  `img` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '反馈的图片地址',
  `reply` varchar(140) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '回复的内容',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `reply_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '回复的时间',
  `reply_by` int(11) NOT NULL DEFAULT 0 COMMENT '回复的管理员ID',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0待回复1已回复2已删除',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_goods
-- ----------------------------
DROP TABLE IF EXISTS `sm_goods`;
CREATE TABLE `sm_goods`  (
  `goods_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品ID',
  `goods_sn` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品编号',
  `goods_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `goods_img` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品图片地址',
  `cat_id` int(11) NOT NULL DEFAULT 0 COMMENT '商品所属分类ID',
  `market_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '商品市场价格',
  `shop_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '商品店铺价格',
  `member_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '商品会员价格',
  `stock` int(11) NOT NULL DEFAULT 0 COMMENT '商品库存',
  `sales_num` int(11) NOT NULL DEFAULT 0 COMMENT '已售数量',
  `keywords` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品关键词 以,划分 最多三个',
  `is_new` tinyint(4) NOT NULL DEFAULT 1 COMMENT '是否为新品',
  `is_hot` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否为热卖',
  `points` int(11) NOT NULL DEFAULT 0 COMMENT '购买该商品赠送的积分',
  `sort` tinyint(4) NOT NULL DEFAULT 0 COMMENT '商品排序值',
  `unit` char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品规格',
  `area` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '售卖的地区',
  `delete_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '删除时间',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '负责修改的人员ID',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未审核1已审核2已上架3已下架4已删除',
  `goods_desc` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品详细说明',
  PRIMARY KEY (`goods_id`) USING BTREE,
  UNIQUE INDEX `goods_id`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_menu
-- ----------------------------
DROP TABLE IF EXISTS `sm_menu`;
CREATE TABLE `sm_menu`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `parent_id` tinyint(4) NOT NULL COMMENT '父级id',
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '选项名称',
  `is_admin` int(2) NOT NULL DEFAULT 0 COMMENT '是否超级管理员0否 1是',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 33 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_merchant
-- ----------------------------
DROP TABLE IF EXISTS `sm_merchant`;
CREATE TABLE `sm_merchant`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `mch_id` int(11) NOT NULL COMMENT '商家ID',
  `mch_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商家名称',
  `mch_cert` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商家申请证书资质 图片2张以内',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未营业1正常营业2暂停营业',
  `pause_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '暂停时间',
  `pause_reason` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '暂停的原因',
  `location` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商户所在位置坐标wgs84',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_merchant_apply
-- ----------------------------
DROP TABLE IF EXISTS `sm_merchant_apply`;
CREATE TABLE `sm_merchant_apply`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `mch_id` int(11) NOT NULL COMMENT '商家ID',
  `mch_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商家名称',
  `mch_cert` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商家申请证书资质 图片2张以内',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0提交审核1不通过2通过',
  `pass_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '通过审核的时间',
  `pass_by` int(11) NOT NULL COMMENT '审核员的id',
  `apply_reason` varchar(140) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '申请原因',
  `pass_reason` varchar(140) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '通过原因',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '负责处理该调数据的用户ID',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '处理更新时间',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_merchant_log
-- ----------------------------
DROP TABLE IF EXISTS `sm_merchant_log`;
CREATE TABLE `sm_merchant_log`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `mch_id` int(11) NOT NULL COMMENT '商家ID',
  `pause_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '暂停时间',
  `reason` varchar(140) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '暂停原因',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_merchant_stock
-- ----------------------------
DROP TABLE IF EXISTS `sm_merchant_stock`;
CREATE TABLE `sm_merchant_stock`  (
  `idx` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `mch_id` int(11) NOT NULL DEFAULT 0 COMMENT '店铺ID',
  `goods_id` int(11) NOT NULL DEFAULT 0 COMMENT '商品ID',
  `stock` int(11) NOT NULL DEFAULT 0 COMMENT '商品库存',
  `sales_num` int(11) NOT NULL DEFAULT 0 COMMENT '售出数量',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '更新的管理员ID',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_order
-- ----------------------------
DROP TABLE IF EXISTS `sm_order`;
CREATE TABLE `sm_order`  (
  `order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单号',
  `order_sn` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单编号',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `mch_id` int(11) NOT NULL DEFAULT 0 COMMENT '店铺ID',
  `total_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '该订单总价',
  `coupon_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '优惠券抵扣金额',
  `logi_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '邮费',
  `logi_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '快递单号',
  `logi_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '快递公司名称',
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户姓名',
  `phone` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户电话',
  `address` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户地址',
  `message` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户留言',
  `pay_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单支付时间',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单生成时间',
  `cancel_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单取消的时间',
  `finish_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单完成时间',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '订单状态1未付款2待发货3已发货4待评价5已完成6已取消',
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '更新时间（后台操作时的更新时间）',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '更新的人员',
  PRIMARY KEY (`order_id`) USING BTREE,
  UNIQUE INDEX `order_sn`(`order_sn`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_order_detail
-- ----------------------------
DROP TABLE IF EXISTS `sm_order_detail`;
CREATE TABLE `sm_order_detail`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL COMMENT '订单号',
  `order_sn` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单编号',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `quantity` smallint(6) NOT NULL DEFAULT 1 COMMENT '商品数量',
  `fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '商品价格',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 70 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_power
-- ----------------------------
DROP TABLE IF EXISTS `sm_power`;
CREATE TABLE `sm_power`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `admin_id` int(4) NOT NULL COMMENT '管理员id',
  `menu_id` varchar(90) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '菜单id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_question
-- ----------------------------
DROP TABLE IF EXISTS `sm_question`;
CREATE TABLE `sm_question`  (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '问题',
  `answer` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '回答',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT 0 COMMENT '创建该条记录的管理员ID',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '负责更新的管理员id',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未上线1已上线2已删除',
  PRIMARY KEY (`question_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_search_kw
-- ----------------------------
DROP TABLE IF EXISTS `sm_search_kw`;
CREATE TABLE `sm_search_kw`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '关键词',
  `nav_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0不跳转1跳转指定商品2跳转到新的界面4跳转到分类',
  `nav_id` int(11) NOT NULL DEFAULT 0 COMMENT '需要跳转的id',
  `sort` tinyint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '更新的管理员ID',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0不展示1展示2删除',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 21 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_search_log
-- ----------------------------
DROP TABLE IF EXISTS `sm_search_log`;
CREATE TABLE `sm_search_log`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `keyword` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `search_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_system_setting
-- ----------------------------
DROP TABLE IF EXISTS `sm_system_setting`;
CREATE TABLE `sm_system_setting`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `mini_name` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '小程序名称',
  `mini_color` char(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '顶部TAB颜色 十六进制6位',
  `logi_fee` int(11) NOT NULL COMMENT '邮费',
  `logi_free_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '满金额包邮费用',
  `service_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '客服电话',
  `mch_distance` int(11) NOT NULL DEFAULT 3000 COMMENT '门店配送距离(米)',
  `is_layer_show` tinyint(4) NOT NULL DEFAULT 0 COMMENT '小程序首页弹出是否展示0不展示1展示',
  `layer_img` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '小程序首页弹出层图片',
  `layer_nav_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0不跳转1跳转商品2跳转文章3跳转优惠券',
  `layer_nav_id` int(11) NOT NULL DEFAULT 0 COMMENT '跳转对应的id',
  `logi_city_ids` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '可配送的城市ID 英文逗号拼接',
  `share_text` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '小程序分享的文字',
  `member_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '成为会员的价格',
  `member_period` int(11) NOT NULL DEFAULT 360 COMMENT '会员周期',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '更新的管理员ID',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_user
-- ----------------------------
DROP TABLE IF EXISTS `sm_user`;
CREATE TABLE `sm_user`  (
  `uid` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `openid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户的openid',
  `is_auth` tinyint(1) NOT NULL DEFAULT 0 COMMENT '用户是否实名认证',
  `is_member` tinyint(4) NOT NULL DEFAULT 0 COMMENT '用户是否是会员',
  `points` int(11) NOT NULL DEFAULT 0 COMMENT '用户积分',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 885 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_user_address
-- ----------------------------
DROP TABLE IF EXISTS `sm_user_address`;
CREATE TABLE `sm_user_address`  (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `consignee` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '收货人',
  `country` smallint(6) NOT NULL DEFAULT 0 COMMENT '城市对应的ID',
  `province` smallint(6) NOT NULL DEFAULT 0 COMMENT '省份对应的ID',
  `city` smallint(6) NOT NULL DEFAULT 0 COMMENT '城市对应的ID',
  `district` smallint(6) NOT NULL DEFAULT 0 COMMENT '地区对应的ID',
  `address` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `mobile` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机号',
  `is_default` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否为默认',
  `is_active` tinyint(4) NOT NULL DEFAULT 1 COMMENT '是否有效',
  PRIMARY KEY (`address_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_user_click_log
-- ----------------------------
DROP TABLE IF EXISTS `sm_user_click_log`;
CREATE TABLE `sm_user_click_log`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '点击的类别0商品1分类',
  `type_id` int(11) NOT NULL COMMENT '点击对应类别的id',
  `click_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '点击时间',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_user_coupon
-- ----------------------------
DROP TABLE IF EXISTS `sm_user_coupon`;
CREATE TABLE `sm_user_coupon`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户uid',
  `coupon_id` tinyint(4) NOT NULL COMMENT '卡券id对应coupon表',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '卡券领取时间',
  `expire_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '卡券过期时间 默认为0 永不过期',
  `use_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '使用时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0可使用1已使用2已过期',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 185 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_user_fav
-- ----------------------------
DROP TABLE IF EXISTS `sm_user_fav`;
CREATE TABLE `sm_user_fav`  (
  `fav_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`fav_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_user_fav_log
-- ----------------------------
DROP TABLE IF EXISTS `sm_user_fav_log`;
CREATE TABLE `sm_user_fav_log`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `fav_action` tinyint(4) NOT NULL DEFAULT 0 COMMENT '收藏操作0取消收藏1收藏',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_user_login_log
-- ----------------------------
DROP TABLE IF EXISTS `sm_user_login_log`;
CREATE TABLE `sm_user_login_log`  (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `login_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户登陆时间',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_user_point
-- ----------------------------
DROP TABLE IF EXISTS `sm_user_point`;
CREATE TABLE `sm_user_point`  (
  `point_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `point` int(11) NOT NULL DEFAULT 0 COMMENT '所获积分',
  `src` tinyint(4) NOT NULL DEFAULT 0 COMMENT '积分来源',
  `desc` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '描述',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`point_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sm_user_profile
-- ----------------------------
DROP TABLE IF EXISTS `sm_user_profile`;
CREATE TABLE `sm_user_profile`  (
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `nickname` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户昵称',
  `avatar_url` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户头像地址',
  `city` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `province` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在省份',
  `country` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在国家',
  `gender` tinyint(1) NOT NULL DEFAULT 0 COMMENT '用户性别',
  `language` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户语言',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

SET FOREIGN_KEY_CHECKS = 1;
