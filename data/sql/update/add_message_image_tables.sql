CREATE TABLE `message_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(128) NOT NULL,
  `name` text COLLATE utf8_unicode_ci NULL,
  `shot_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `message_id_created_at` (`message_id`,`created_at`),
  KEY `message_id_idx` (`message_id`),
  KEY `file_name_idx` (`file_name`),
  CONSTRAINT `mi_message_id_message_id` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
