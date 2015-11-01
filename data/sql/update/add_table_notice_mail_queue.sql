CREATE TABLE `notice_mail_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notice_status_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `notice_status_id_idx` (`notice_status_id`),
  KEY `member_id_created_at_idx` (`member_id`,`created_at`),
  CONSTRAINT `notice_mail_queue_notice_status_id` FOREIGN KEY (`notice_status_id`) REFERENCES `notice_status` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
