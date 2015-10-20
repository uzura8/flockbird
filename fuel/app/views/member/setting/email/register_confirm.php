<?php
$label_size = 3;
$back_uri = 'member/setting/email';
if (!empty($is_regist_mode)) $back_uri .= '/regist';
?>
<div class="well">
<?php echo form_open(true, false, !empty($action) ? array('action' => $action) : array()); ?>
<?php echo form_text($input['email'], term('site.email'), $label_size); ?>
<?php echo form_input($val, 'code', '', 6, $label_size); ?>
<?php echo form_button($is_registerd ? 'form.do_update' : 'site.register', 'submit', null, null, $label_size); ?>
<?php echo form_button('form.back', 'button', 'button_back', array(
	'class' => 'btn btn-default js-simpleLink',
	'data-uri' => $back_uri,
), $label_size); ?>
<?php echo Form::hidden('email', $input['email']); ?>
<?php echo Form::hidden('email_confirm', $input['email_confirm']); ?>
<?php if (!empty($input['password'])): ?>
	<?php echo Form::hidden('password', $input['password']); ?>
<?php endif; ?>
<?php echo form_close(); ?>
</div><!-- well -->
