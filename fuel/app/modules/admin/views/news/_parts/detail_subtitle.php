<ul class="list-inline mt10">
	<li><small><label><?php echo term('site.last', 'form.updated', 'site.datetime'); ?>:</label> <?php echo site_get_time($news->updated_at) ?></small></li>
	<?php if (isset_datatime($news->published_at)): ?><li><small><label><?php echo term('form.publish', 'site.datetime'); ?>:</label> <?php echo site_get_time($news->published_at) ?></small></li><?php endif; ?>
</ul>

<?php
$publish_action = $news->is_published ? 'unpublish' : 'publish';
$menus = array(
	array('icon_term' => 'form.preview', 'href' => 'news/preview/'.$news->slug.'?token='.$news->token, 'attr' => array('target' => '_blank')),
	array('icon_term' => 'form.do_edit', 'href' => 'admin/news/edit/'.$news->id),
	array('icon_term' => 'form.do_'.$publish_action, 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => sprintf('admin/news/%s/%d', $publish_action, $news->id),
		'data-msg' => sprintf('%sしますか？', $news->is_published ? term('form.unpublish').'に' : term('form.publish')),
	)),
	array('icon_term' => 'form.do_delete', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => 'admin/news/delete/'.$news->id,
		'data-msg' => '削除します。よろしいですか？',
	)),
);
echo btn_dropdown('form.edit', $menus, true, null, null, true, array('class' => 'edit'));
?>
