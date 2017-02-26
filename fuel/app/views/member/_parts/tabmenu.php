<ul class="nav nav-tabs nav-justified mb10">
<?php if (is_enabled('timeline')): ?>
	<?php echo html_tag('li', array(
		'class' => check_current_uris(array('member/'.$member->id, 'member/me')) ? 'active' : '',
	), navigation_link('timeline.plural', 'member/'.$member->id)); ?>
<?php endif; ?>
	<?php echo html_tag('li', array(
		'class' => check_current_uri('member/profile/'.$member->id) ? 'active' : '',
	), navigation_link('profile', 'member/profile/'.$member->id)); ?>

<?php
	if (conf('memberRelation.friend.isEnabled'))
	{
		$uri = sprintf('member/%d/relation/friends', $member->id);
		echo html_tag(
			'li',
			array('class' => check_current_uri($uri) ? 'active' : ''),
			anchor($uri, sprintf('%s (%d)', term('friend'), Model_MemberRelation::get_count4member_id($member->id, 'friend')))
		);
	}
?>

<?php
	if (conf('memberRelation.follow.isEnabled'))
	{
		$uri = sprintf('member/%d/relation/follows', $member->id);
		echo html_tag(
			'li',
			array('class' => check_current_uri($uri) ? 'active' : ''),
			anchor($uri, sprintf('%s (%d)', t('following'), Model_MemberRelation::get_count4member_id($member->id, 'follow')))
		);

		$uri = sprintf('member/%d/relation/followers', $member->id);
		echo html_tag(
			'li',
			array('class' => check_current_uri($uri) ? 'active' : ''),
			anchor($uri, sprintf('%s (%d)', t('follower'), Model_MemberRelation::get_count4member_id($member->id, 'follow', 'member_id_to')))
		);
	}
?>
</ul>

