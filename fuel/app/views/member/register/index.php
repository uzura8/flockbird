<?php echo alert(term('profile').'とパスワードを入力してください'); ?>
<div class="well">
<?php $label_size = 3; ?>
<?php echo form_open(); ?>
	<?php echo render('member/profile/_parts/form/edit_items', array(
		'label_size' => $label_size,
		'val' => $val,
		'member_public_flags' => $member_public_flags,
		'profiles' => $profiles,
		'member_profile_public_flags' => $member_profile_public_flags,
	)); ?>
	<?php echo form_input($val, 'password', '', 6, 3); ?>
	<?php echo Form::hidden('token', Input::param('token')); ?>
	<?php echo form_button(term('form.do_edit'), 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
