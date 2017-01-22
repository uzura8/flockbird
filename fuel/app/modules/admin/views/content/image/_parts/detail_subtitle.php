<?php
$menus = array();
//$menus[] = array('icon_term' => 'form.do_edit', 'href' => 'admin/content/image/edit/'.$site_image->id);
$menus[] = array('icon_term' => 'form.do_delete', 'attr' => array(
	'class' => 'js-simplePost',
	'data-uri' => 'admin/content/image/delete/'.$site_image->id,
	'data-msg' => __('message_delete_confirm'),
));
echo btn_dropdown('form.edit', $menus, true, null, null, true, array('class' => 'edit'));
?>
