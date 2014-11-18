<ul class="list-inline mt10">
	<li><small><label><?php echo term('site.last', 'form.updated', 'site.datetime'); ?>:</label> <?php echo site_get_time($content_page->updated_at) ?></small></li>
	<li><?php echo label_is_secure($content_page->is_secure); ?></li>
</ul>

<?php
$menus = array(
	array('icon_term' => 'form.do_edit', 'href' => 'admin/content/page/edit/'.$content_page->id),
	array('icon_term' => 'form.do_delete', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => 'admin/content/page/delete/'.$content_page->id,
		'data-msg' => '削除します。よろしいですか？',
	)),
);
echo btn_dropdown('form.edit', $menus, true, null, null, true, array('class' => 'edit'));
?>
