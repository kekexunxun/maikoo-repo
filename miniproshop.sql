/*
Navicat MySQL Data Transfer

Source Server         : maikoo
Source Server Version : 50637
Source Host           : 120.79.145.110:3306
Source Database       : miniproshop

Target Server Type    : MYSQL
Target Server Version : 50637
File Encoding         : 65001

Date: 2018-08-02 11:16:34
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sp_admin`
-- ----------------------------
DROP TABLE IF EXISTS `sp_admin`;
CREATE TABLE `sp_admin` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `name` varchar(20) NOT NULL COMMENT '管理员登录名',
  `password` varchar(100) NOT NULL COMMENT '管理员密码',
  `create_time` char(10) CHARACTER SET utf8mb4 NOT NULL COMMENT '创建时间',
  `is_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否启用',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_article`
-- ----------------------------
DROP TABLE IF EXISTS `sp_article`;
CREATE TABLE `sp_article` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `title` varchar(30) NOT NULL COMMENT '评测文章的title',
  `brief` varchar(70) NOT NULL COMMENT '文章简介',
  `content` text NOT NULL COMMENT '文章的内容',
  `pic` varchar(100) NOT NULL COMMENT '当前文章的封面',
  `author` varchar(15) NOT NULL DEFAULT '' COMMENT '文章作者',
  `cat_id` int(11) NOT NULL DEFAULT '0' COMMENT '文章分类id 当type 为 1 时有效',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '文章发布的类型 0 评测 1 资讯',
  `views` int(11) NOT NULL DEFAULT '0' COMMENT '当前文章查看人数',
  `is_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '文章是否展示',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '文章是否被删除',
  `create_time` char(10) NOT NULL COMMENT '文章发布时间',
  PRIMARY KEY (`article_id`),
  UNIQUE KEY `article_id` (`article_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_article_cat`
-- ----------------------------
DROP TABLE IF EXISTS `sp_article_cat`;
CREATE TABLE `sp_article_cat` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文章分类id',
  `father_id` int(11) NOT NULL COMMENT '上级ID',
  `name` varchar(10) NOT NULL COMMENT '分类名称',
  `orderby` int(11) NOT NULL DEFAULT '0' COMMENT '分类排序0-100',
  `is_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '分类是否展示',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '分类是否删除',
  `create_time` char(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_article_click_count`
-- ----------------------------
DROP TABLE IF EXISTS `sp_article_click_count`;
CREATE TABLE `sp_article_click_count` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `user_openid` char(60) NOT NULL COMMENT '用户的openid',
  `article_id` int(11) NOT NULL COMMENT '文章ID',
  `click_time` char(10) NOT NULL COMMENT '点击打开的时间',
  `create_time` char(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`idx`),
  UNIQUE KEY `idx` (`idx`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_bannerlist`
-- ----------------------------
DROP TABLE IF EXISTS `sp_bannerlist`;
CREATE TABLE `sp_bannerlist` (
  `idx` smallint(6) NOT NULL AUTO_INCREMENT,
  `pic` varchar(100) NOT NULL COMMENT 'banner图片地址',
  `orderby` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'banner排序0-100越大越优先',
  `navigate` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'banner跳转地址 默认0不跳转 1小程序 2专栏 3当日排行',
  `navigate_name` varchar(25) NOT NULL COMMENT '跳转对应的名称',
  `navigate_id` smallint(6) NOT NULL COMMENT 'banner跳转对应的id',
  `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '当前Banner是否展示 默认不展示',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Banner是否删除',
  `delete_time` char(10) NOT NULL DEFAULT '' COMMENT '当前banner删除时间',
  `create_time` char(10) NOT NULL COMMENT '当前栏目创建时间',
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_cat_click_count`
-- ----------------------------
DROP TABLE IF EXISTS `sp_cat_click_count`;
CREATE TABLE `sp_cat_click_count` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `user_openid` char(60) NOT NULL COMMENT '用户的openid',
  `cat_id` int(11) NOT NULL COMMENT '分类的ID',
  `click_time` char(10) NOT NULL COMMENT '点击打开的时间',
  `create_time` char(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`idx`),
  UNIQUE KEY `idx` (`idx`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=748 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_catagory`
-- ----------------------------
DROP TABLE IF EXISTS `sp_catagory`;
CREATE TABLE `sp_catagory` (
  `catagory_id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '目录id',
  `father_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '上级id',
  `name` varchar(10) NOT NULL COMMENT '分类名称(不超过4个字)',
  `orderby` tinyint(4) NOT NULL DEFAULT '0' COMMENT '分类排序',
  `create_time` char(10) NOT NULL COMMENT '创建时间',
  `pic` varchar(100) NOT NULL COMMENT '当前目录的pic',
  `is_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '当前分类是否展示 默认不展示',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '当前分类是否删除',
  `delete_time` char(10) NOT NULL DEFAULT '' COMMENT '当前分类删除时间',
  PRIMARY KEY (`catagory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_clause`
-- ----------------------------
DROP TABLE IF EXISTS `sp_clause`;
CREATE TABLE `sp_clause` (
  `idx` tinyint(4) NOT NULL AUTO_INCREMENT,
  `clause` text NOT NULL COMMENT '用户协议',
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_column`
-- ----------------------------
DROP TABLE IF EXISTS `sp_column`;
CREATE TABLE `sp_column` (
  `idx` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '专栏ID',
  `name` varchar(20) NOT NULL COMMENT '专栏名称',
  `brief` varchar(100) NOT NULL COMMENT '专栏简介',
  `pic` varchar(60) NOT NULL COMMENT '专题的封面',
  `minis` varchar(100) NOT NULL COMMENT '专栏包含的小程序ID以及排序(不超过20个)',
  `views` int(11) NOT NULL DEFAULT '0' COMMENT '查看人数',
  `create_time` char(10) NOT NULL COMMENT '创建时间',
  `is_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '当前专栏是否展示 默认不展示',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '当前专栏是否删除',
  `delete_time` char(10) NOT NULL DEFAULT '' COMMENT '当前栏目删除时间',
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_column_click_count`
-- ----------------------------
DROP TABLE IF EXISTS `sp_column_click_count`;
CREATE TABLE `sp_column_click_count` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `user_openid` char(60) NOT NULL COMMENT '用户的openid',
  `column_id` int(11) NOT NULL COMMENT '小程序专栏ID',
  `click_time` char(10) NOT NULL COMMENT '点击打开的时间',
  `create_time` char(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`idx`),
  UNIQUE KEY `idx` (`idx`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=862 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_menu`
-- ----------------------------
DROP TABLE IF EXISTS `sp_menu`;
CREATE TABLE `sp_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `parent_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '菜单级别（0一级菜单）',
  `name` varchar(32) NOT NULL COMMENT '菜单名称',
  `is_admin` int(2) DEFAULT '0' COMMENT '最高管理员(1) ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_mini_click_count`
-- ----------------------------
DROP TABLE IF EXISTS `sp_mini_click_count`;
CREATE TABLE `sp_mini_click_count` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `user_openid` char(60) NOT NULL COMMENT '用户的openid',
  `mini_id` int(11) NOT NULL COMMENT '小程序ID',
  `mini_appid` varchar(50) NOT NULL COMMENT '小程序appid',
  `click_time` char(10) NOT NULL COMMENT '点击打开的时间',
  `create_time` char(10) NOT NULL COMMENT '创建时间',
  `is_enter` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否跳转到了小程序里面',
  `is_enable` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `clicks` int(11) NOT NULL DEFAULT '1' COMMENT '点击量',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`idx`),
  UNIQUE KEY `idx` (`idx`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=1628 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_minipro`
-- ----------------------------
DROP TABLE IF EXISTS `sp_minipro`;
CREATE TABLE `sp_minipro` (
  `mini_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `appid` char(40) NOT NULL COMMENT '小程序的appid',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '小程序路径',
  `name` varchar(25) NOT NULL COMMENT '小程序名称',
  `avatarUrl` varchar(70) NOT NULL COMMENT '小程序头像',
  `brief` varchar(100) NOT NULL COMMENT '小程序简介',
  `pics` varchar(500) NOT NULL COMMENT '小程序截图简介 限制5张',
  `intro` varchar(200) NOT NULL COMMENT '小程序使用简介',
  `views` int(11) NOT NULL DEFAULT '0' COMMENT '小程序查看人数',
  `rate` decimal(2,1) NOT NULL DEFAULT '4.0' COMMENT '当前应用的评分(满分5)',
  `catagory_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '对应的分类ID',
  `create_time` char(10) NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否被删除',
  `is_active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否展示',
  `delete_time` char(10) NOT NULL COMMENT '当前小程序删除时间',
  `is_openable` tinyint(4) NOT NULL DEFAULT '1' COMMENT '当前小程序是否能够跳转',
  `unable_time` char(10) NOT NULL DEFAULT '' COMMENT '当前小程序最近一次不能被打开的时间',
  `keywords` char(30) NOT NULL COMMENT '小程序的关键词以英文的逗号隔开',
  `extra_data` varchar(100) NOT NULL DEFAULT '' COMMENT '额外的信息',
  PRIMARY KEY (`mini_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_power`
-- ----------------------------
DROP TABLE IF EXISTS `sp_power`;
CREATE TABLE `sp_power` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `admin_id` int(4) NOT NULL COMMENT '管理员id',
  `menu_id` int(4) NOT NULL COMMENT '菜单id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=413 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_rank`
-- ----------------------------
DROP TABLE IF EXISTS `sp_rank`;
CREATE TABLE `sp_rank` (
  `idx` tinyint(4) NOT NULL AUTO_INCREMENT,
  `mini_id` int(11) NOT NULL COMMENT '小程序id',
  `orderby` tinyint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `update_time` char(10) NOT NULL COMMENT '更新时间',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `is_active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否显示',
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_rate`
-- ----------------------------
DROP TABLE IF EXISTS `sp_rate`;
CREATE TABLE `sp_rate` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `mini_id` smallint(6) NOT NULL COMMENT '小程序id',
  `appid` char(50) NOT NULL COMMENT '小程序的appid',
  `rate` tinyint(4) NOT NULL COMMENT '用户评分(满分5分)',
  `user_openid` char(50) NOT NULL COMMENT '用户openid',
  `create_time` char(10) NOT NULL COMMENT '评价时间',
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_search`
-- ----------------------------
DROP TABLE IF EXISTS `sp_search`;
CREATE TABLE `sp_search` (
  `idx` smallint(6) NOT NULL AUTO_INCREMENT,
  `mini_id` smallint(6) NOT NULL COMMENT '小程序id',
  `orderby` tinyint(4) NOT NULL DEFAULT '0' COMMENT '小程序排序',
  `create_time` char(10) NOT NULL COMMENT '创建时间',
  `is_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '当前搜索字段是否展示 默认不展示',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '当前搜索字段是否删除',
  `delete_time` char(10) NOT NULL DEFAULT '' COMMENT '当前字段删除时间',
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_setting`
-- ----------------------------
DROP TABLE IF EXISTS `sp_setting`;
CREATE TABLE `sp_setting` (
  `idx` tinyint(4) NOT NULL AUTO_INCREMENT,
  `is_auto_cal_mini_data` tinyint(4) NOT NULL COMMENT '是否自动计算小程序数据',
  `auto_run_intval` smallint(6) NOT NULL DEFAULT '3600' COMMENT '自动运行的间隔时间(单位s)',
  `single_cat_mini_limit` tinyint(4) NOT NULL COMMENT '小程序首页分类显示每个目录允许展示的小程序数量(0为展示全部 最高为10)',
  `default_mini_rate` decimal(2,1) NOT NULL DEFAULT '4.0' COMMENT '默认小程序的评分',
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_update_time`
-- ----------------------------
DROP TABLE IF EXISTS `sp_update_time`;
CREATE TABLE `sp_update_time` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `column_update_time` char(10) NOT NULL,
  `mini_update_time` char(10) NOT NULL,
  `cat_update_time` char(10) NOT NULL,
  `article_update_time` char(10) NOT NULL,
  `create_time` char(10) NOT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_user_fav`
-- ----------------------------
DROP TABLE IF EXISTS `sp_user_fav`;
CREATE TABLE `sp_user_fav` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `user_openid` varchar(50) NOT NULL,
  `fav_id` int(11) NOT NULL COMMENT '用户收藏的id(mini或者column)',
  `fav_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1 mini 2 column',
  `is_fav` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否收藏',
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_user_fav_log`
-- ----------------------------
DROP TABLE IF EXISTS `sp_user_fav_log`;
CREATE TABLE `sp_user_fav_log` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `user_openid` char(50) NOT NULL,
  `fav_id` int(11) NOT NULL COMMENT '收藏的id',
  `fav_type` tinyint(4) NOT NULL COMMENT '收藏的类型 1 小程序 2专题',
  `fav_action` tinyint(4) NOT NULL DEFAULT '0' COMMENT '收藏操作 0 取消收藏 1 收藏',
  `create_time` char(10) NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_usercount`
-- ----------------------------
DROP TABLE IF EXISTS `sp_usercount`;
CREATE TABLE `sp_usercount` (
  `idx` mediumint(9) NOT NULL AUTO_INCREMENT,
  `user_openid` char(70) NOT NULL,
  `open_time` char(30) NOT NULL COMMENT '用户打开小程序的时间',
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=1453 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sp_userinfo`
-- ----------------------------
DROP TABLE IF EXISTS `sp_userinfo`;
CREATE TABLE `sp_userinfo` (
  `user_id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_openid` varchar(50) NOT NULL COMMENT '用户的openid',
  `nickName` varchar(20) NOT NULL DEFAULT '' COMMENT '用户昵称',
  `avatarUrl` varchar(120) NOT NULL DEFAULT '' COMMENT '用户头像地址',
  `city` varchar(20) NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `province` varchar(20) NOT NULL DEFAULT '' COMMENT '用户所在省份',
  `country` varchar(10) NOT NULL DEFAULT '' COMMENT '用户所在国家',
  `gender` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户性别',
  `is_auth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户是否实名认证',
  `language` char(10) NOT NULL,
  `user_qrcode` varchar(80) NOT NULL DEFAULT '' COMMENT '用户二维码推广地址',
  `create_time` char(10) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=882 DEFAULT CHARSET=utf8;