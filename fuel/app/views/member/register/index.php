<?php echo alert(sprintf('%sと%sを入力してください', term('profile'), term('site.password'))); ?>
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
	<?php echo form_input($val, 'password', '', 6, $label_size); ?>
	<?php echo Form::hidden('token', Input::param('token')); ?>
	<?php echo form_text(
		anchor('site/term', '利用規約', false, array(), true).' をお読みいただき、同意される方のみ「同意して登録する」ボタンを押してください。',
		null,
		$label_size,
		true
	); ?>
	<?php echo form_button('同意して登録する', 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
