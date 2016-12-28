<?php echo alert(__('member_message_signup')); ?>
<div class="well">
<?php echo form_open(true, false, array('action' => 'member/register/confirm_signup')); ?>
	<?php echo form_input($val, 'email', null, 6, 3); ?>
	<?php echo form_input($val, 'password', null, 6, 3); ?>
	<?php echo form_button(t('form.do_send_for', array('label' => t('site.confirmation_mail'))), null, null, null, 3, null, 'form.do_send'); ?>
<?php echo form_close(); ?>
</div><!-- well -->
