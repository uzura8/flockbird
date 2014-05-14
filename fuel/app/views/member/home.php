<?php echo render('_parts/member_profile', array(
	'is_mypage' => $is_mypage,
	'member' => $member,
	'member_profiles' => $member_profiles,
	'access_from' => $access_from,
	'display_type' => 'summery',
	'link_uri' => $is_mypage ? 'member/profile' : 'member/profile/'.$member->id,
)); ?>

<h3><?php echo sprintf('%sさんの%s', $member->name, term('timeline')); ?></h3>
<?php echo render('timeline::_parts/list', array('list' => $list, 'is_next' => $is_next, 'member' => $member)); ?>

