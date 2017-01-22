<?php echo render('news::_parts/news_subinfo', array('news' => $news)); ?>

<?php
$publish_action = $news->is_published ? 'unpublish' : 'publish';
$menus = array(
	array('icon_term' => 'form.preview', 'href' => 'news/preview/'.$news->slug.'?token='.$news->token, 'attr' => array('target' => '_blank')),
	array('icon_term' => 'form.do_edit', 'href' => 'admin/news/edit/'.$news->id),
	array('icon_term' => 'form.do_'.$publish_action, 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => sprintf('admin/news/%s/%d', $publish_action, $news->id),
		'data-msg' => __($news->is_published ? 'message_unpublish_confirm' : 'message_publish_confirm'),
	)),
	array('icon_term' => 'form.do_delete', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => 'admin/news/delete/'.$news->id,
		'data-msg' => __('message_delete_confirm'),
	)),
);
echo btn_dropdown('form.edit', $menus, true, null, null, true, array('class' => 'edit'));
?>
