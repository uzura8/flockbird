CREATE TABLE `album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `body` text NULL,
  `public_flag` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at_idx` (`created_at`),
  KEY `member_id_created_at_idx` (`member_id`,`created_at`),
  KEY `public_flag_craeted_at_idx` (`public_flag`,`created_at`),
  KEY `member_id_idx` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `album_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `image` varchar(100) NOT NULL,
  `name` text NULL,
  `shot_at` datetime NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `album_id_created_at` (`album_id`,`created_at`),
  KEY `album_id_idx` (`album_id`),
  CONSTRAINT `album_image_album_id_album_id` FOREIGN KEY (`album_id`) REFERENCES `album` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `album_image_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_image_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `album_image_id_created_at` (`album_image_id`,`created_at`),
  KEY `album_image_id_idx` (`album_image_id`),
  CONSTRAINT `album_image_comment_album_image_id_album_image_id` FOREIGN KEY (`album_image_id`) REFERENCES `album_image` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
