CREATE TABLE `fbusers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `facebook_id` varchar(50) NOT NULL,
  `facebook_name` varchar(255) NOT NULL,
  `facebook_link` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facebook_id_UNIQUE_idx` (`facebook_id`),
  UNIQUE KEY `member_id_UNIQUE_idx` (`member_id`),
  CONSTRAINT `fbusers_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
