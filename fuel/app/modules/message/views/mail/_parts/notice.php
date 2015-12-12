<?php echo $member_name_from; ?>から、<?php echo sprintf('%sを%sしました。', $message_type_name, term('form.recieved')); ?>

<?php if ($message_subject): ?>
<?php echo strim($message_subject, conf('noticeMail.trimWidth.subject', 'message'), null, false); ?>

----
<?php endif; ?>

<?php echo strim($message_body, conf('noticeMail.trimWidth.body', 'message'), null, false); ?>


<?php echo \Uri::create('message/'.$message_id); ?> にてご確認ください。

<?php echo term('form.recieved', 'site.datetime'); ?>: <?php echo $received_at; ?>
