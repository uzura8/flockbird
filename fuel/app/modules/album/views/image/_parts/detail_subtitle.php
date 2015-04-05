<?php
$date = isset($album_image->shot_at) ? $album_image->shot_at : $album_image->created_at;
echo render('_parts/member_contents_box', array(
	'member'      => $album_image->album->member,
	'id'          => $album_image->id,
	'public_flag' => $album_image->public_flag,
	'public_flag_disabled_to_update' => \Album\Site_Util::check_album_disabled_to_update($album_image->album->foreign_table),
	'model'       => 'album_image',
	'date'        => array('datetime' => $date, 'label' => term('site.shot'))
)); ?>

<?php
$dropdown_btn_group_attr = array(
	'id' => 'btn_dropdown_'.$album_image->id,
	'class' => array('dropdown', 'boxBtn', 'edit'),
);
$get_uri = sprintf('album/image/api/menu/%d.html', $album_image->id);
$dropdown_btn_attr = array(
	'class' => 'js-dropdown_content_menu',
	'data-uri' => sprintf('album/image/api/menu/%d.html?is_detail=1', $album_image->id),
	'data-member_id' => $album_image->album->member_id,
	'data-menu' => '#menu_'.$album_image->id,
	'data-loaded' => 0,
);
$menus = array();
echo btn_dropdown('noterm.dropdown', $menus, false, 'xs', null, true, $dropdown_btn_group_attr, $dropdown_btn_attr, false);
?>
