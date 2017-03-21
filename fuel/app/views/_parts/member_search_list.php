<?php
if (!isset($list_id)) $list_id = 'article_list';
if (!isset($is_simple_list)) $is_simple_list = false;
?>
<?php if (empty($is_base_page)): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!count($list)): ?>
<?php if (!empty($no_data_message)): ?>
<?php echo $no_data_message; ?>
<?php else: ?>
<?php echo t('common.delimitter.of', array('subject' => t('site.registration'), 'object' => t('member.view'))); ?>
<?php endif; ?>
<?php else: ?>

<?php
$is_display_load_before_link = $page > 1          && in_array($loaded_position, array('replace', 'prepend'));
$is_display_load_after_link  = !empty($next_page) && in_array($loaded_position, array('replace', 'append'));
if ($is_display_load_before_link || $is_display_load_after_link)
{
	$load_link_attr_default = array(
		'class' => 'listMoreBox js-ajax-loadList',
		'data-uri' => 'member/api/search.html',
		'data-list' => '#article_list',
	);
	if (!empty($history_keys)) $load_link_attr_default['data-history_keys'] = json_encode($history_keys);
	if (empty($get_data_list) && !empty($inputs)) $get_data_list = $inputs;
	if (empty($get_data_list)) $get_data_list = array();
	if (!empty($loaded_position)) $get_data_list['position'] = $loaded_position;
}
if ($is_display_load_before_link)
{
	$get_data_list['page'] = $page - 1;
	$get_data_list['position'] = 'prepend';
	$href = Uri::create_url('member/search', $get_data_list);
	$load_before_link_attr = array('data-get_data' => json_encode($get_data_list));
	echo Html::anchor($href, icon_label('site.see_more', 'both', false, null, 'fa fa-'), array_merge($load_before_link_attr, $load_link_attr_default));
}
?>

<?php if (!empty($is_base_page)): ?><div id="<?php echo $list_id; ?>"><?php endif; ?>
<?php foreach ($list as $obj): ?>
	<div class="article" id="article_<?php echo $obj->member_id; ?>">
<?php echo render('_parts/member_profile', array(
	'member_id' => $obj->member_id,
	'page_type' => 'lerge_list',
	'display_type' => 'summary',
	'is_simple_list' => !empty($is_simple_list),
	'hide_fallow_btn' => !empty($hide_fallow_btn),
	'show_access_block_btn' => !empty($show_access_block_btn),
)); ?>
	</div>
<?php endforeach; ?>

<?php
if ($is_display_load_after_link)
{
	$get_data_list['page'] = $next_page;
	$get_data_list['position'] = 'append';
	$href = Uri::create_url('member/search', $get_data_list);
	$load_after_link_attr = array('data-get_data' => json_encode($get_data_list));
	echo Html::anchor($href, icon_label('site.see_more', 'both', false, null, 'fa fa-'), array_merge($load_after_link_attr, $load_link_attr_default));
}
?>

<?php if (!empty($is_base_page)): ?></div><?php endif; ?>
<?php endif; ?>

<?php if (empty($is_base_page)): ?></body></html><?php endif; ?>
