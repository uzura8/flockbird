<?php echo render('_parts/member_profile', array(
	'is_mypage' => $is_mypage,
	'member' => $member,
	'member_profiles' => $member_profiles,
	'access_from' => $access_from,
	'display_type' => !empty($display_type) ? $display_type : 'summary',
	'link_uri' => $is_mypage ? 'member/profile' : 'member/profile/'.$member->id,
	'is_hide_fallow_btn' => isset($is_hide_fallow_btn) ? $is_hide_fallow_btn : false,
	'with_link2profile_image' => true,
	'with_edit_btn' => true,
	'with_message_btn' => true,
)); ?>

<?php echo render('member/_parts/tabmenu', array(
	'member' => $member,
	'access_from' => $access_from,
)); ?>

<?php if (is_enabled('timeline') && !empty($timeline)): ?>
<?php echo render('timeline::_parts/list', $timeline); ?>
<?php endif; ?>
