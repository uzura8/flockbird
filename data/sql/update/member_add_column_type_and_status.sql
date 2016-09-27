ALTER TABLE `member` ADD COLUMN `group` int(3) NOT NULL DEFAULT 1;
ALTER TABLE `member` ADD COLUMN `status` tinyint(2) NOT NULL DEFAULT 0;
ALTER TABLE `member_profile_cache` ADD COLUMN `country` varchar(2) DEFAULT NULL;
ALTER TABLE `member_profile_cache` ADD COLUMN `country_public_flag` tinyint(2) NOT NULL DEFAULT 0;
