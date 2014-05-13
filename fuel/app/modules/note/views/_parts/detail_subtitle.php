<?php echo render('_parts/member_contents_box', array(
	'member'      => $note->member,
	'id'          => $note->id,
	'public_flag' => $note->public_flag,
	'public_flag_view_icon_only' => IS_SP,
	'model'       => 'note',
	'size' => 'M',
	'date'        => array('datetime' => $note->published_at ? $note->published_at : $note->updated_at, 'label' => $note->published_at ? '日時' : '更新日時')
)); ?>

<?php if (isset($u) && $u->id == $note->member_id): ?>
<?php
$menus = array(array('icon_term' => 'form.do_edit', 'href' => 'note/edit/'.$note->id));
if (!$note->is_published)
{
	$menus[] = array('icon_term' => 'form.do_publish', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => 'note/publish/'.$note->id,
	));
}
$menus[] = array('icon_term' => 'form.do_delete', 'attr' => array(
	'class' => 'js-simplePost',
	'data-uri' => 'note/delete/'.$note->id,
));
echo btn_dropdown('form.edit', $menus, true, null, null, true, array('class' => 'edit'));
?>
<?php endif; ?>

