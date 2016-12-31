<?php echo render('_parts/member_profile', array(
	'member' => $member,
	'member_profiles' => $member_profiles,
	'access_from' => $access_from,
	'with_edit_btn' => true,
	'show_message_btn' => true,
	'report_data' => $report_data,
)); ?>

<?php if (conf('address.isEnabled', 'member')
	&& conf('address.isDisplay', 'member')
	&& $member_address = Model_MemberAddress::get_one_main($member->id)): ?>

<h3>
	<?php echo t('member.address.view'); ?>
<?php if ($member->id == get_uid()): ?>
	<?php echo btn('form.edit', 'member/setting/address', 'edit', true, 'sm', null, array('class' => 'pull-right')); ?>
<?php endif; ?>
</h3>
<?php echo render('member/_parts/address', array(
		'member' => $member,
		'member_address' => $member_address,
		'display_type' => 'detail',
	)); ?>
<?php endif; ?>
