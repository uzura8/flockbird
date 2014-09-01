<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!IS_API): ?><div id="article_list"><?php endif; ?>
<?php if ($list): ?>

<?php
if (!isset($is_display_load_before_link)) $is_display_load_before_link = false;
if ($next_id || $is_display_load_before_link)
{
	$list_more_box_attr_default = array('class' => 'listMoreBox js-ajax-Load_timeline');
	$data_array = array();
	if (!empty($member)) $data_array['member_id'] = $member->id;
	if (!empty($mytimeline)) $data_array['mytimeline'] = 1;
	if ($data_array) $list_more_box_attr_default['data-get_data'] = $data_array;
}
?>
<?php if ($is_display_load_before_link): ?>
<?php
$first_obj = Util_Array::get_first($list);
$load_before_link_attr = array('data-since_id' => $first_obj->id);
$load_before_link_attr = array_merge($list_more_box_attr_default, $load_before_link_attr);
?>
<a href="#" <?php echo Util_Array::conv_array2attr_string($load_before_link_attr); ?>><?php echo icon_label('site.see_latest', 'both', false, null, 'fa fa-'); ?></a>
<?php endif; ?>

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
$load_after_link_attr = array('data-max_id' => $next_id);
if (!empty($since_id)) $load_after_link_attr['data-since_id'] = $since_id;
$load_after_link_attr = array_merge($list_more_box_attr_default, $load_after_link_attr);
?>
<a href="#" <?php echo Util_Array::conv_array2attr_string($load_after_link_attr); ?>><?php echo icon_label('site.see_more', 'both', false, null, 'fa fa-'); ?></a>
<?php endif; ?>

<?php if (!IS_API): ?></div><!-- article_list --><?php endif; ?>
<?php if (IS_API): ?></body></html><?php endif; ?>
