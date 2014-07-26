DROP TABLE IF EXISTS `timeline_cache`;
DROP TABLE IF EXISTS `member_follow_timeline`;
DROP TABLE IF EXISTS `timeline_comment`;
DROP TABLE IF EXISTS `timeline_child_data`;
DROP TABLE IF EXISTS `timeline`;

CREATE TABLE `timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NULL,
  `member_id_to` int(11) NULL,
  `group_id` int(11) NULL,
  `page_id` int(11) NULL,
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `body` text NULL,
  `foreign_table` varchar(20) NULL COMMENT 'Reference table name',
  `foreign_id` int(11) NULL COMMENT 'The id of reference table',
  `source` varchar(64) NULL COMMENT 'The source caption',
  `source_uri` text NULL COMMENT 'The source URI',
  `public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `sort_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `timeline_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  KEY `foreign_table_foreign_id_type_created_at_idx` (`foreign_table`,`foreign_id`,`type`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `timeline_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timeline_id` int(11) NOT NULL,
  `member_id` int(11) NULL,
  `member_id_to` int(11) NULL,
  `group_id` int(11) NULL,
  `page_id` int(11) NULL,
  `is_follow` tinyint(1) NOT NULL DEFAULT '0',
  `public_flag` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `timeline_id_idx` (`timeline_id`),
  UNIQUE KEY `timeline_id_is_follow_UNIQUE_idx` (`timeline_id`,`is_follow`),
  CONSTRAINT `timeline_cache_timeline_id_timeline_id` FOREIGN KEY (`timeline_id`) REFERENCES `timeline` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `timeline_child_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timeline_id` int(11) NOT NULL,
  `foreign_table` varchar(20) NULL COMMENT 'Reference table name',
  `foreign_id` int(11) NULL COMMENT 'The id of reference table',
  PRIMARY KEY (`id`),
  KEY `timeline_id_idx` (`timeline_id`),
  KEY `foreign_table_foreign_id_timeline_id_idx` (`foreign_table`,`foreign_id`,`timeline_id`),
  CONSTRAINT `timeline_child_data_timeline_id_timeline_id` FOREIGN KEY (`timeline_id`) REFERENCES `timeline` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `timeline_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timeline_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `timeline_id_created_at` (`timeline_id`,`created_at`),
  CONSTRAINT `timeline_comment_timeline_id_timeline_id` FOREIGN KEY (`timeline_id`) REFERENCES `timeline` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `member_follow_timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `timeline_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_timeline_id_UNIQUE_idx` (`member_id`,`timeline_id`),
  CONSTRAINT `member_follow_timeline_member_id_timeline_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `member_follow_timeline_timeline_id_timeline_id` FOREIGN KEY (`timeline_id`) REFERENCES `timeline` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
