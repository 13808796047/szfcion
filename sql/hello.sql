/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : fcc

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-11-06 11:28:36
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for account
-- ----------------------------
DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `uid` char(10) NOT NULL COMMENT '账户编号',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '账户类型，0：未认证，1：普通',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '账户状态，1：正常，0：冻结',
  `username` char(11) NOT NULL COMMENT '登录账号',
  `password` char(32) NOT NULL COMMENT '登录密码',
  `safeword` char(32) DEFAULT NULL COMMENT '安全密码',
  `authen` tinyint(4) NOT NULL DEFAULT '0' COMMENT '账户状态，1：通过，0：未认证，2：审核中，3：被拒绝',
  `inviter` char(11) DEFAULT NULL COMMENT '邀请人',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  UNIQUE KEY `username` (`username`),
  KEY `inviter` (`inviter`)
) ENGINE=InnoDB AUTO_INCREMENT=33009 DEFAULT CHARSET=utf8mb4 COMMENT='账户';

-- ----------------------------
-- Table structure for account_audit
-- ----------------------------
DROP TABLE IF EXISTS `account_audit`;
CREATE TABLE `account_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0：未通过，1：已通过',
  `username` char(11) NOT NULL COMMENT '登录账号',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `status` (`status`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='账户审核';

-- ----------------------------
-- Table structure for article
-- ----------------------------
DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排列顺序',
  `top` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `type` tinyint(4) DEFAULT NULL COMMENT '文章类型，1：新闻，8：帮助',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `image` varchar(100) DEFAULT NULL COMMENT '缩略图',
  `content` text NOT NULL COMMENT '内容',
  `date` timestamp NULL DEFAULT NULL COMMENT '显示的时间',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COMMENT='文章';

-- ----------------------------
-- Table structure for boss
-- ----------------------------
DROP TABLE IF EXISTS `boss`;
CREATE TABLE `boss` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `status` tinyint(4) NOT NULL DEFAULT '2' COMMENT '状态，1：正常，0：隐藏：2：待审核',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `title` varchar(200) DEFAULT NULL COMMENT '头衔',
  `advantage` text COMMENT '优势',
  `background` text COMMENT '背景',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `status` (`status`),
  KEY `title` (`title`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='BOSS';

-- ----------------------------
-- Table structure for calendar_log
-- ----------------------------
DROP TABLE IF EXISTS `calendar_log`;
CREATE TABLE `calendar_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `username` char(11) NOT NULL COMMENT '用户账户',
  `action` tinyint(4) NOT NULL DEFAULT '1' COMMENT '操作类型，1：打卡',
  `currency` tinyint(4) NOT NULL DEFAULT '1' COMMENT '赠送的货币类型',
  `money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '赠送的可用货币',
  `machine` tinyint(4) DEFAULT '0' COMMENT '赠送的矿机编号',
  `power` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '赠送的算力',
  `continuity` int(11) NOT NULL DEFAULT '0' COMMENT '连续天数',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `action` (`action`),
  KEY `currency` (`currency`),
  KEY `money` (`money`),
  KEY `machine` (`machine`),
  KEY `power` (`power`),
  KEY `continuity` (`continuity`)
) ENGINE=InnoDB AUTO_INCREMENT=94338 DEFAULT CHARSET=utf8 COMMENT='日历记录';

-- ----------------------------
-- Table structure for clock
-- ----------------------------
DROP TABLE IF EXISTS `clock`;
CREATE TABLE `clock` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `mid` char(10) NOT NULL COMMENT '矿机编号',
  `money` decimal(30,12) DEFAULT '0.000000000000' COMMENT '本次收益',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `mid` (`mid`),
  KEY `date` (`create_at`),
  KEY `m_money` (`money`)
) ENGINE=InnoDB AUTO_INCREMENT=3045866 DEFAULT CHARSET=utf8 COMMENT='打卡表';

-- ----------------------------
-- Table structure for config
-- ----------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:字符，2:数值，3:布尔，4:文件，5:json，6:数组',
  `parent` int(11) DEFAULT '0' COMMENT '上级编号',
  `token` varchar(50) NOT NULL COMMENT '唯一标志',
  `name` varchar(30) NOT NULL COMMENT '配置名称',
  `value` text COMMENT '配置的数据',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='配置';

-- ----------------------------
-- Table structure for contract
-- ----------------------------
DROP TABLE IF EXISTS `contract`;
CREATE TABLE `contract` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排列顺序',
  `visible` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否可见，1：可见，0：隐藏',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态，1：空闲，2：有主，3：瓜分',
  `is_delivery` tinyint(4) NOT NULL DEFAULT '0' COMMENT '可否交货，1：可以，0：不可以',
  `delivery` tinyint(4) NOT NULL DEFAULT '1' COMMENT '交割状态',
  `token` char(10) DEFAULT NULL COMMENT '交割编号',
  `audit` tinyint(4) NOT NULL DEFAULT '1' COMMENT '审核状态：1通过，0未通过',
  `agent` char(11) DEFAULT NULL COMMENT '代理商',
  `owner` char(11) DEFAULT NULL COMMENT '归属人',
  `catalog` tinyint(4) NOT NULL DEFAULT '1' COMMENT '项目分类',
  `title` varchar(50) NOT NULL COMMENT '合约名称',
  `image` varchar(100) NOT NULL COMMENT '合约图片',
  `content` text COMMENT '合约介绍',
  `base_price` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '基础价格',
  `now_price` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '当前价格',
  `inc` decimal(30,12) NOT NULL DEFAULT '1.000000000000' COMMENT '增幅比例',
  `charge` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '手续费比例',
  `loop` int(11) NOT NULL DEFAULT '1' COMMENT '当前第几轮',
  `profit` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '每秒收益',
  `source` text COMMENT '数据，用于编辑后的临时数据',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `agent` (`agent`),
  KEY `owner` (`owner`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`),
  KEY `is_delivery` (`is_delivery`),
  KEY `delivery` (`delivery`),
  KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='合约';

