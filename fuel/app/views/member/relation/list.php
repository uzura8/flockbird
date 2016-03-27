<?php
echo render('_parts/member_list', array(
	'is_base_page' => true,
	'is_simple_list' => true,
	'is_hide_fallow_btn' => true,
	'is_display_access_block_btn' => true,
	'list' => $list,
	'related_member_table_name' => 'member',
	'next_id' => $next_id,
	'since_id' => $since_id,
	'get_uri' => 'member/relation/api/list/access_block.json',
	'history_keys' => json_encode(array('q', 'max_id')),
	'is_display_load_before_link' => $max_id ? true : false,
));
?>

