<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!IS_API): ?><div id="article_list"><?php endif; ?>
<?php if ($list): ?>
<?php echo Form::hidden('liked_timeline_ids', json_encode($liked_timeline_ids), array('id' => 'liked_timeline_ids')); ?>
<?php
if (!isset($is_display_load_before_link)) $is_display_load_before_link = false;
if ($next_id || $is_display_load_before_link)
{
	$list_more_box_attr_default = array('class' => 'listMoreBox js-ajax-Load_timeline');
	$gete_data_list_default = array();
	if (!empty($member)) $gete_data_list_default['member_id'] = $member->id;
	if (!empty($mytimeline)) $gete_data_list_default['mytimeline'] = 1;
}

// see latest link.
if ($is_display_load_before_link)
{
	$first_obj = Util_Array::get_first($list);
	$gete_data_list = array('since_id' => $first_obj->id);
	$load_before_link_attr = array_merge($list_more_box_attr_default, array(
		'data-get_data' => json_encode(array_merge($gete_data_list_default, $gete_data_list)),
		'data-type' => 'see_latest',
	));
	echo html::anchor('#', icon_label('site.see_latest', 'both', false, null, 'fa fa-'), $load_before_link_attr);
}
?>

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

<?php // see more link.
if ($next_id)
{
	$gete_data_list = array('max_id' => $next_id);
	if (!empty($since_id)) $gete_data_list['since_id'] = $since_id;

	$anchor_text_default = icon_label('site.see_more', 'both', false, null, 'fa fa-');
	if (!empty($see_more_link['uri']))
	{
		$href = Uri::create_url($see_more_link['uri'], $gete_data_list);
		$anchor_text = !empty($see_more_link['text']) ? $see_more_link['text'] : $anchor_text_default;
		$load_after_link_attr = array('class' => 'listMoreBox');
	}
	else
	{
		$href = IS_API ? '#' : Uri::create_url(Uri::string(), array('max_id' => $next_id));
		$anchor_text = $anchor_text_default;
		$load_after_link_attr = array_merge($list_more_box_attr_default, array('data-get_data' => json_encode(array_merge($gete_data_list_default, $gete_data_list))));
	}
	echo Html::anchor($href, $anchor_text, $load_after_link_attr);
}
?>

<?php if (!IS_API): ?></div><!-- article_list --><?php endif; ?>
<?php if (IS_API): ?></body></html><?php endif; ?>
