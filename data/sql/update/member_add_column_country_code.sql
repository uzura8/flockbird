ALTER TABLE `member` ADD COLUMN `country` varchar(2) DEFAULT NULL COMMENT 'Save format by  ISO 3166-1 alpha-2';
ALTER TABLE `member` ADD COLUMN `country_public_flag` tinyint(2) NOT NULL DEFAULT 0;
