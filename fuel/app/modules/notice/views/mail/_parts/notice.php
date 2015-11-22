<?php
$action_members = \Notice\Site_View::get_action_members($members, $members_count);
$action         = \Notice\Site_View::convert_notice_action($foreign_table, $type);
$content_uri    = \Notice\Site_View::get_notice_content_uri($foreign_table, $foreign_id, $parent_table, $parent_id);
?>
<?php echo $action_members; ?>が、<?php echo $action; ?>

<?php echo \Uri::create($content_uri); ?> にてご確認ください。

<?php echo term('site.datetime'); ?>: <?php echo $sort_datetime; ?>
