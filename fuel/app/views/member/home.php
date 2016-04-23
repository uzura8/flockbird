<?php echo render('_parts/member_profile', array(
	'member' => $member,
	'member_profiles' => $member_profiles,
	'access_from' => $access_from,
	'display_type' => !empty($display_type) ? $display_type : 'summary',
	'link_uri' => $is_mypage ? 'member/profile' : 'member/profile/'.$member->id,
	'hide_fallow_btn' => isset($hide_fallow_btn) ? $hide_fallow_btn : false,
	'with_edit_btn' => true,
	'show_message_btn' => true,
	'report_data' => !empty($report_data) ? $report_data : array(),
)); ?>

<?php echo render('member/_parts/tabmenu', array(
	'member' => $member,
	'access_from' => $access_from,
)); ?>

<?php if (is_enabled('timeline') && !empty($timeline)): ?>
<?php echo render('timeline::_parts/list', $timeline); ?>
<?php endif; ?>
