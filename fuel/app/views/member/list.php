<?php echo render('member/_parts/search_form', array(
	'search_word' => !empty($search_word) ? $search_word : null,
)); ?>

<?php
echo render('_parts/member_list', array(
	'is_base_page' => true,
	'list' => $list,
	'next_id' => $next_id,
	'since_id' => $since_id,
	'get_uri' => 'member/api/list.json',
	'history_keys' => json_encode(array('q', 'max_id')),
	'is_display_load_before_link' => $max_id ? true : false,
));
?>

