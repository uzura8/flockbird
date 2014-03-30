<?php
echo render('_parts/list_group', array('items' => array(array(
	'link' => 'member/setting',
	'text' => term(array('site.setting', 'site.item', 'site.list')),
))));
?>
