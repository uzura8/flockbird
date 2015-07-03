ALTER TABLE `file` ADD COLUMN `user_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: site member, 1:admin_user';
ALTER TABLE `file` DROP INDEX `member_id_idx`;
ALTER TABLE `file` ADD INDEX `user_type_member_id_idx` (`user_type`,`member_id`);

