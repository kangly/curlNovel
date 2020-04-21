DROP TABLE IF EXISTS `novel`;

CREATE TABLE `novel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '小说表id',
  `type_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类别id',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '小说名称',
  `author` varchar(50) NOT NULL DEFAULT '' COMMENT '小说作者',
  `summary` varchar(500) NOT NULL DEFAULT '' COMMENT '小说简介',
  `source` varchar(50) NOT NULL DEFAULT '' COMMENT '小说来源',
  `source_link` varchar(100) NOT NULL DEFAULT '' COMMENT '小说来源链接',
  `is_publish` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否发布',
  `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否首页推荐',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '创建人姓名',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `novel_type`;

CREATE TABLE `novel_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '小说类别表id',
  `pid` int(10) unsigned NOT NULL COMMENT '父级类别id',
  `title` varchar(50) NOT NULL COMMENT '类别标题',
  `intro` varchar(100) NOT NULL COMMENT '类别介绍',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人id',
  `username` varchar(50) DEFAULT NULL COMMENT '创建人姓名',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `novel_chapter`;

CREATE TABLE `novel_chapter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '小说章节表id',
  `novel_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '小说表id',
  `source_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '小说源章节标识',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '小说章节名称',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `novel_id` (`novel_id`,`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `novel_content`;

CREATE TABLE `novel_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '小说章节内容表id',
  `chapter_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '小说章节表id',
  `content` text COMMENT '小说章节内容',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;