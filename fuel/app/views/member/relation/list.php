<?php
echo render('_parts/member_list', array(
	'is_base_page' => true,
	'is_simple_list' => true,
	'hide_fallow_btn' => $type == 'access_block',
	'show_access_block_btn' => $type == 'access_block',
	'list' => $list,
	'member_relation_name' => $type == 'follower' ? 'member_from' : 'member',
	'next_id' => $next_id,
	'since_id' => $since_id,
	'get_uri' => sprintf('member/relation/api/list/%s/%d.json', $type, $member->id),
	'history_keys' => json_encode(array('q', 'max_id')),
	'is_display_load_before_link' => $max_id ? true : false,
));
?>

