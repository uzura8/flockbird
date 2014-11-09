CREATE TABLE `file_bin` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'File path and name',
  `bin` longblob COMMENT 'Content of file',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Saves content of files';
