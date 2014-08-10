<?php echo render('_parts/member_contents_box', array(
	'member'      => $album->member,
	'id'          => $album->id,
	'public_flag' => $album->public_flag,
	'have_children_public_flag'  => true,
	'public_flag_disabled_to_update' => $disabled_to_update,
	'is_refresh_after_update_public_flag' => true,
	'model'       => 'album',
	'child_model' => 'album_image',
	'date'        => array('datetime' => $album->created_at, 'label' => term('site.datetime'))
)); ?>
<?php if (!$disabled_to_update && isset($u) && $u->id == $album->member_id): ?>
<?php
$menus = array(
	array('icon_term' => 'form.do_edit', 'href' => 'album/edit/'.$album->id),
	array('icon_term' => 'form.do_delete', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => 'album/delete/'.$album->id,
		'data-msg' => '削除します。よろしいですか。',
	)),
);
echo btn_dropdown('form.edit', $menus, true, null, null, true, array('class' => 'edit'));
?>
<?php endif; ?>