-- ----------------------------
-- Table structure for contract_log
-- ----------------------------
DROP TABLE IF EXISTS `contract_log`;
CREATE TABLE `contract_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `cid` int(11) NOT NULL COMMENT '具体合约的编号',
  `token` char(10) DEFAULT NULL COMMENT '交割编号',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `action` tinyint(4) NOT NULL DEFAULT '1' COMMENT '动作类型，很重要',
  `loop` int(11) NOT NULL DEFAULT '0' COMMENT '当前操作是在第几轮执行的',
  `money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '本次操作花的钱，或是赚的钱',
  `charge` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '本次操作的手续费',
  `target` char(11) DEFAULT NULL COMMENT '转让对象的账号',
  `ratio` float NOT NULL DEFAULT '0' COMMENT '涨跌比例',
  `content` text COMMENT '内容数据',
  `remark` text COMMENT '备注信息',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `username` (`username`),
  KEY `loop` (`loop`),
  KEY `action` (`action`),
  KEY `target` (`target`),
  KEY `ratio` (`ratio`),
  KEY `create_at` (`create_at`),
  KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='合约记录';

-- ----------------------------
-- Table structure for contract_percent
-- ----------------------------
DROP TABLE IF EXISTS `contract_percent`;
CREATE TABLE `contract_percent` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `cid` int(11) NOT NULL COMMENT '具体合约的编号',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `loop` int(11) NOT NULL DEFAULT '1' COMMENT '哪一轮买入的',
  `ratio` float NOT NULL DEFAULT '0' COMMENT '所占比例',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cid_username_loop` (`cid`,`username`,`loop`),
  KEY `cid` (`cid`),
  KEY `username` (`username`),
  KEY `loop` (`loop`),
  KEY `ratio` (`ratio`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='合约比例';

-- ----------------------------
-- Table structure for dashboard
-- ----------------------------
DROP TABLE IF EXISTS `dashboard`;
CREATE TABLE `dashboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `username` char(11) NOT NULL COMMENT '用户账户',
  `power` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '个人算力（包含个人、矿机、团队等所有）',
  `team_power` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '团队算力',
  `team_count` int(11) NOT NULL DEFAULT '0' COMMENT '团队总人数',
  `authen` int(11) NOT NULL DEFAULT '0' COMMENT '实名认证人数',
  `machine_count` int(11) NOT NULL DEFAULT '0' COMMENT '矿机数量',
  `machine_power` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '矿机算力',
  `machine_expire` int(11) NOT NULL DEFAULT '0' COMMENT '过期矿机数量',
  `one` int(11) NOT NULL DEFAULT '0' COMMENT '1代数量',
  `two` int(11) NOT NULL DEFAULT '0' COMMENT '2代数量',
  `three` int(11) NOT NULL DEFAULT '0' COMMENT '3代数量',
  `four` int(11) NOT NULL DEFAULT '0' COMMENT '4代数量',
  `five` int(11) NOT NULL DEFAULT '0' COMMENT '5代数量',
  `six` int(11) NOT NULL DEFAULT '0' COMMENT '6代数量',
  `seven` int(11) NOT NULL DEFAULT '0' COMMENT '7代数量',
  `eight` int(11) NOT NULL DEFAULT '0' COMMENT '8代数量',
  `lv0` int(11) NOT NULL DEFAULT '0' COMMENT '未认证用户数量',
  `lv1` int(11) NOT NULL DEFAULT '0' COMMENT '1级用户数量',
  `lv2` int(11) NOT NULL DEFAULT '0' COMMENT '2级用户数量',
  `lv3` int(11) NOT NULL DEFAULT '0' COMMENT '3级用户数量',
  `lv4` int(11) NOT NULL DEFAULT '0' COMMENT '4级用户数量',
  `lv5` int(11) NOT NULL DEFAULT '0' COMMENT '5级用户数量',
  `lv6` int(11) NOT NULL DEFAULT '0' COMMENT '6级用户数量',
  `lv7` int(11) NOT NULL DEFAULT '0' COMMENT '7级用户数量',
  `lv8` int(11) NOT NULL DEFAULT '0' COMMENT '8级用户数量',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=33007 DEFAULT CHARSET=utf8 COMMENT='仪表盘';

