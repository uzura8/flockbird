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
		__('message_confirmation_to_agree_terms_of_use', array('link' => anchor('site/term', t('site.term'), false, array(), true))),
		null,
		$label_size,
		true
	); ?>
<?php endif; ?>

	<?php echo form_button($is_regist ? 'site.register_with_agree' : 'form.do_edit', 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
