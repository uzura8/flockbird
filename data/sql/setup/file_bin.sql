CREATE TABLE `file_bin` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'File name',
  `bin` longblob COMMENT 'Content of file',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Saves content of files';
