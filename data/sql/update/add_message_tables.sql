CREATE TABLE `member_relation_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `member_id_lower` int(11) NOT NULL COMMENT 'Lower member id',
  `member_id_upper` int(11) NOT NULL COMMENT 'Upper member id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_lower_upper_UNIQUE_idx` (`member_id_lower`,`member_id_upper`),
  KEY `member_id_upper_idx` (`member_id_upper`),
  CONSTRAINT `member_relation_unit_member_id_lower_member_id` FOREIGN KEY (`member_id_lower`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `member_relation_unit_member_id_upper_member_id` FOREIGN KEY (`member_id_upper`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Member one-to-one unit';

CREATE TABLE `group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `name` text COLLATE utf8_unicode_ci NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:use for system only, 1:normal',
  `public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `group_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `group_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `role_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:normal, 1:admin',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id_member_id_UNIQUE_idx` (`group_id`,`member_id`),
  KEY `member_id_idx` (`member_id`),
  CONSTRAINT `group_member_group_id_group_id` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_member_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `member_id` int(11) NOT NULL COMMENT 'Sent member id',
  `subject` text COLLATE utf8_unicode_ci NULL,
  `body` text COLLATE utf8_unicode_ci NULL,
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1:one to one relation message, 2:group massage, 8:site information, 9:system information',
  `foreign_table` varchar(20) NULL COMMENT 'Reference table name',
  `foreign_id` int(11) NULL COMMENT 'The id of reference table',
  `is_sent` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:unsent, 1:sent',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:undeleted, 1:deleted',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `sent_at` datetime NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `member_id_sent_at_idx` (`member_id`,`sent_at`),
  KEY `member_id_updated_at_idx` (`member_id`,`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Use for message master';

CREATE TABLE `message_sent_member_relation_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `member_relation_unit_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_id_member_relation_unit_id_UNIQUE_idx` (`message_id`,`member_relation_unit_id`),
  KEY `member_relation_unit_id_id_idx` (`member_relation_unit_id`,`id`),
  CONSTRAINT `mrmru_mru_id_member_relation_unit_id` FOREIGN KEY (`member_relation_unit_id`) REFERENCES `member_relation_unit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mrmru_mru_message_id_message_id` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Use for sent message list for one to one members';

CREATE TABLE `message_sent_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `group_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_id_group_id_UNIQUE_idx` (`message_id`,`group_id`),
  KEY `group_id_id_idx` (`group_id`,`id`),
  CONSTRAINT `message_group_group_group_id_group_id` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_group_group_message_id_message_id` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Use for sent message list for groups';

CREATE TABLE `message_sent_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `message_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `admin_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_id_member_id_UNIQUE_idx` (`message_id`,`member_id`),
  KEY `member_id_idx` (`member_id`),
  CONSTRAINT `message_sent_admin_message_id_message_id` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_sent_admin_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Use for sent message list from admin_user';

CREATE TABLE `message_recieved` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `member_id` int(11) NOT NULL COMMENT 'Recieved member id',
  `message_id` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:unread, 1:already read',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:undeleted, 1:deleted',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_message_id_UNIQUE_idx` (`member_id`,`message_id`),
  KEY `member_id_id_idx` (`member_id`,`id`),
  KEY `message_id_idx` (`message_id`),
  CONSTRAINT `message_recieved_message_id_message_id` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_recieved_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Use for recieved message list for member';

CREATE TABLE `message_recieved_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `member_id` int(11) NOT NULL COMMENT 'Recieved member id',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1:one to one relation message, 2:group massage, 8:info message from admin, 9:info message from system',
  `type_related_id` int(11) NOT NULL COMMENT 'The id of reference table. Set id based on type value',
  `last_message_id` int(11) NOT NULL COMMENT 'Last message id for each message type',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:unread, 1:already read',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `last_sent_at` datetime NOT NULL COMMENT 'Last message sent time for each message type',
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_type_type_related_id_UNIQUE_idx` (`member_id`,`type`,`type_related_id`),
  KEY `member_id_last_sent_at_idx` (`member_id`,`last_sent_at`),
  CONSTRAINT `message_recieved_summary_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Use for recieved message list integrated member_relation_unit and group';

CREATE TABLE `message_recieved_mail_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_recieved_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '0:unexecuted, 1:successed and Greater than these are errors',
  `result_message` text NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status_id_idx` (`status`,`id`),
  KEY `message_recieved_id_idx` (`message_recieved_id`,`id`),
  KEY `member_id_idx` (`member_id`),
  CONSTRAINT `mrmq_message_recieved_id_message_recieved_id` FOREIGN KEY (`message_recieved_id`) REFERENCES `message_recieved` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_recieved_mail_queue_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
