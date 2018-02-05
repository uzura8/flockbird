ALTER TABLE `member` ADD COLUMN `country` varchar(2) DEFAULT NULL COMMENT 'Save format by  ISO 3166-1 alpha-2';
ALTER TABLE `member` ADD COLUMN `country_public_flag` tinyint(2) NOT NULL DEFAULT 0;
ALTER TABLE `member_profile_cache` ADD COLUMN `country` varchar(2) DEFAULT NULL;
ALTER TABLE `member_profile_cache` ADD COLUMN `country_public_flag` tinyint(2) NOT NULL DEFAULT 0;

CREATE TABLE `member_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name_phonetic` varchar(50) COLLATE utf8_unicode_ci NULL,
  `first_name_phonetic` varchar(50) COLLATE utf8_unicode_ci NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci NULL,
  `country` varchar(2) DEFAULT NULL COMMENT 'Save format by  ISO 3166-1 alpha-2',
  `postal_code` varchar(20) NOT NULL,
  `region` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address01` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address02` text COLLATE utf8_unicode_ci NULL,
  `phone01` varchar(20) NOT NULL,
  `phone02` varchar(20) NULL,
  `description` text COLLATE utf8_unicode_ci NULL,
  `type` tinyint(2) NOT NULL DEFAULT 0 COMMENT '0:optional, 1:main',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id_type_idx` (`member_id`, `type`),
  CONSTRAINT `member_address_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `member_pre` ADD COLUMN `group` int(3) NOT NULL DEFAULT 1;

ALTER TABLE `album` CHANGE COLUMN `foreign_table` `foreign_table` varchar(64) NULL;
ALTER TABLE `notice` CHANGE COLUMN `foreign_table` `foreign_table` varchar(64) NOT NULL COMMENT 'Reference table name';
ALTER TABLE `notice` CHANGE COLUMN `foreign_id` `foreign_id` varchar(10) NOT NULL COMMENT 'The id of reference table';
ALTER TABLE `notice` CHANGE COLUMN `parent_table` `parent_table`  varchar(64) NULL COMMENT 'Use for open page url.';
ALTER TABLE `notice` CHANGE COLUMN `parent_id` `parent_id` varchar(10) NULL COMMENT 'Use for open page url.';
ALTER TABLE `member_watch_content` CHANGE COLUMN `foreign_table` `foreign_table` varchar(64) NOT NULL COMMENT 'Reference table name';
ALTER TABLE `member_watch_content` CHANGE COLUMN `foreign_id` `foreign_id` varchar(10) NOT NULL COMMENT 'The id of reference table';
ALTER TABLE `timeline` CHANGE COLUMN `foreign_table` `foreign_table` varchar(64) NULL COMMENT 'Reference table name';
ALTER TABLE `timeline` CHANGE COLUMN `foreign_id` `foreign_id` varchar(10) NULL COMMENT 'The id of reference table';

ALTER TABLE `template` ADD COLUMN `lang` varchar(5) NOT NULL;
ALTER TABLE `template` CHANGE COLUMN `title` `title` varchar(255) COLLATE utf8_unicode_ci NULL;
ALTER TABLE `template` CHANGE COLUMN `body` `body` text COLLATE utf8_unicode_ci NULL;
ALTER TABLE `template` DROP INDEX `name_UNIQUE_idx`;
ALTER TABLE `template` ADD INDEX `template_id_lang_UNIQUE_idx` (`name`, `lang`);

ALTER TABLE `admin_user` CHANGE COLUMN `last_login` `last_login` datetime NULL;
