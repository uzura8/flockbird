ALTER TABLE `member` DROP INDEX `birthday_id`;
ALTER TABLE `member` CHANGE COLUMN `birthday` `birthdate` varchar(5) DEFAULT NULL;
ALTER TABLE `member` CHANGE COLUMN `birthday_public_flag` `birthdate_public_flag` tinyint(2) NOT NULL DEFAULT 0;
ALTER TABLE `member` ADD INDEX `birthdate_id` (`birthdate`,`id`);
