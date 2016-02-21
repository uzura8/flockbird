<?php echo render('_parts/search_form', array(
	'input_value' => !empty($search_word) ? $search_word : null,
	'input_attr' => array(
		'placeholder' => sprintf('%sで%s', term('member.name'), term('form.search')),
		'class' => 'form-control js-keyup',
		'data-btn' => '#btn_search_member',
	),
	'btn_attr' => array(
		'id' => 'btn_search_member',
		'data-list' => '#article_list',
		'data-uri' => 'member/api/list.json',
	),
)); ?>

<?php
echo render('_parts/member_list', array(
	'list' => $list,
	'next_id' => $next_id,
	'since_id' => $since_id,
	'get_uri' => 'member/api/list.json',
	'history_key' => 'max_id',
	'is_display_load_before_link' => $max_id ? true : false,
));
?>

