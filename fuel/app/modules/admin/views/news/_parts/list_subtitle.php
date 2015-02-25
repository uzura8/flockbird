<?php
$url = 'admin/news/create';
$attr = array();
if (conf('image.isInsertBody', 'news'))
{
	$url = 'admin/news/create_instantly';
	$attr = array(
		'class' => 'js-simplePost',
		'data-msg' => term('news.view').'を'.term('form.create').'します。よろしいですか？',
	);
}
echo btn('form.create', $url, 'edit', true, '', null, $attr);
?>

