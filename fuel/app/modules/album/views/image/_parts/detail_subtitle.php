<?php
$date = isset($album_image->shot_at) ? $album_image->shot_at : $album_image->created_at;
echo render('_parts/member_contents_box', array(
	'member'      => $album_image->album->member,
	'id'          => $album_image->id,
	'public_flag' => $album_image->public_flag,
	'public_flag_disabled_to_update' => \Album\Site_Util::check_album_disabled_to_update($album_image->album->foreign_table),
	'model'       => 'album_image',
	'date'        => array('datetime' => $date, 'label' => '撮影')
)); ?>
<?php if (isset($u) && $u->id == $album_image->album->member_id): ?>
<?php
$menus = array(array('icon_term' => 'form.do_edit', 'href' => 'album/image/edit/'.$album_image->id));
if ($album_image->album->cover_album_image_id == $album_image->id)
{
	$menus[] = array('tag' => 'disabled', 'icon_term' => 'form.set_cover');
}
else
{
	$menus[] = array('icon_term' => 'form.set_cover', 'attr' => array(
		'class' => 'link_album_image_set_cover',
		'id' => 'link_album_image_set_cover_'.$album_image->id,
	));
}
$menus[] = array('icon_term' => 'form.do_delete', 'attr' => array(
	'class' => 'js-simplePost',
	'data-uri' => 'album/image/delete/'.$album_image->id,
));
echo btn_dropdown('form.edit', $menus, true, null, null, true, array('class' => 'edit'));
?>
<?php endif; ?>
