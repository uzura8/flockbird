<?php echo render('_parts/member_profile', array(
	'is_mypage' => $is_mypage,
	'member' => $member,
	'member_profiles' => $member_profiles,
	'access_from' => $access_from,
	'display_type' => 'summery',
	'link_uri' => $is_mypage ? 'member/profile' : 'member/profile/'.$member->id,
)); ?>

<?php if (is_enabled('timeline')): ?>
<h3><?php echo sprintf('%sさんの%s', $member->name, term('timeline')); ?></h3>
<?php echo render('timeline::_parts/list', array(
	'list' => $list,
	'next_id' => $next_id,
	'since_id' => $since_id,
	'is_display_load_before_link' => $is_display_load_before_link,
	'member' => $member,
	'liked_timeline_ids' => $liked_timeline_ids,
)); ?>
<?php endif; ?>
