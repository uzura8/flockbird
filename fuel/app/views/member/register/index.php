<?php echo alert(__('message_please_input_following_items')); ?>
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
	<?php echo form_input($val, 'password', '', 7, $label_size); ?>
<?php if (!$member_pre->password): ?>
	<?php echo form_input($val, 'password_confirm', '', 7, $label_size); ?>
<?php endif; ?>
	<?php echo Form::hidden('token', Input::param('token')); ?>
	<?php echo form_text(
		__('message_confirmation_to_agree_terms_of_use', array('link' => anchor('site/term', t('site.term'), false, array(), true))),
		null,
		$label_size,
		true
	); ?>
	<?php echo form_button('site.register_with_agree', 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
