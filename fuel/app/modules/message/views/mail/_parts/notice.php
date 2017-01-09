<?php echo __('message_recieve_for_from', array(
	'subject' => conv_honorific_name($member_name_from),
	'label' => $message_type_name,
), '', $lang); ?>

----
<?php echo strim($message_body, conf('noticeMail.trimWidth.body', 'message'), null, false); ?>

----

<?php echo __('message_check_from_url'); ?>


<?php echo \Uri::create('message/'.$message_id); ?>

<?php echo term('form.recieved', 'site.datetime'); ?>: <?php echo $received_at; ?>
