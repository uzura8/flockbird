CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `member_id` int(11) NOT NULL,
  `subject` text COLLATE utf8_unicode_ci NULL,
  `body` text COLLATE utf8_unicode_ci NULL,
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0: normal, 1:admin_member_message',
  `foreign_table` varchar(20) NULL COMMENT 'Reference table name',
  `foreign_id` int(11) NULL COMMENT 'The id of reference table',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: undeleted, 1:deleted',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id_created_at_idx` (`member_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `message_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `name` text COLLATE utf8_unicode_ci NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `message_sent` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `member_id_to` int(11) NOT NULL COMMENT 'Target member id',
  `member_id_from` int(11) NOT NULL COMMENT 'Subject member id',
  `message_id` int(11) NOT NULL,
  `message_group_id` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: unread, 1:already read',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: undeleted, 1:deleted',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_from_to_message_id_UNIQUE_idx` (`member_id_from`,`member_id_to`,`message_id`),
  KEY `member_id_to_created_at_idx` (`member_id_to`,`created_at`),
  KEY `message_id_idx` (`message_id`),
  KEY `message_group_id_idx` (`message_group_id`),
  CONSTRAINT `message_sent_message_id_message_id` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_sent_message_group_id_message_group_id` FOREIGN KEY (`message_group_id`) REFERENCES `message_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `message_group_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `message_group_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_group_id_member_id_UNIQUE_idx` (`message_group_id`,`member_id`),
  KEY `member_id_created_at_idx` (`member_id`,`created_at`),
  CONSTRAINT `message_group_member_message_group_id_message_group_id` FOREIGN KEY (`message_group_id`) REFERENCES `message_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_group_member_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `message_group_sent` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `message_group_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_group_id_message_id_UNIQUE_idx` (`message_group_id`,`message_id`),
  KEY `message_group_id_created_at_idx` (`message_group_id`,`created_at`),
  KEY `message_group_id_idx` (`message_group_id`),
  KEY `message_id_idx` (`message_id`),
  CONSTRAINT `message_group_sent_message_group_id_message_group_id` FOREIGN KEY (`message_group_id`) REFERENCES `message_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_group_sent_message_id_message_id` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `message_sent_mail_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_sent_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '0:unexecuted, 1:successed and Greater than these are errors',
  `result_message` text NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `message_sent_id_id_idx` (`message_sent_id`,`id`),
  KEY `member_id_idx` (`member_id`),
  CONSTRAINT `message_sent_mail_queue_message_sent_id_message_sent_id` FOREIGN KEY (`message_sent_id`) REFERENCES `message_sent` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_sent_mail_queue_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
