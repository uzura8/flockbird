<?php
$label_size = 3;
$btn_label = t('form.do_send_for', array('label' => t('site.confirmation_mail')));
?>
<div class="well">
<?php echo form_open(true, false, !empty($action) ? array('action' => $action) : array()); ?>
<?php echo form_input($val, 'email', '', 7, $label_size); ?>
<?php echo form_input($val, 'email_confirm', '', 7, $label_size); ?>
<?php if (!$u->check_registered_oauth(true)): ?>
	<?php echo form_input($val, 'password', '', 7, $label_size); ?>
<?php endif; ?>
<?php echo form_button($btn_label, 'submit', null, null, $label_size, null, 'form.do_send'); ?>
<?php echo form_close(); ?>
</div><!-- well -->

<?php if (empty($is_regist_mode)): ?>
<?php echo render('member/setting/_parts/footer_navi'); ?>
<?php endif; ?>

