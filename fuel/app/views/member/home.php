<?php echo render('_parts/member_profile', array(
	'is_mypage' => $is_mypage,
	'member' => $member,
	'member_profiles' => $member_profiles,
	'access_from' => $access_from,
	'display_type' => !empty($display_type) ? $display_type : 'summary',
	'link_uri' => $is_mypage ? 'member/profile' : 'member/profile/'.$member->id,
	'is_hide_fallow_btn' => isset($is_hide_fallow_btn) ? $is_hide_fallow_btn : false,
)); ?>

<?php if (is_enabled('timeline') && !empty($timeline)): ?>
<h3><?php echo sprintf('%sさんの%s', member_name($member), term('timeline')); ?></h3>
<?php echo render('timeline::_parts/list', $timeline); ?>
<?php endif; ?>
