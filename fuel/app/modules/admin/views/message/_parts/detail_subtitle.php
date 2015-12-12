<?php
$menus = array(
	array('icon_term' => 'form.do_edit', 'href' => 'admin/message/edit/'.$message->id),
	array('icon_term' => 'form.do_send', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => 'admin/message/send/'.$message->id,
		'data-msg' => sprintf('%sしますか？', term('form.send')),
	)),
	array('icon_term' => 'form.do_delete', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => 'admin/message/delete/'.$message->id,
		'data-msg' => term('form.delete').'します。よろしいですか？',
	)),
);

echo btn_dropdown('form.edit', $menus, true, null, null, true, array('class' => 'edit'));
?>
