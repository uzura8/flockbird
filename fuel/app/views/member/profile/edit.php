<div class="well">
<?php $label_size = 3; ?>
<?php echo form_open(); ?>
	<?php echo render('member/profile/_parts/form/edit_items', array(
		'label_size' => $label_size,
		'val' => $val,
		'site_configs_profile' => $site_configs_profile,
		'member_public_flags' => $member_public_flags,
		'profiles' => $profiles,
		'member_profile_public_flags' => $member_profile_public_flags,
	)); ?>
	<?php echo form_button(term('form.do_edit'), 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
