CREATE TABLE `note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `body` text NOT NULL,
  `public_flag` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at_idx` (`created_at`),
  KEY `member_id_created_at_idx` (`member_id`,`created_at`),
  KEY `public_flag_craeted_at_idx` (`public_flag`,`created_at`),
  KEY `member_id_idx` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
