CREATE TABLE `member_profile_cache` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sex` varchar(16) DEFAULT NULL,
  `sex_public_flag` tinyint(2) NOT NULL DEFAULT 0,
  `birthyear` int(4) DEFAULT NULL,
  `birthyear_public_flag` tinyint(2) NOT NULL DEFAULT 0,
  `birthdate` varchar(5) DEFAULT NULL,
  `birthdate_public_flag` tinyint(2) NOT NULL DEFAULT 0,
  `birthday` date DEFAULT NULL,
  `birthday_public_flag` tinyint(2) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`member_id`),
  UNIQUE KEY `name_UNIQUE_idx` (`name`),
  CONSTRAINT `mpc_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
