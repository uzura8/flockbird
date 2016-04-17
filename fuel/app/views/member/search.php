<?php echo render('member/_parts/search_form', array(
	'inputs' => !empty($inputs) ? $inputs : array(),
	'is_simple_search' => false,
	'val' => $val,
	'profiles' => $profiles,
)); ?>

<?php
echo render('_parts/member_search_list', array(
	'is_base_page' => true,
	'get_uri' => 'member/api/search.json',
	'list' => $list,
	'limit' => $limit,
	'page' => $page,
	'next_page' => $next_page,
	'inputs' => $inputs,
	'history_keys' => json_encode(array_keys($inputs)),
	'no_data_message' => !empty($no_data_message) ? $no_data_message : '',
	'loaded_position' => $loaded_position,
	'is_desplay_load_before_link' => isset($is_desplay_load_before_link) ? $is_desplay_load_before_link : false,
));
?>

