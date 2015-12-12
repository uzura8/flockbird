<?php echo render('_parts/member_contents_box', array(
	'member'      => $message->member,
	'id'          => $message->id,
	'model'       => 'message',
	'size'        => 'M',
	'date'        => array(
		'datetime' => $message->sent_at ? $message->sent_at : $message->updated_at,
		'label'    => $message->sent_at ? term('form.send', 'site.datetime') : term('form.updated', 'site.datetime'),
	)
)); ?>

<?php if (!$message->is_sent): ?>
<?php
$menus = array(
	array('icon_term' => 'form.do_edit', 'href' => 'admin/message/edit/'.$message->id),
	array('icon_term' => 'form.do_send', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => sprintf('admin/message/send/%d', $message->id),
		'data-msg' => sprintf('%sしますか？', term('form.send')),
	)),
	array('icon_term' => 'form.do_delete', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => 'admin/message/delete/'.$message->id,
		'data-msg' => '削除します。よろしいですか？',
	)),
);
echo btn_dropdown('form.edit', $menus, true, null, null, true, array('class' => 'edit'));
?>
<?php endif; ?>

