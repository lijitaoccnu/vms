/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50505
Source Host           : 127.0.0.1:3306
Source Database       : publish

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-12-15 18:43:09
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for t_package
-- ----------------------------
DROP TABLE IF EXISTS `t_package`;
CREATE TABLE `t_package` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '更新包ID',
  `uuid` varchar(32) NOT NULL COMMENT '更新包UUID',
  `filename` varchar(32) NOT NULL COMMENT '更新包文件名',
  `savePath` varchar(64) NOT NULL COMMENT '更新包存储目录',
  `saveName` varchar(64) DEFAULT NULL COMMENT '更新包存储文件名',
  `uploadTime` datetime NOT NULL COMMENT '上传时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_project
-- ----------------------------
DROP TABLE IF EXISTS `t_project`;
CREATE TABLE `t_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '项目ID',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '项目名称',
  `code` varchar(32) NOT NULL DEFAULT '' COMMENT '项目编码',
  `version` varchar(32) NOT NULL DEFAULT '' COMMENT '当前版本',
  `rootPath` varchar(255) NOT NULL DEFAULT '' COMMENT '项目根路径',
  `cachePath` varchar(255) NOT NULL DEFAULT '' COMMENT '缓存目录路径',
  `ignorePath` varchar(1024) NOT NULL DEFAULT '' COMMENT '备份过滤路径',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='项目表';

-- ----------------------------
-- Table structure for t_server
-- ----------------------------
DROP TABLE IF EXISTS `t_server`;
CREATE TABLE `t_server` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `projId` int(10) unsigned NOT NULL COMMENT '项目ID',
  `host` varchar(32) NOT NULL COMMENT '服务器地址',
  `user` varchar(16) NOT NULL COMMENT '服务器访问用户',
  `pwd` varchar(32) NOT NULL COMMENT '服务器访问密码',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `server` (`projId`,`host`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='服务器表';

-- ----------------------------
-- Table structure for t_version
-- ----------------------------
DROP TABLE IF EXISTS `t_version`;
CREATE TABLE `t_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `projId` int(10) unsigned NOT NULL COMMENT '项目ID',
  `version` varchar(32) NOT NULL DEFAULT '' COMMENT '版本号',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '版本标题',
  `detail` varchar(512) NOT NULL DEFAULT '' COMMENT '发布内容',
  `package` varchar(32) NOT NULL DEFAULT '' COMMENT '更新包UUID',
  `createBy` char(16) NOT NULL DEFAULT '' COMMENT '创建人',
  `createTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `pushStartTime` datetime DEFAULT NULL COMMENT '推送开始时间',
  `pushOverTime` datetime DEFAULT NULL COMMENT '推送完成时间',
  `publishStartTime` datetime DEFAULT NULL COMMENT '发布时间',
  `publishOverTime` datetime DEFAULT NULL COMMENT '发布完成时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`projId`,`version`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='版本表';
