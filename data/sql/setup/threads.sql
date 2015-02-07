CREATE TABLE `thread` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `body` text NOT NULL,
  `public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `member_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL DEFAULT '0',
  `comment_count` int(11) NOT NULL DEFAULT '0',
  `like_count` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `sort_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `public_flag_srot_datetime_category_id_idx` (`public_flag`,`sort_datetime`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `thread_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_thread_id_UNIQUE_idx` (`member_id`,`thread_id`),
  KEY `thread_id_id_idx` (`thread_id`,`id`),
  CONSTRAINT `thread_like_thread_id_thread_id` FOREIGN KEY (`thread_id`) REFERENCES `thread` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `thread_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `like_count` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `thread_id_id_idx` (`thread_id`,`id`),
  KEY `member_id_idx` (`member_id`),
  CONSTRAINT `thread_comment_thread_id_thread_id` FOREIGN KEY (`thread_id`) REFERENCES `thread` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `thread_comment_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread_comment_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_thread_comment_id_UNIQUE_idx` (`member_id`,`thread_comment_id`),
  KEY `thread_comment_id_id_idx` (`thread_comment_id`,`id`),
  CONSTRAINT `thread_comment_like_thread_comment_id_thread_comment_id` FOREIGN KEY (`thread_comment_id`) REFERENCES `thread_comment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `thread_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `name` text NULL,
  `shot_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `thread_id_created_at` (`thread_id`,`created_at`),
  KEY `thread_id_idx` (`thread_id`),
  KEY `file_name_idx` (`file_name`),
  CONSTRAINT `thread_image_thread_id_thread_id` FOREIGN KEY (`thread_id`) REFERENCES `thread` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
