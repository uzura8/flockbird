ALTER TABLE `album` MODIFY `foreign_table` varchar(64) NOT NULL COMMENT 'Reference table name';
ALTER TABLE `notice` MODIFY `foreign_table` varchar(64) NOT NULL COMMENT 'Reference table name';
ALTER TABLE `member_watch_content` MODIFY `foreign_table` varchar(64) NOT NULL COMMENT 'Reference table name';

ALTER TABLE `timeline` MODIFY `foreign_table` varchar(64) NOT NULL COMMENT 'Reference table name';
ALTER TABLE `timeline` MODIFY `foreign_id` varchar(10) NOT NULL COMMENT 'The id of reference table';

ALTER TABLE `timeline_child_data` MODIFY `foreign_table` varchar(64) NOT NULL COMMENT 'Reference table name';
ALTER TABLE `timeline_child_data` MODIFY `foreign_id` varchar(10) NOT NULL COMMENT 'The id of reference table';

ALTER TABLE `message` MODIFY `foreign_table` varchar(64) NOT NULL COMMENT 'Reference table name';
ALTER TABLE `message` MODIFY `foreign_id` varchar(10) NOT NULL COMMENT 'The id of reference table';
