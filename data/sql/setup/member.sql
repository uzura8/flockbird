Create Table: CREATE TABLE `member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `last_login` datetime NOT NULL,
  `login_hash` varchar(255) NOT NULL DEFAULT '',
  `register_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: normal, 1:facebook',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
