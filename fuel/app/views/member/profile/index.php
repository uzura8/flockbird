<?php echo render('_parts/member_profile', array(
	'is_mypage' => $is_mypage,
	'member' => $member,
	'member_profiles' => $member_profiles,
	'access_from' => $access_from,
	'with_link2profile_image' => true,
	'with_edit_btn' => true,
	'with_message_btn' => true,
)); ?>