-- ----------------------------
-- Table structure for event_log
-- ----------------------------
DROP TABLE IF EXISTS `event_log`;
CREATE TABLE `event_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型，1：刮刮卡',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态，0：待提货，1：已发货，2：待发货',
  `hit` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否中奖，0：没有，1：中奖',
  `reward` int(11) NOT NULL DEFAULT '0' COMMENT '中的具体奖品的编号',
  `reward_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '奖品类型，1：矿机，2：实物，3：话费',
  `receive` text COMMENT '收货地址信息',
  `send` text COMMENT '发货信息',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `hit` (`hit`),
  KEY `reward` (`reward`),
  KEY `reward_type` (`reward_type`),
  KEY `create_at` (`create_at`)
) ENGINE=InnoDB AUTO_INCREMENT=24409 DEFAULT CHARSET=utf8 COMMENT='活动记录';

-- ----------------------------
-- Table structure for funding
-- ----------------------------
DROP TABLE IF EXISTS `funding`;
CREATE TABLE `funding` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排列顺序',
  `visible` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否可见，1：可见，0：隐藏',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '类型，1：普通项目，8：官方项目',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态，1：进行中，2：审核中，3：已结束',
  `currency` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：可用资金，8：RMB',
  `owner` char(11) DEFAULT NULL COMMENT '项目归属人',
  `catalog` tinyint(4) NOT NULL DEFAULT '1' COMMENT '项目分类',
  `title` varchar(50) NOT NULL COMMENT '项目名称',
  `image` varchar(100) NOT NULL COMMENT '项目图片',
  `content` text COMMENT '项目介绍',
  `target` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '目标金额',
  `current` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '当前金额',
  `people` int(11) NOT NULL DEFAULT '0' COMMENT '参与人数',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '参与次数',
  `expire_at` timestamp NULL DEFAULT NULL COMMENT '到期时间',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `sort` (`sort`),
  KEY `visible` (`visible`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `owner` (`owner`),
  KEY `catalog` (`catalog`),
  KEY `title` (`title`),
  KEY `people` (`people`),
  KEY `count` (`count`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='众筹';

-- ----------------------------
-- Table structure for funding_log
-- ----------------------------
DROP TABLE IF EXISTS `funding_log`;
CREATE TABLE `funding_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `fid` int(11) NOT NULL COMMENT '项目编号',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '类型，1：普通项目，8：官方项目',
  `currency` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：可用资金，8：RMB',
  `action` tinyint(4) NOT NULL DEFAULT '1' COMMENT '操作类型，1：投资，8：结束',
  `username` char(11) DEFAULT NULL COMMENT '用户账号',
  `money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '投资金额',
  `charge` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '手续费',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `type` (`type`),
  KEY `action` (`action`),
  KEY `username` (`username`),
  KEY `money` (`money`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='众筹记录';

-- ----------------------------
-- Table structure for fund_holder
-- ----------------------------
DROP TABLE IF EXISTS `fund_holder`;
CREATE TABLE `fund_holder` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `fund` int(11) NOT NULL DEFAULT '0' COMMENT '基金编号',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '总金额',
  `expire_at` timestamp NULL DEFAULT NULL COMMENT '考察时间',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fund` (`fund`),
  KEY `money` (`money`),
  KEY `expire_at` (`expire_at`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='基金持有';

-- ----------------------------
-- Table structure for fund_log
-- ----------------------------
DROP TABLE IF EXISTS `fund_log`;
CREATE TABLE `fund_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `fund` int(11) NOT NULL DEFAULT '0' COMMENT '基金编号',
  `notice` int(11) NOT NULL DEFAULT '0' COMMENT '公示编号',
  `action` tinyint(4) NOT NULL DEFAULT '1' COMMENT '动作，1：买商品投入，2：主动投入，3：帮别人投入，6，使用基金',
  `username` char(11) DEFAULT NULL COMMENT '捐赠者',
  `friend` char(11) DEFAULT NULL COMMENT '出钱帮好友捐赠的人',
  `product` int(11) DEFAULT NULL COMMENT '商品编号',
  `price` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '商品价格',
  `money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '具体金额',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `fund` (`fund`),
  KEY `action` (`action`),
  KEY `username` (`username`),
  KEY `friend` (`friend`),
  KEY `product` (`product`),
  KEY `price` (`price`),
  KEY `money` (`money`),
  KEY `create_at` (`create_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='基金记录';

-- ----------------------------
-- Table structure for fund_notice
-- ----------------------------
DROP TABLE IF EXISTS `fund_notice`;
CREATE TABLE `fund_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `fund` int(11) NOT NULL DEFAULT '0' COMMENT '基金编号',
  `image` varchar(100) NOT NULL COMMENT '图片',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '所需金额',
  `reason` text COMMENT '原因',
  `people` int(11) DEFAULT '0' COMMENT '参与人数',
  `content` text COMMENT '具体内容',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `fund` (`fund`),
  KEY `username` (`username`),
  KEY `money` (`money`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='基金公示';

-- ----------------------------
-- Table structure for global_bonus
-- ----------------------------
DROP TABLE IF EXISTS `global_bonus`;
CREATE TABLE `global_bonus` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `date` date NOT NULL COMMENT '具体日期',
  `charge` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '本次手续费',
  `rate` text NOT NULL COMMENT '各级别的比例配置',
  `people` int(11) NOT NULL DEFAULT '0' COMMENT '总发放人数',
  `money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '总发放金额',
  `lv0` text COMMENT 'lv0的情况',
  `lv1` text COMMENT 'lv1的情况',
  `lv2` text COMMENT 'lv2的情况',
  `lv3` text COMMENT 'lv3的情况',
  `lv4` text COMMENT 'lv4的情况',
  `lv5` text COMMENT 'lv5的情况',
  `lv6` text COMMENT 'lv6的情况',
  `lv7` text COMMENT 'lv7的情况',
  `lv8` text COMMENT 'lv8的情况',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='全球分红';

-- ----------------------------
-- Table structure for imtoken
-- ----------------------------
DROP TABLE IF EXISTS `imtoken`;
CREATE TABLE `imtoken` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `tid` char(10) NOT NULL COMMENT '订单编号',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1充值，2提现',
  `status` tinyint(4) NOT NULL DEFAULT '2' COMMENT '1成功，2待审核，0失败',
  `username` char(11) NOT NULL COMMENT '自己的账号',
  `number` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '数量',
  `charge` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '手续费',
  `address` varchar(100) DEFAULT NULL COMMENT '我的钱包地址',
  `qrcode` varchar(100) DEFAULT NULL COMMENT '我的钱包二维码',
  `certificate` text COMMENT '凭证，图片列表',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tid` (`tid`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `username` (`username`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8 COMMENT='imtoken';

-- ----------------------------
-- Table structure for log
-- ----------------------------
DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `type` tinyint(4) NOT NULL COMMENT '类型',
  `text` text COMMENT '描述',
  `ip` char(20) DEFAULT NULL COMMENT 'IP地址',
  `ua` text COMMENT 'UserAgent',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `type` (`type`),
  KEY `ip` (`ip`),
  KEY `create_at` (`create_at`)
) ENGINE=MyISAM AUTO_INCREMENT=1754657 DEFAULT CHARSET=utf8 COMMENT='日志表';

-- ----------------------------
-- Table structure for machine
-- ----------------------------
DROP TABLE IF EXISTS `machine`;
CREATE TABLE `machine` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `mid` char(10) NOT NULL COMMENT '矿机编号',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态，1：正常，0：停止',
  `source` tinyint(4) DEFAULT '1' COMMENT '来源，1：购买，2：赠送',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `product` int(11) NOT NULL COMMENT '产品编号',
  `divide` tinyint(4) DEFAULT '0' COMMENT '级别，0：仅自己收益，1：1级也能获益，2：2级也能获益，以此类推',
  `name` varchar(30) NOT NULL COMMENT '产品名称',
  `cycle` int(11) NOT NULL COMMENT '周期，单位小时',
  `income` decimal(30,12) NOT NULL COMMENT '预计收益',
  `power` decimal(30,12) NOT NULL COMMENT '算力，hash/h',
  `interval` int(11) NOT NULL DEFAULT '3600' COMMENT '收矿间隔、默认一小时',
  `price` decimal(30,12) NOT NULL COMMENT '价格',
  `profit` decimal(30,12) DEFAULT '0.000000000000' COMMENT '累计收益',
  `count` int(11) DEFAULT '0' COMMENT '收矿次数',
  `profit_at` timestamp NULL DEFAULT NULL COMMENT '上次收矿时间',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mid` (`mid`),
  KEY `status` (`status`),
  KEY `username` (`username`),
  KEY `product` (`product`),
  KEY `profit` (`profit`),
  KEY `income` (`income`)
) ENGINE=InnoDB AUTO_INCREMENT=118676 DEFAULT CHARSET=utf8 COMMENT='矿机表';

-- ----------------------------
-- Table structure for market
-- ----------------------------
DROP TABLE IF EXISTS `market`;
CREATE TABLE `market` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `date` date NOT NULL COMMENT '具体日期',
  `price` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '指导价格',
  `high` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '最高价格',
  `low` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '最低价格',
  `buy_count` int(11) NOT NULL DEFAULT '0' COMMENT '买入订单数量',
  `buy_money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '买入货币数量',
  `sell_count` int(11) NOT NULL DEFAULT '0' COMMENT '卖出订单数量',
  `sell_money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '卖入货币数量',
  `success_count` int(11) NOT NULL DEFAULT '0' COMMENT '成交订单数量',
  `success_money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '成交金额数量',
  `charge` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '总收取手续费',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8 COMMENT='行情';

-- ----------------------------
-- Table structure for message
-- ----------------------------
DROP TABLE IF EXISTS `message`;
CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `content` text COMMENT '具体内容',
  `reply` text COMMENT '系统回复',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='留言';

-- ----------------------------
-- Table structure for oauth
-- ----------------------------
DROP TABLE IF EXISTS `oauth`;
CREATE TABLE `oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `oid` char(32) NOT NULL COMMENT '唯一编号，md5(p+a+o)',
  `platform` tinyint(4) NOT NULL DEFAULT '1' COMMENT '平台，1：微信，2：QQ',
  `appid` varchar(50) NOT NULL COMMENT 'AppID',
  `openid` varchar(50) NOT NULL COMMENT 'OpenID',
  `unionid` varchar(50) DEFAULT NULL COMMENT 'UnionID',
  `username` char(11) DEFAULT NULL COMMENT '用户账号，没有代表仅授权但并未注册成功',
  `avatar` varchar(100) DEFAULT NULL COMMENT '头像',
  `nickname` varchar(30) DEFAULT NULL COMMENT '昵称',
  `gender` tinyint(4) NOT NULL DEFAULT '1' COMMENT '性别，1：男，2：女',
  `province` varchar(20) DEFAULT NULL COMMENT '省份',
  `city` varchar(20) DEFAULT NULL COMMENT '城市',
  `county` varchar(20) DEFAULT NULL COMMENT '区县',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `oid` (`oid`),
  UNIQUE KEY `platform_appid_openid` (`platform`,`appid`,`openid`),
  UNIQUE KEY `platform_appid_unionid` (`platform`,`appid`,`unionid`),
  UNIQUE KEY `platform_appid_openid_username` (`platform`,`appid`,`openid`,`username`),
  UNIQUE KEY `platform_appid_unionid_username` (`platform`,`appid`,`unionid`,`username`),
  KEY `platform` (`platform`),
  KEY `appid` (`appid`),
  KEY `openid` (`openid`),
  KEY `username` (`username`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='第三方授权登录';

-- ----------------------------
-- Table structure for order
-- ----------------------------
DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `oid` char(10) NOT NULL COMMENT '订单编号',
  `status` tinyint(4) DEFAULT '2' COMMENT '状态，1：正常（已发货），0：失败，2：待发货，3：待确认',
  `seller` char(11) DEFAULT NULL COMMENT '卖家账号',
  `buyer` char(11) NOT NULL COMMENT '买家账号',
  `product` int(11) NOT NULL COMMENT '产品编号',
  `title` varchar(30) NOT NULL COMMENT '产品名称',
  `currency` tinyint(4) NOT NULL DEFAULT '1' COMMENT '支付的货币类型',
  `price` decimal(30,12) NOT NULL COMMENT '价格',
  `charge` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '手续费',
  `code` char(32) DEFAULT NULL COMMENT '激活码',
  `power` decimal(30,12) NOT NULL COMMENT '奖励算力',
  `address` text COMMENT '收货地址',
  `express` text COMMENT '快递信息',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `oid` (`oid`),
  KEY `status` (`status`),
  KEY `seller` (`seller`),
  KEY `buyer` (`buyer`),
  KEY `product` (`product`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB AUTO_INCREMENT=96197 DEFAULT CHARSET=utf8 COMMENT='商城订单';

-- ----------------------------
-- Table structure for payment
-- ----------------------------
DROP TABLE IF EXISTS `payment`;
CREATE TABLE `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `pid` char(10) NOT NULL COMMENT '订单编号',
  `token` varchar(32) DEFAULT NULL COMMENT '外部编号',
  `status` tinyint(4) NOT NULL DEFAULT '2' COMMENT '1成功，2待付款，0失败',
  `username` char(11) NOT NULL COMMENT '自己的账号',
  `channel` varchar(20) NOT NULL COMMENT '充值渠道',
  `currency` tinyint(4) NOT NULL DEFAULT '1' COMMENT '要充值的货币',
  `number` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '数量',
  `charge` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '手续费',
  `raw` text COMMENT '原始数据',
  `notify_at` timestamp NULL DEFAULT NULL COMMENT '付款时间',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pid` (`pid`),
  KEY `token` (`token`),
  KEY `status` (`status`),
  KEY `username` (`username`),
  KEY `channel` (`channel`),
  KEY `currency` (`currency`),
  KEY `number` (`number`),
  KEY `charge` (`charge`),
  KEY `notify_at` (`notify_at`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB AUTO_INCREMENT=21105 DEFAULT CHARSET=utf8 COMMENT='充值';

-- ----------------------------
-- Table structure for pool
-- ----------------------------
DROP TABLE IF EXISTS `pool`;
CREATE TABLE `pool` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `action` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型，1：领取收益，2：使用道具，3：加入矿池',
  `power` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '当前的算力是多少，使用道具之前的',
  `prop` varchar(30) DEFAULT NULL COMMENT '当前使用的道具名称',
  `spend` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '花费了多少',
  `reward` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '奖励了多少',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `action` (`action`),
  KEY `prop` (`prop`),
  KEY `create_at` (`create_at`)
) ENGINE=InnoDB AUTO_INCREMENT=205342 DEFAULT CHARSET=utf8 COMMENT='共享矿池';

-- ----------------------------
-- Table structure for profile
-- ----------------------------
DROP TABLE IF EXISTS `profile`;
CREATE TABLE `profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `username` char(11) NOT NULL COMMENT '用户账户',
  `nickname` varchar(10) DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(100) DEFAULT NULL COMMENT '头像',
  `wechat` varchar(32) DEFAULT NULL COMMENT '微信账号',
  `qq` varchar(11) DEFAULT NULL COMMENT 'QQ号码',
  `alipay` varchar(32) DEFAULT NULL COMMENT '支付宝',
  `realname` char(4) DEFAULT NULL COMMENT '真实姓名',
  `idcard` char(18) DEFAULT NULL COMMENT '身份证号码',
  `certificate` text COMMENT '证件图片',
  `authen_reason` text COMMENT '实名拒绝认证理由',
  `bankname` varchar(10) DEFAULT NULL COMMENT '银行名称',
  `bankcard` varchar(30) DEFAULT NULL COMMENT '银行卡号',
  `bankaddress` varchar(30) DEFAULT NULL COMMENT '分行地址',
  `phone` char(11) DEFAULT NULL COMMENT '预留手机号',
  `province` int(11) DEFAULT NULL COMMENT '省',
  `city` int(11) DEFAULT NULL COMMENT '市',
  `county` int(11) DEFAULT NULL COMMENT '区',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `idcard` (`idcard`),
  KEY `province` (`province`),
  KEY `city` (`city`),
  KEY `county` (`county`)
) ENGINE=InnoDB AUTO_INCREMENT=33007 DEFAULT CHARSET=utf8mb4 COMMENT='档案';

-- ----------------------------
-- Table structure for record
-- ----------------------------
DROP TABLE IF EXISTS `record`;
CREATE TABLE `record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `rid` char(32) NOT NULL COMMENT '对外编号',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `currency` tinyint(4) NOT NULL COMMENT '具体货币',
  `business` tinyint(4) NOT NULL COMMENT '具体业务',
  `before` decimal(30,12) DEFAULT '0.000000000000' COMMENT '原本货币数量',
  `now` decimal(30,12) DEFAULT '0.000000000000' COMMENT '本次货币数量',
  `after` decimal(30,12) DEFAULT '0.000000000000' COMMENT '最后货币数量',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rid_currency` (`rid`,`currency`),
  KEY `username` (`username`),
  KEY `currency` (`currency`),
  KEY `business` (`business`),
  KEY `now` (`now`)
) ENGINE=InnoDB AUTO_INCREMENT=5013311 DEFAULT CHARSET=utf8 COMMENT='流水';

-- ----------------------------
-- Table structure for region
-- ----------------------------
DROP TABLE IF EXISTS `region`;
CREATE TABLE `region` (
  `code` int(11) NOT NULL COMMENT '地区编码',
  `type` tinyint(4) NOT NULL DEFAULT '3' COMMENT '类型，1省，2市，3区',
  `province` int(11) DEFAULT NULL COMMENT '省编码',
  `province_name` varchar(30) DEFAULT NULL COMMENT '省名字',
  `city` int(11) DEFAULT NULL COMMENT '市编码',
  `city_name` varchar(30) DEFAULT NULL COMMENT '市名字',
  `name` varchar(30) DEFAULT NULL COMMENT '地区名字',
  `address` varchar(90) DEFAULT NULL COMMENT '完整地区',
  PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='地区';

-- ----------------------------
-- Table structure for sms
-- ----------------------------
DROP TABLE IF EXISTS `sms`;
CREATE TABLE `sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `type` int(11) NOT NULL COMMENT '验证类型，取自模板编号',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态，1：正常，0：已使用',
  `mobile` char(11) NOT NULL COMMENT '手机号码',
  `data` text COMMENT '数据内容',
  `ip` varchar(20) DEFAULT NULL COMMENT 'IP地址',
  `ua` text COMMENT 'UserAgent',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=40220 DEFAULT CHARSET=utf8 COMMENT='短信';

-- ----------------------------
-- Table structure for staff_account
-- ----------------------------
DROP TABLE IF EXISTS `staff_account`;
CREATE TABLE `staff_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态，1：正常，0：停用',
  `username` varchar(30) NOT NULL COMMENT '员工账号',
  `password` char(32) NOT NULL COMMENT '用户密码',
  `ip` varchar(20) DEFAULT NULL COMMENT 'IP地址',
  `ua` text COMMENT 'UserAgent',
  `login_at` timestamp NULL DEFAULT NULL COMMENT '登录时间',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='员工 - 账号';

-- ----------------------------
-- Table structure for staff_log
-- ----------------------------
DROP TABLE IF EXISTS `staff_log`;
CREATE TABLE `staff_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `staff` int(11) NOT NULL COMMENT '员工编号，0为超级管理员',
  `path` varchar(200) DEFAULT NULL COMMENT '访问路径',
  `method` varchar(20) DEFAULT NULL COMMENT '访问方式',
  `param` mediumtext COMMENT '具体参数',
  `ip` varchar(30) DEFAULT NULL COMMENT 'IP地址',
  `ua` text COMMENT '设备情况',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `staff` (`staff`),
  KEY `path` (`path`),
  KEY `method` (`method`),
  KEY `ip` (`ip`),
  KEY `create_at` (`create_at`)
) ENGINE=MyISAM AUTO_INCREMENT=402295 DEFAULT CHARSET=utf8 COMMENT='员工 - 记录';

-- ----------------------------
-- Table structure for staff_power
-- ----------------------------
DROP TABLE IF EXISTS `staff_power`;
CREATE TABLE `staff_power` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `staff` int(11) NOT NULL COMMENT '员工编号',
  `role` int(11) NOT NULL COMMENT '节点编号',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `staff` (`staff`),
  KEY `role` (`role`),
  KEY `create_at` (`create_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1053 DEFAULT CHARSET=utf8 COMMENT='员工 - 权限';

-- ----------------------------
-- Table structure for staff_role
-- ----------------------------
DROP TABLE IF EXISTS `staff_role`;
CREATE TABLE `staff_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `parent` int(11) DEFAULT NULL COMMENT '上级节点',
  `visable` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否可见',
  `type` tinyint(4) NOT NULL DEFAULT '3' COMMENT '节点类型，1主节点，2次节点，3操作',
  `name` varchar(30) NOT NULL COMMENT '节点名称',
  `path` varchar(50) DEFAULT NULL COMMENT '节点路径',
  `icon` varchar(30) DEFAULT NULL COMMENT '节点图标',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `path` (`path`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8911 DEFAULT CHARSET=utf8 COMMENT='员工 - 节点';

-- ----------------------------
-- Table structure for store
-- ----------------------------
DROP TABLE IF EXISTS `store`;
CREATE TABLE `store` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `sort` int(11) DEFAULT '0' COMMENT '排列顺序，数字越大越靠前',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态，1：在售，0：停售',
  `audit` tinyint(4) DEFAULT '1' COMMENT '审核，1：通过，0：待审核',
  `catalog` tinyint(4) NOT NULL DEFAULT '1' COMMENT '类目，1：矿机',
  `username` char(11) DEFAULT NULL COMMENT '用户账号，为空表示管理员发布',
  `divide` tinyint(4) DEFAULT '0' COMMENT '级别，0：仅自己收益，1：1级也能获益，2：2级也能获益，以此类推',
  `title` varchar(30) NOT NULL COMMENT '产品名称',
  `image` varchar(100) DEFAULT NULL COMMENT '产品图片',
  `cycle` int(11) NOT NULL COMMENT '周期，单位小时',
  `income` decimal(30,12) NOT NULL COMMENT '预计收益',
  `power` decimal(30,12) NOT NULL COMMENT '奖励的算力',
  `interval` int(11) NOT NULL DEFAULT '3600' COMMENT '收矿间隔、默认一小时',
  `price` decimal(30,12) NOT NULL COMMENT '价格',
  `price_score` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '积分价格',
  `limit` int(11) DEFAULT '0' COMMENT '每人限购，0为不限制',
  `day` int(11) DEFAULT '0' COMMENT '每天限购，0为不限制',
  `sales` int(11) DEFAULT '0' COMMENT '销量',
  `stock` int(11) DEFAULT '0' COMMENT '库存',
  `content` text COMMENT '产品介绍',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `catalog` (`catalog`),
  KEY `username` (`username`),
  KEY `divide` (`divide`),
  KEY `title` (`title`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COMMENT='商城表';

-- ----------------------------
-- Table structure for ticket
-- ----------------------------
DROP TABLE IF EXISTS `ticket`;
CREATE TABLE `ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `type` char(10) NOT NULL DEFAULT '1' COMMENT '券的类型，1：注册，Mxx开头为矿机购买',
  `username` char(11) DEFAULT NULL COMMENT '使用者账号',
  `token` char(32) NOT NULL COMMENT '具体票券',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `type` (`type`),
  KEY `username` (`username`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='票券';

-- ----------------------------
-- Table structure for trade
-- ----------------------------
DROP TABLE IF EXISTS `trade`;
CREATE TABLE `trade` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `tid` char(10) NOT NULL COMMENT '订单编号',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '类型，1：买入，2：出售',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态，0：失败，1：匹配中，2：待付款，3：待确认，8：成功，4：投诉中',
  `is_rmb` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已扣人民币',
  `is_deposit` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已扣押金',
  `number` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '数量',
  `price` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '价格',
  `charge` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '手续费',
  `owner` char(11) NOT NULL COMMENT '自己的账号',
  `target` char(11) DEFAULT NULL COMMENT '对方的账号',
  `secret` char(10) DEFAULT NULL COMMENT '当前暗号，三十六进制的当前时间戳，每次别人退出交易后更新暗号',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tid` (`tid`),
  KEY `owner` (`owner`),
  KEY `target` (`target`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`),
  KEY `is_rmb` (`is_rmb`),
  KEY `is_deposit` (`is_deposit`)
) ENGINE=InnoDB AUTO_INCREMENT=188298 DEFAULT CHARSET=utf8 COMMENT='交易';

-- ----------------------------
-- Table structure for trade_audit
-- ----------------------------
DROP TABLE IF EXISTS `trade_audit`;
CREATE TABLE `trade_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `tid` char(10) NOT NULL COMMENT '订单编号',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态，1：通过，0：待审核',
  `remark` text COMMENT '备注',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `status` (`status`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='交易审核';

-- ----------------------------
-- Table structure for trade_log
-- ----------------------------
DROP TABLE IF EXISTS `trade_log`;
CREATE TABLE `trade_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `tid` char(10) NOT NULL COMMENT '订单编号',
  `secret` char(10) NOT NULL COMMENT '暗号',
  `command` tinyint(4) NOT NULL DEFAULT '1' COMMENT '命令，此次执行的具体操作',
  `username` char(11) NOT NULL COMMENT '用户账号',
  `content` text COMMENT '内容，如文字或图片',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `secret` (`secret`)
) ENGINE=InnoDB AUTO_INCREMENT=191235 DEFAULT CHARSET=utf8 COMMENT='交易记录';

-- ----------------------------
-- Table structure for transfer
-- ----------------------------
DROP TABLE IF EXISTS `transfer`;
CREATE TABLE `transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `tid` char(10) NOT NULL COMMENT '订单编号',
  `owner` char(11) NOT NULL COMMENT '自己的账号',
  `target` char(11) DEFAULT NULL COMMENT '对方的账号',
  `number` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '数量',
  `charge` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '手续费',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tid` (`tid`),
  KEY `owner` (`owner`),
  KEY `target` (`target`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB AUTO_INCREMENT=277 DEFAULT CHARSET=utf8 COMMENT='转账';

-- ----------------------------
-- Table structure for upgrade
-- ----------------------------
DROP TABLE IF EXISTS `upgrade`;
CREATE TABLE `upgrade` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `username` char(11) NOT NULL COMMENT '用户账户',
  `level` tinyint(4) NOT NULL DEFAULT '1' COMMENT '具体级别',
  `money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '赠送的可用货币',
  `machine` text COMMENT '赠送的矿机',
  `power` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '赠送的算力',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_level` (`username`,`level`)
) ENGINE=InnoDB AUTO_INCREMENT=22417 DEFAULT CHARSET=utf8 COMMENT='升级';

-- ----------------------------
-- Table structure for uuid
-- ----------------------------
DROP TABLE IF EXISTS `uuid`;
CREATE TABLE `uuid` (
  `id` char(32) NOT NULL COMMENT '具体编号',
  `type` tinyint(4) NOT NULL COMMENT '业务类型',
  PRIMARY KEY (`id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='UUID';

-- ----------------------------
-- Table structure for wallet
-- ----------------------------
DROP TABLE IF EXISTS `wallet`;
CREATE TABLE `wallet` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `username` char(11) NOT NULL COMMENT '用户账户',
  `rmb` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '人民币',
  `money` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '可用货币，账户余额',
  `deposit` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '冻结货币，账户存款',
  `release` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '待释放资金',
  `score` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '可用积分',
  `score_deposit` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '冻结积分',
  `spend` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '商城消费额',
  `profit` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '矿机收益',
  `team_profit` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '团队矿机收益',
  `bonus` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '全球分红',
  `trade` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '交易分红',
  `sell` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '成功卖出的数量',
  `buy` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '成功买入的数量',
  `ts_in` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '转账转入的数量',
  `ts_out` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '转账转出的数量',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=33007 DEFAULT CHARSET=utf8 COMMENT='钱包';

-- ----------------------------
-- Table structure for withdraw
-- ----------------------------
DROP TABLE IF EXISTS `withdraw`;
CREATE TABLE `withdraw` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统编号',
  `wid` char(10) NOT NULL COMMENT '订单编号',
  `status` tinyint(4) NOT NULL DEFAULT '2' COMMENT '1成功，2待审核，0失败',
  `username` char(11) NOT NULL COMMENT '自己的账号',
  `channel` varchar(20) NOT NULL COMMENT '渠道',
  `currency` tinyint(4) NOT NULL DEFAULT '1' COMMENT '要提现的货币',
  `number` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '数量',
  `charge` decimal(30,12) NOT NULL DEFAULT '0.000000000000' COMMENT '手续费',
  `source` text COMMENT '资料，如支付宝账号或银行卡号的备份',
  `remark` text COMMENT '备注信息',
  `reason` text COMMENT '理由：拒绝的理由',
  `notify_at` timestamp NULL DEFAULT NULL COMMENT '到账时间',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `wid` (`wid`),
  KEY `status` (`status`),
  KEY `username` (`username`),
  KEY `channel` (`channel`),
  KEY `currency` (`currency`),
  KEY `number` (`number`),
  KEY `charge` (`charge`),
  KEY `notify_at` (`notify_at`),
  KEY `create_at` (`create_at`),
  KEY `update_at` (`update_at`)
) ENGINE=InnoDB AUTO_INCREMENT=10107 DEFAULT CHARSET=utf8 COMMENT='提现';
