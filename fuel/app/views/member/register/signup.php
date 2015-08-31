<?php echo alert(sprintf('%sに使用する %s と %s を入力してください。', term('site.login'), term('site.email'), term('site.password'))); ?>
<div class="well">
<?php echo form_open(true, false, array('action' => 'member/register/confirm_signup')); ?>
	<?php echo form_input($val, 'email', null, 6, 3); ?>
	<?php echo form_input($val, 'password', null, 6, 3); ?>
	<?php echo form_button(sprintf('%sを%s', term('form.confirming', 'site.mail'), term('form.do_send')), null, null, null, 3, null, 'form.do_send'); ?>
<?php echo form_close(); ?>
</div><!-- well -->
