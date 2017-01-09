<?php
$action_members = \Notice\Site_View::get_action_members($members, $members_count, $lang);
$notice_message = \Notice\Site_View::convert_notice_message($foreign_table, $type, $action_members, $lang);
$content_uri    = \Notice\Site_View::get_notice_content_uri($foreign_table, $foreign_id, $parent_table, $parent_id);
?>
<?php echo $notice_message; ?>

<?php echo __('message_check_from_url'); ?>


<?php echo \Uri::create($content_uri); ?>


<?php echo t('site.datetime'); ?>: <?php echo $sort_datetime; ?>
