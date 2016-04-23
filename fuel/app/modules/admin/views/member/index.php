<?php echo render('_parts/member_profile', array(
	'member' => $member,
	'member_profiles' => $member_profiles,
	'access_from' => $access_from,
	'display_type' => 'detail',
	'hide_fallow_btn' => true,
	'with_edit_btn' => true,
	'show_message_btn' => true,
	'report_data' => array(),
)); ?>
