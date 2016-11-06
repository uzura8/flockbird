ALTER TABLE notice MODIFY `foreign_id` varchar(10) NOT NULL COMMENT 'The id of reference table';
ALTER TABLE member_watch_content MODIFY `foreign_id` varchar(10) NOT NULL COMMENT 'The id of reference table';
