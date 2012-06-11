CREATE TABLE `note_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `note_id_created_at` (`note_id`,`created_at`),
  KEY `note_id_idx` (`note_id`),
  KEY `member_id_idx` (`member_id`),
  CONSTRAINT `note_comment_note_id_note_id` FOREIGN KEY (`note_id`) REFERENCES `note` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
