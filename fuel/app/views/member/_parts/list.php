<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php echo term('member.view'); ?>の<?php echo term('site.registration'); ?>がありません。
<?php else: ?>
<div id="article_list">
<?php foreach ($list as $id => $member): ?>
	<div class="article" id="article_<?php echo $id; ?>">
<?php echo render('_parts/member_profile', array(
	'member' => $member,
	'member_profiles' => Model_MemberProfile::get4member_id($member->id, true, 'summery'),
	'access_from' => Auth::check() ? 'member' : 'guest',
	'is_list' => true,
	'page_type' => 'lerge_list',
	'display_type' => 'summery',
)); ?>
	</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<nav id="page-nav">
<?php
$uri = sprintf('member/api/list.html?page=%d', $page + 1);
echo Html::anchor($uri, '');
?>
</nav>

<?php if (IS_API): ?></body></html><?php endif; ?>
