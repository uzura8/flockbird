<?php
if (!isset($list_id)) $list_id = 'article_list';
if (!isset($is_simple_list)) $is_simple_list = false;
?>
<?php if (empty($is_base_page)): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php if (!empty($no_data_message)): ?>
<?php echo $no_data_message; ?>
<?php else: ?>
<?php echo term('member.view'); ?>の<?php echo term('site.registration'); ?>がありません。
<?php endif; ?>
<?php else: ?>

<?php
if (!isset($is_display_load_before_link)) $is_display_load_before_link = false;
if ($next_id || $is_display_load_before_link)
{
	$load_link_attr_default = array(
		'class' => 'listMoreBox js-ajax-loadList',
		'data-uri' => $get_uri,
		'data-list' => '#article_list',
	);
	if (!empty($history_keys)) $load_link_attr_default['data-history_keys'] = json_encode($history_keys);
}
if ($is_display_load_before_link)
{
	$first_obj = Util_Array::get_first($list);
	$gete_data_list = array('since_id' => $first_obj->id);
	$load_before_link_attr = array('data-get_data' => json_encode($gete_data_list));
	echo Html::anchor('#', icon_label('site.see_latest', 'both', false, null, 'fa fa-'), array_merge($load_before_link_attr, $load_link_attr_default));
}
?>

<?php if (!empty($is_base_page)): ?><div id="<?php echo $list_id; ?>"><?php endif; ?>
<?php foreach ($list as $id => $obj): ?>
	<div class="article" id="article_<?php echo $id; ?>">
<?php echo render('_parts/member_profile', array(
	'member' => !empty($member_relation_name) ? $obj->{$member_relation_name} : $obj,
	'next_id' => $next_id,
	'access_from' => Auth::check() ? 'member' : 'guest',
	'is_list' => true,
	'page_type' => 'list',
	'display_type' => 'summary',
	'is_simple_list' => !empty($is_simple_list),
	'is_hide_fallow_btn' => !empty($is_hide_fallow_btn),
	'is_display_access_block_btn' => !empty($is_display_access_block_btn),
)); ?>
	</div>
<?php endforeach; ?>

<?php
if ($next_id)
{
	if (empty($get_data_list)) $get_data_list = array();
	$get_data_list['max_id'] = $next_id;
	if (!empty($since_id)) $get_data_list['since_id'] = $since_id;
	$load_after_link_attr = array('data-get_data' => json_encode($get_data_list));
	$href = empty($is_base_page) ? '#' : Uri::create_url(Uri::string(), array('max_id' => $next_id));
	echo Html::anchor($href, icon_label('site.see_more', 'both', false, null, 'fa fa-'), array_merge($load_after_link_attr, $load_link_attr_default));
}
?>

<?php if (!empty($is_base_page)): ?></div><?php endif; ?>
<?php endif; ?>

<?php if (empty($is_base_page)): ?></body></html><?php endif; ?>
