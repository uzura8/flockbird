<?php $is_api_request = Site_Util::check_is_api_request(); ?>
<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if ($list): ?>
<?php foreach ($list as $id => $timeline): ?>
		<?php echo \Timeline\Site_Util::get_article_view($timeline); ?>
<?php endforeach; ?>
<?php endif; ?>

<?php if ($is_next): ?>
<nav id="page-nav">
<?php
$attr = array('class' => 'load_more_timeline listMoreBox', 'data-last_id' => $id);
if (!empty($member)) $attr['data-member_id'] = $member->id;
echo Html::anchor('#', '<i class="ls-icon-dropdown"></i> もっとみる', $attr);
?>
</nav>
<?php endif; ?>

<?php if ($is_api_request): ?></body></html><?php endif; ?>
