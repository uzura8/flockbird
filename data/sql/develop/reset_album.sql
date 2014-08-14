DROP TABLE IF EXISTS `album_image_comment`;
DROP TABLE IF EXISTS `note_album_image`;
DROP TABLE IF EXISTS `album_image`;
DROP TABLE IF EXISTS `album`;

CREATE TABLE `album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `body` text NULL,
  `public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `cover_album_image_id` int(11) DEFAULT NULL,
  `foreign_table` varchar(20) NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at_idx` (`created_at`),
  KEY `member_id_created_at_idx` (`member_id`,`created_at`),
  KEY `public_flag_craeted_at_idx` (`public_flag`,`created_at`),
  KEY `member_id_foreign_table_idx` (`member_id`,`foreign_table`),
  KEY `member_id_idx` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `album_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `file_id` varchar(255) NOT NULL,
  `name` text NULL,
  `public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `comment_count` int(11) NOT NULL DEFAULT '0',
  `shot_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `sort_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `album_id_public_flag_id` (`album_id`,`public_flag`,`id`),
  KEY `album_id_public_flag_shot_at` (`album_id`,`public_flag`,`shot_at`),
  KEY `sort_datetime_idx` (`sort_datetime`),
  KEY `file_id_idx` (`file_id`),
  CONSTRAINT `album_image_album_id_album_id` FOREIGN KEY (`album_id`) REFERENCES `album` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `album_image_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_image_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `body` text NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `album_image_id_created_at` (`album_image_id`,`created_at`),
  KEY `album_image_id_idx` (`album_image_id`),
  CONSTRAINT `album_image_comment_album_image_id_album_image_id` FOREIGN KEY (`album_image_id`) REFERENCES `album_image` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `note_album_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `album_image_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `note_id_idx` (`note_id`),
  CONSTRAINT `note_album_image_note_id_note_id` FOREIGN KEY (`note_id`) REFERENCES `note` (`id`) ON DELETE CASCADE,
  CONSTRAINT `note_album_image_album_image_id_album_image_id` FOREIGN KEY (`album_image_id`) REFERENCES `album_image` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
