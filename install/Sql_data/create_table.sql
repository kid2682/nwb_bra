/*
 * Date: 2012-09-17 13:28:08
*/
DROP TABLE IF EXISTS `vg_access`;
CREATE TABLE `vg_access` (
  `role_id` int(5) NOT NULL,
  `node_id` int(5) NOT NULL,
  KEY `role_id` (`role_id`),
  KEY `node_id` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_ad`
-- ----------------------------
DROP TABLE IF EXISTS `vg_ad`;
CREATE TABLE `vg_ad` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `board_id` smallint(5) NOT NULL,
  `type` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `url` varchar(255) NOT NULL,
  `code` text NOT NULL,
  `start_time` int(10) NOT NULL,
  `end_time` int(10) NOT NULL,
  `clicks` int(10) NOT NULL DEFAULT '0',
  `add_time` int(10) NOT NULL,
  `ordid` int(10) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `board_id` (`board_id`,`start_time`,`end_time`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_adboard`
-- ----------------------------
DROP TABLE IF EXISTS `vg_adboard`;
CREATE TABLE `vg_adboard` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `width` smallint(5) NOT NULL,
  `height` smallint(5) NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `vg_admin`
-- ----------------------------
DROP TABLE IF EXISTS `vg_admin`;
CREATE TABLE `vg_admin` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `add_time` int(10) DEFAULT NULL,
  `last_time` int(10) DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `role_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_album`
-- ----------------------------
DROP TABLE IF EXISTS `vg_album`;
CREATE TABLE `vg_album` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '默认专辑',
  `img` varchar(255) DEFAULT NULL,
  `uid` int(10) NOT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `cid` int(10) NOT NULL DEFAULT '0',
  `sort_order` int(10) DEFAULT '0',
  `recommend_count` int(10) NOT NULL,
  `recommend` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `add_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recommend` (`recommend`),
  KEY `sort_order` (`sort_order`),
  KEY `uid` (`uid`),
  KEY `status` (`status`,`cid`,`sort_order`),
  KEY `recommend_count` (`recommend_count`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of vg_album
-- ----------------------------

-- ----------------------------
-- Table structure for `vg_album_cate`
-- ----------------------------
DROP TABLE IF EXISTS `vg_album_cate`;
CREATE TABLE `vg_album_cate` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '我的专辑',
  `add_time` int(10) NOT NULL DEFAULT '0',
  `album_num` int(10) NOT NULL DEFAULT '0',
  `sort_order` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sort_order` (`status`,`sort_order`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_album_items`
-- ----------------------------
DROP TABLE IF EXISTS `vg_album_items`;
CREATE TABLE `vg_album_items` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `items_id` int(10) NOT NULL DEFAULT '0',
  `pid` int(10) NOT NULL DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `add_time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `vg_album_recommend`
-- ----------------------------
DROP TABLE IF EXISTS `vg_album_recommend`;
CREATE TABLE `vg_album_recommend` (
  `album_id` int(10) DEFAULT NULL,
  `uid` int(10) DEFAULT NULL,
  KEY `album_id` (`album_id`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_article`
-- ----------------------------
DROP TABLE IF EXISTS `vg_article`;
CREATE TABLE `vg_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` tinyint(4) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `orig` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `abst` varchar(255) NOT NULL,
  `info` mediumtext NOT NULL,
  `add_time` datetime NOT NULL,
  `ordid` tinyint(4) NOT NULL,
  `is_hot` tinyint(1) NOT NULL DEFAULT '0',
  `is_best` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-待审核 1-已审核',
  `seo_title` varchar(255) NOT NULL,
  `seo_keys` varchar(255) NOT NULL,
  `seo_desc` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_best` (`is_best`),
  KEY `add_time` (`add_time`),
  KEY `cate_id` (`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_article_cate`
-- ----------------------------
DROP TABLE IF EXISTS `vg_article_cate`;
CREATE TABLE `vg_article_cate` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `alias` varchar(50) NOT NULL,
  `pid` smallint(4) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `article_nums` int(10) unsigned NOT NULL,
  `sort_order` smallint(4) unsigned NOT NULL,
  `seo_title` varchar(255) NOT NULL,
  `seo_keys` varchar(255) NOT NULL,
  `seo_desc` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `vg_auto_collect`
-- ----------------------------
DROP TABLE IF EXISTS `vg_auto_collect`;
CREATE TABLE `vg_auto_collect` (
  `id` char(4) NOT NULL,
  `value` varchar(200) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_auto_collect_date`
-- ----------------------------
DROP TABLE IF EXISTS `vg_auto_collect_date`;
CREATE TABLE `vg_auto_collect_date` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `add_date` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_cash_back_log`
-- ----------------------------
DROP TABLE IF EXISTS `vg_cash_back_log`;
CREATE TABLE `vg_cash_back_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `uname` varchar(40) NOT NULL,
  `before_money` decimal(10,2) DEFAULT NULL,
  `after_money` decimal(10,2) DEFAULT NULL,
  `in_money` decimal(10,2) DEFAULT NULL,
  `out_money` decimal(10,2) DEFAULT NULL,
  `time` varchar(20) NOT NULL,
  `type` int(1) NOT NULL COMMENT '1表示收入，2表示支出',
  `info` varchar(100) NOT NULL,
  `sign` char(40) NOT NULL,
  `before_jifenbao` decimal(10,0) DEFAULT NULL,
  `after_jifenbao` decimal(10,0) DEFAULT NULL,
  `in_jifenbao` decimal(10,0) DEFAULT NULL,
  `out_jifenbao` decimal(10,0) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `vg_collect_miao`
-- ----------------------------
DROP TABLE IF EXISTS `vg_collect_miao`;
CREATE TABLE `vg_collect_miao` (
  `cate_id` smallint(4) NOT NULL,
  `collect_time` int(10) NOT NULL DEFAULT '0',
  UNIQUE KEY `cate_id` (`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_collect_taobao`
-- ----------------------------
DROP TABLE IF EXISTS `vg_collect_taobao`;
CREATE TABLE `vg_collect_taobao` (
  `cate_id` smallint(4) NOT NULL,
  `collect_time` int(10) NOT NULL DEFAULT '0',
  UNIQUE KEY `cate_id` (`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `vg_exchange_goods`;
CREATE TABLE `vg_exchange_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `goods_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:虚拟卡 2:实体商品',
  `img` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `integral` int(11) NOT NULL DEFAULT '0',
  `stock` int(11) NOT NULL DEFAULT '0',
  `buy_count` int(11) NOT NULL DEFAULT '0',
  `user_id` smallint(5) NOT NULL DEFAULT '1',
  `user_num` int(8) NOT NULL COMMENT '每人限兑',
  `is_best` tinyint(1) NOT NULL DEFAULT '0',
  `is_hot` tinyint(1) NOT NULL DEFAULT '0',
  `begin_time` varchar(40) NOT NULL,
  `end_time` varchar(40) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `seo_title` varchar(400) NOT NULL,
  `seo_keys` varchar(400) NOT NULL,
  `seo_desc` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `begin_end_time` (`begin_time`,`end_time`),
  KEY `status` (`status`),
  KEY `sort` (`sort`),
  KEY `is_best` (`is_best`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_exchange_order`
-- ----------------------------
DROP TABLE IF EXISTS `vg_exchange_order`;
CREATE TABLE `vg_exchange_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sn` varchar(255) NOT NULL,
  `data_name` varchar(255) NOT NULL DEFAULT '',
  `goods_status` tinyint(1) NOT NULL COMMENT '0:未发货;1:部分发货;2:全部发货;3:部分退货;4:全部退货'',',
  `data_num` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL COMMENT '订单状态\r\n0: 未确认\r\n1: 完成\r\n2: 作废',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `region_lv1` int(11) NOT NULL,
  `region_lv2` int(11) NOT NULL,
  `region_lv3` int(11) NOT NULL,
  `region_lv4` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile_phone` varchar(255) NOT NULL,
  `fax_phone` varchar(255) NOT NULL,
  `fix_phone` varchar(255) NOT NULL,
  `alim` varchar(255) NOT NULL,
  `msn` varchar(255) NOT NULL,
  `qq` varchar(255) NOT NULL,
  `consignee` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `user_name` varchar(60) NOT NULL DEFAULT '',
  `remark` varchar(255) NOT NULL,
  `adm_memo` varchar(255) NOT NULL,
  `order_score` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sn` (`sn`),
  KEY `uid` (`uid`),
  KEY `goods_status` (`goods_status`),
  KEY `status` (`status`),
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_find_password_log`
-- ----------------------------
DROP TABLE IF EXISTS `vg_find_password_log`;
CREATE TABLE `vg_find_password_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `md5` char(32) NOT NULL,
  `create_time` varchar(20) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `uid` int(11) NOT NULL,
  `status` int(1) NOT NULL COMMENT '0表示没有激活，1表示激活',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_flink`
-- ----------------------------
DROP TABLE IF EXISTS `vg_flink`;
CREATE TABLE `vg_flink` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` smallint(4) NOT NULL DEFAULT '1',
  `img` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `ordid` smallint(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_flink_cate`
-- ----------------------------
DROP TABLE IF EXISTS `vg_flink_cate`;
CREATE TABLE `vg_flink_cate` (
  `id` smallint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_focus`
-- ----------------------------
DROP TABLE IF EXISTS `vg_focus`;
CREATE TABLE `vg_focus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `abst` text NOT NULL,
  `clicks` int(10) NOT NULL DEFAULT '0',
  `ordid` smallint(4) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`) ,
  KEY `ordid` (`ordid`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_focus_cate`
-- ----------------------------
DROP TABLE IF EXISTS `vg_focus_cate`;
CREATE TABLE `vg_focus_cate` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `width` smallint(6) NOT NULL DEFAULT '0',
  `height` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_group`
-- ----------------------------
DROP TABLE IF EXISTS `vg_group`;
CREATE TABLE `vg_group` (
  `id` smallint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `title` varchar(50) NOT NULL,
  `create_time` int(11) unsigned NOT NULL,
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sort` smallint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `status` (`status`) ,
  KEY `sort` (`sort`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_items`
-- ----------------------------
DROP TABLE IF EXISTS `vg_items`;
CREATE TABLE `vg_items` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cid` smallint(4) DEFAULT NULL,
  `item_key` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `simg` varchar(255) DEFAULT NULL,
  `bimg` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `url` varchar(1000) DEFAULT NULL,
  `sid` smallint(4) NOT NULL DEFAULT '0',
  `hits` int(10) NOT NULL DEFAULT '0',
  `likes` int(10) NOT NULL DEFAULT '0' COMMENT '喜欢数',
  `browse_num` int(10) NOT NULL,
  `haves` int(10) NOT NULL DEFAULT '0' COMMENT '库存数',
  `comments` int(10) NOT NULL DEFAULT '0' COMMENT '评论数',
  `comments_last` text COMMENT '最近的N条评论',
  `is_index` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `add_time` int(10) NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL DEFAULT '0',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_keys` varchar(255) DEFAULT NULL,
  `sort_order` int(10) DEFAULT '0',
  `seo_desc` text,
  `cash_back_rate` varchar(40) NOT NULL,
  `seller_name` varchar(100) NOT NULL,
  `remark` varchar(50) DEFAULT NULL,
  `remark_status` tinyint(6) DEFAULT '1',
  `is_collect_comments` int(1) DEFAULT '0' COMMENT '是否采集了淘宝评论1表示已经采集了淘宝评论',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `is_index` (`is_index`),
  KEY `title` (`title`),
  KEY `index_sid` (`sid`),
  KEY `uid` (`uid`),
  KEY `item_key` (`item_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_items_cate`
-- ----------------------------
DROP TABLE IF EXISTS `vg_items_cate`;
CREATE TABLE `vg_items_cate` (
  `id` smallint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `keywords` varchar(128) NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `pid` smallint(4) NOT NULL DEFAULT '0',
  `item_nums` int(10) NOT NULL DEFAULT '0',
  `item_likes` int(11) NOT NULL,
  `ordid` smallint(4) NOT NULL DEFAULT '0',
  `tags` varchar(50) NOT NULL,
  `is_hots` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `recommend` int(1) NOT NULL DEFAULT '0' COMMENT '0表示不推荐，1表示推荐',
  `import_status` int(1) NOT NULL DEFAULT '1',
  `seo_title` varchar(255) NOT NULL,
  `seo_keys` varchar(255) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `seo_desc` text NOT NULL,
  `matching_title` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_is_hots` (`is_hots`),
  KEY `ordid` (`ordid`),
  KEY `index_pid` (`pid`,`recommend`,`status`) ,
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_items_comments`
-- ----------------------------
DROP TABLE IF EXISTS `vg_items_comments`;
CREATE TABLE `vg_items_comments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `items_id` int(11) NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL,
  `uname` varchar(50) DEFAULT NULL,
  `info` text NOT NULL,
  `add_time` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `vg_items_site`
-- ----------------------------
DROP TABLE IF EXISTS `vg_items_site`;
CREATE TABLE `vg_items_site` (
  `id` smallint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `site_domain` varchar(255) NOT NULL,
  `site_logo` varchar(255) NOT NULL,
  `collect_url` varchar(255) NOT NULL,
  `collect_time` int(10) NOT NULL DEFAULT '0',
  `item_nums` int(10) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_items_tags`
-- ----------------------------
DROP TABLE IF EXISTS `vg_items_tags`;
CREATE TABLE `vg_items_tags` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `item_nums` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `seo_title` varchar(255) NOT NULL,
  `seo_keys` varchar(255) NOT NULL,
  `seo_desc` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_items_tags_cate`
-- ----------------------------
DROP TABLE IF EXISTS `vg_items_tags_cate`;
CREATE TABLE `vg_items_tags_cate` (
  `cate_id` smallint(4) NOT NULL,
  `tag_id` int(10) NOT NULL,
  KEY `cate_id` (`cate_id`) ,
  KEY `tag_id` (`tag_id`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_items_tags_item`
-- ----------------------------
DROP TABLE IF EXISTS `vg_items_tags_item`;
CREATE TABLE `vg_items_tags_item` (
  `item_id` int(10) NOT NULL,
  `tag_id` int(10) NOT NULL,
  KEY `item_id` (`item_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_items_user`
-- ----------------------------
DROP TABLE IF EXISTS `vg_items_user`;
CREATE TABLE `vg_items_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iid` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `add_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`iid`),
  KEY `uid` (`uid`),
  KEY `item_id_2` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_like_list`
-- ----------------------------
DROP TABLE IF EXISTS `vg_like_list`;
CREATE TABLE `vg_like_list` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `items_id` int(10) NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL DEFAULT '0',
  `add_time` bigint(12) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `add_time` (`add_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_miao_order`
-- ----------------------------
DROP TABLE IF EXISTS `vg_miao_order`;
CREATE TABLE `vg_miao_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_time` varchar(20) DEFAULT NULL,
  `seller_name` varchar(20) DEFAULT NULL,
  `uid` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `order_code` varchar(50) DEFAULT NULL,
  `item_count` int(5) DEFAULT NULL,
  `item_price` varchar(10) DEFAULT NULL,
  `sales` varchar(20) DEFAULT NULL,
  `commission` varchar(10) DEFAULT NULL,
  `cash_back` varchar(10) DEFAULT NULL,
  `status` varchar(20) NOT NULL COMMENT '//订单状态',
  `is_update` int(1) NOT NULL DEFAULT '0' COMMENT '0表示未更新用户表，以及返现表，1表示已经更新，不需要再次更新',
  `jiesuan_data` datetime NOT NULL COMMENT '结算日期',
  `order_id` varchar(20) NOT NULL,
  `cash_back_jifenbao` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `order_code` (`order_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_nav`
-- ----------------------------
DROP TABLE IF EXISTS `vg_nav`;
CREATE TABLE `vg_nav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(50) NOT NULL,
  `url` varchar(255) NOT NULL,
  `sort_order` smallint(4) NOT NULL,
  `system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-系统 0-自定义',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '导航位置1-顶部 2-底部',
  `in_site` tinyint(1) NOT NULL COMMENT '1-本站内 0-站外',
  `is_show` tinyint(1) NOT NULL DEFAULT '1',
  `seo_title` varchar(255) NOT NULL,
  `seo_keys` text NOT NULL,
  `seo_desc` text NOT NULL,
  `items_cate_id` int(11) DEFAULT NULL COMMENT '//分类id',
  PRIMARY KEY (`id`),
  KEY `is_show` (`is_show`),
  KEY `type` (`type`),
  KEY `sort_order` (`sort_order`),
  KEY `alias` (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_node`
-- ----------------------------
DROP TABLE IF EXISTS `vg_node`;
CREATE TABLE `vg_node` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(255) NOT NULL,
  `module_name` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `action_name` varchar(50) DEFAULT NULL,
  `data` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `sort` smallint(6) unsigned NOT NULL DEFAULT '0',
  `auth_type` tinyint(1) NOT NULL DEFAULT '0',
  `group_id` tinyint(3) unsigned DEFAULT '0',
  `often` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-不常用 1-常用',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-不常用 1-常用',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `module` (`module`),
  KEY `auth_type` (`auth_type`),
  KEY `is_show` (`is_show`),
  KEY `group_id` (`group_id`),
  KEY `sort` (`sort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `vg_role`
-- ----------------------------
DROP TABLE IF EXISTS `vg_role`;
CREATE TABLE `vg_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `status` tinyint(1) unsigned DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `create_time` int(11) unsigned NOT NULL,
  `update_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_seller_cate`
-- ----------------------------
DROP TABLE IF EXISTS `vg_seller_cate`;
CREATE TABLE `vg_seller_cate` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `cid` int(8) NOT NULL,
  `name` varchar(200) NOT NULL,
  `count` int(8) NOT NULL,
  `seller_status` int(1) NOT NULL DEFAULT '1',
  `status` int(1) NOT NULL,
  `sort` int(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`) ,
  KEY `index_status` (`status`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_seller_list`
-- ----------------------------
DROP TABLE IF EXISTS `vg_seller_list`;
CREATE TABLE `vg_seller_list` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `sid` int(8) NOT NULL,
  `cate_id` int(8) NOT NULL,
  `name` varchar(200) NOT NULL,
  `site_logo` varchar(200) DEFAULT NULL,
  `net_logo` varchar(200) NOT NULL,
  `recommend` int(1) NOT NULL,
  `click_url` varchar(400) NOT NULL,
  `sort` int(6) NOT NULL,
  `description` varchar(200) NOT NULL,
  `freeshipment` int(1) NOT NULL,
  `installment` int(1) NOT NULL,
  `has_invoice` int(1) NOT NULL,
  `cash_back_rate` varchar(64) NOT NULL,
  `status` int(1) NOT NULL,
  `update_time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_status` (`status`) ,
  KEY `index_recommend` (`recommend`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_seller_list_cate`
-- ----------------------------
DROP TABLE IF EXISTS `vg_seller_list_cate`;
CREATE TABLE `vg_seller_list_cate` (
  `list_id` int(11) NOT NULL,
  `cate_id` int(11) NOT NULL,
  KEY `list_id` (`list_id`) ,
  KEY `cate_id` (`cate_id`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_seller_list_goods`
-- ----------------------------
DROP TABLE IF EXISTS `vg_seller_list_goods`;
CREATE TABLE `vg_seller_list_goods` (
  `id` int(11) NOT NULL,
  `seller_list_id` int(11) NOT NULL,
  `seller_cate_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `seller_name` varchar(100) NOT NULL,
  `pic_url` varchar(400) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `desc` tinytext NOT NULL,
  `click_url` varchar(400) NOT NULL,
  `seller_url` varchar(400) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `seller_list_id` (`seller_list_id`) ,
  KEY `seller_cate_id` (`seller_cate_id`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_seo`
-- ----------------------------
DROP TABLE IF EXISTS `vg_seo`;
CREATE TABLE `vg_seo` (
  `description` text,
  `keywords` text,
  `title` varchar(250) DEFAULT NULL,
  `actionname` varchar(30) DEFAULT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `actionname` (`actionname`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_setting`
-- ----------------------------
DROP TABLE IF EXISTS `vg_setting`;
CREATE TABLE `vg_setting` (
  `name` varchar(100) NOT NULL,
  `data` text NOT NULL,
  KEY `name` (`name`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_ucenter`
-- ----------------------------
DROP TABLE IF EXISTS `vg_ucenter`;
CREATE TABLE `vg_ucenter` (
  `name` varchar(255) DEFAULT NULL,
  `value` varchar(10000) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_user`
-- ----------------------------
DROP TABLE IF EXISTS `vg_user`;
CREATE TABLE `vg_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '0',
  `passwd` varchar(50) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `add_time` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `last_time` int(11) NOT NULL DEFAULT '0',
  `last_ip` varchar(15) DEFAULT '0',
  `is_majia` int(1) DEFAULT '0' COMMENT '0表示普通用户 1表示马甲',
  `login_count` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `add_time` (`add_time`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_user_comments`
-- ----------------------------
DROP TABLE IF EXISTS `vg_user_comments`;
CREATE TABLE `vg_user_comments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT '0',
  `uname` varchar(100) NOT NULL,
  `pid` int(10) NOT NULL DEFAULT '0',
  `info` text,
  `type` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `add_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `index_pid` (`pid`) ,
  KEY `index_status` (`status`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_user_consignee`
-- ----------------------------
DROP TABLE IF EXISTS `vg_user_consignee`;
CREATE TABLE `vg_user_consignee` (
  `uid` int(11) NOT NULL,
  `region_lv1` int(11) NOT NULL DEFAULT '0',
  `region_lv2` int(11) NOT NULL DEFAULT '0',
  `region_lv3` int(11) NOT NULL DEFAULT '0',
  `region_lv4` int(11) NOT NULL DEFAULT '0',
  `address` varchar(255) NOT NULL DEFAULT '',
  `mobile_phone` varchar(255) NOT NULL DEFAULT '',
  `fix_phone` varchar(255) NOT NULL DEFAULT '',
  `consignee` varchar(255) NOT NULL DEFAULT '',
  `zip` varchar(255) NOT NULL DEFAULT '',
  `qq` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `fax_phone` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_user_follow`
-- ----------------------------
DROP TABLE IF EXISTS `vg_user_follow`;
CREATE TABLE `vg_user_follow` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `fans_id` int(10) NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL DEFAULT '0',
  `add_time` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fans_id` (`fans_id`,`uid`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_user_history`
-- ----------------------------
DROP TABLE IF EXISTS `vg_user_history`;
CREATE TABLE `vg_user_history` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT '0',
  `uname` varchar(100) NOT NULL,
  `add_time` int(10) NOT NULL DEFAULT '0',
  `info` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_user_info`
-- ----------------------------
DROP TABLE IF EXISTS `vg_user_info`;
CREATE TABLE `vg_user_info` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `sex` tinyint(4) NOT NULL DEFAULT '0' COMMENT '//0表示男，1表示女，2表示未知',
  `brithday` varchar(50) NOT NULL DEFAULT '1985|1|1',
  `address` varchar(50) NOT NULL DEFAULT '请选择|请选择',
  `blog` varchar(200) NOT NULL DEFAULT 'http://',
  `info` varchar(500) NOT NULL DEFAULT '自我介绍',
  `share_num` int(11) DEFAULT '0',
  `like_num` int(11) DEFAULT '0',
  `follow_num` int(10) DEFAULT '0',
  `fans_num` int(10) DEFAULT '0',
  `album_num` int(10) DEFAULT '0',
  `exchange_num` int(8) NOT NULL DEFAULT '0',
  `integral` int(10) DEFAULT '0',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '用户资金',
  `jifenbao` decimal(10,0) DEFAULT '0',
  `constellation` tinyint(4) NOT NULL DEFAULT '0' COMMENT '星座',
  `job` tinyint(4) NOT NULL DEFAULT '0' COMMENT '职业',
  `qq` varchar(20) DEFAULT NULL,
  `realname` varchar(64) DEFAULT NULL,
  `alipay` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `integral` (`integral`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_user_msg`
-- ----------------------------
DROP TABLE IF EXISTS `vg_user_msg`;
CREATE TABLE `vg_user_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_user` varchar(40) NOT NULL,
  `from_user` varchar(40) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `del` tinyint(1) NOT NULL,
  `date` int(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_user_openid`
-- ----------------------------
DROP TABLE IF EXISTS `vg_user_openid`;
CREATE TABLE `vg_user_openid` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `uname` varchar(100) NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '0',
  `info` text,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`) ,
  KEY `openid` (`openid`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_user_setmsg`
-- ----------------------------
DROP TABLE IF EXISTS `vg_user_setmsg`;
CREATE TABLE `vg_user_setmsg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(20) NOT NULL,
  `val` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vg_user_tixian`
-- ----------------------------
DROP TABLE IF EXISTS `vg_user_tixian`;
CREATE TABLE `vg_user_tixian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `uname` varchar(100) NOT NULL,
  `money` decimal(10,2) DEFAULT NULL,
  `jifenbao` decimal(10,0) DEFAULT '0',
  `remark` varchar(1000) NOT NULL,
  `addtime` varchar(40) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `reply` varchar(1000) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '//0表示没正在处理，1表示已经审核，2表示退回',
  `realname` varchar(64) DEFAULT NULL,
  `alipay` varchar(255) DEFAULT NULL,
  `is_money` int(1) DEFAULT '1' COMMENT '1表示钱2表示集分宝',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `vg_word`
-- ----------------------------
DROP TABLE IF EXISTS `vg_word`;
CREATE TABLE `vg_word` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `cid` smallint(6) NOT NULL DEFAULT '0',
  `word` varchar(255) NOT NULL DEFAULT '',
  `replacement` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(2) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `sort` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`) ,
  KEY `cid` (`cid`) ,
  KEY `word` (`word`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `vg_word_cate`
-- ----------------------------
DROP TABLE IF EXISTS `vg_word_cate`;
CREATE TABLE `vg_word_cate` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `name` (`name`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `vg_wegoapi`;
CREATE TABLE `vg_wegoapi` (
  `name` varchar(100) NOT NULL,
  `data` text NOT NULL,
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

