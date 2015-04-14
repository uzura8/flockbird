<?php
$uri = 'admin/news/create';
$method = 'GET';
$attr = array();
if (conf('image.isInsertBody', 'news'))
{
	$uri = 'admin/news/create_instantly';
	$method = 'POST';
	$attr = array(
		'class' => 'js-simplePost',
		'data-msg' => term('news.view').'を'.term('form.create').'します。よろしいですか？',
	);
}
if (check_acl($uri, $method)) echo btn('form.create', $uri, 'edit', true, '', null, $attr);
?>

