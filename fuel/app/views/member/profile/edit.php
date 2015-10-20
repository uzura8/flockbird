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

<?php if ($is_regist): ?>
	<?php echo form_text(
		anchor('site/term', '利用規約', false, array(), true).' をお読みいただき、同意される方のみ「同意して登録する」ボタンを押してください。',
		null,
		$label_size,
		true
	); ?>
<?php endif; ?>

	<?php echo form_button($is_regist ? '同意して登録する' : 'form.do_edit', 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
