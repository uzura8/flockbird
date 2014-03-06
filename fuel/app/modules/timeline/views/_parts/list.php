<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!IS_API): ?><div id="article_list"><?php endif; ?>
<?php if ($list): ?>
<?php foreach ($list as $id => $timeline_cache): ?>
		<?php echo \Timeline\Site_Util::get_article_view($timeline_cache->id, $timeline_cache->timeline_id, \Auth::check() ? $u->id : 0); ?>
<?php endforeach; ?>
<?php endif; ?>

<?php if ($is_next): ?>
<nav id="page-nav">
<?php
$attr = array('class' => 'load_more_timeline listMoreBox', 'data-last_id' => $id);
if (!empty($member)) $attr['data-member_id'] = $member->id;
if (!empty($mytimeline)) $attr['data-mytimeline'] = 1;
echo Html::anchor('#', '<i class="ls-icon-dropdown"></i> もっとみる', $attr);
?>
</nav>
<?php endif; ?>
<?php if (!IS_API): ?></div><!-- article_list --><?php endif; ?>
<?php if (IS_API): ?></body></html><?php endif; ?>
