<?php echo render('_parts/member_profile', array(
	'member' => $member,
	'member_profiles' => $member_profiles,
	'access_from' => $access_from,
	'with_edit_btn' => true,
	'show_message_btn' => true,
)); ?>
