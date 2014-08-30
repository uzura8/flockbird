<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!IS_API): ?><div id="article_list"><?php endif; ?>
<?php if ($list): ?>
<?php foreach ($list as $id => $timeline_cache): ?>
<?php
echo render('timeline::_parts/article', array(
	'timeline_cache_id' => $timeline_cache->id,
	'timeline_id' => $timeline_cache->timeline_id,
	'type' => $timeline_cache->type,
	'comment_count' => $timeline_cache->comment_count,
	'like_count' => $timeline_cache->like_count,
	'member_id' => $timeline_cache->member_id,
	'self_member_id' => \Auth::check() ? $u->id : 0,
));
?>
<?php endforeach; ?>
<?php endif; ?>

<?php if ($next_id): ?>
<?php
$attr = array(
	'class' => 'listMoreBox js-ajax-Load_timeline',
	'data-max_id' => $next_id,
	'data-latest' => 1,
	'data-desc' => 1,
);
$data_array = array('desc' => 1);
if (!empty($member)) $data_array['member_id'] = $member->id;
if (!empty($mytimeline)) $data_array['mytimeline'] = 1;
if ($data_array) $attr['data-get_data'] = $data_array;
if (!empty($since_id)) $attr['data-since_id'] = $since_id;
?>
<a href="#" <?php echo Util_Array::conv_array2attr_string($attr); ?>><?php echo icon_label('site.see_more', 'both', false, null, 'fa fa-'); ?></a>
<?php endif; ?>

<?php if (!IS_API): ?></div><!-- article_list --><?php endif; ?>
<?php if (IS_API): ?></body></html><?php endif; ?>
