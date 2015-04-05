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
<?php if ((Auth::check() && $u->id != $album->member_id) || (Auth::check() && $u->id == $album->member_id && !$disabled_to_update)): ?>
<?php
$dropdown_btn_group_attr = array(
	'id' => 'btn_dropdown_'.$album->id,
	'class' => array('dropdown', 'boxBtn', 'edit'),
);
$get_uri = sprintf('album/api/menu/%d.html', $album->id);
$dropdown_btn_attr = array(
	'class' => 'js-dropdown_content_menu',
	'data-uri' => sprintf('album/api/menu/%d.html?is_detail=1', $album->id),
	'data-member_id' => $album->member_id,
	'data-menu' => '#menu_'.$album->id,
	'data-loaded' => 0,
);
$menus = array();
echo btn_dropdown('noterm.dropdown', $menus, false, 'xs', null, true, $dropdown_btn_group_attr, $dropdown_btn_attr, false);
?>
<?php endif; ?>
