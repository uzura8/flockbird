<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php echo __('nobody_liked'); ?>
<?php else: ?>
<div id="article_list">
<?php foreach ($list as $id => $obj): ?>
	<div class="article" id="article_<?php echo $id; ?>">
<?php echo render('_parts/member_profile', array(
	'member' => !empty($member_relation_name) ? $obj->{$member_relation_name} : $obj,
	'member_profiles' => $with_profile ? Model_MemberProfile::get4member_id($member->id, true, 'summary') : array(),
	'is_simple_list' => true,
	'page_type' => 'lerge_list',
	'display_type' => 'summary',
)); ?>
	</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<nav id="page-nav">
<?php
$uri = sprintf('member/api/list.html?page=%d', $page + 1);
?>
<a href="<?php echo Uri::base_path($uri); ?>"></a>
</nav>

<?php if (IS_API): ?></body></html><?php endif; ?>
