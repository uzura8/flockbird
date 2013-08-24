<?php echo render('_parts/member_contents_box', array(
	'member'      => $album->member,
	'id'          => $album->id,
	'public_flag' => $album->public_flag,
	'have_children_public_flag'  => true,
	'public_flag_view_icon_only' => IS_SP,
	'public_flag_disabled_to_update' => $disabled_to_update,
	'is_refresh_after_update_public_flag' => true,
	'model'       => 'album',
	'child_model' => 'album_image',
	'date'        => array('datetime' => $album->created_at, 'label' => '日時')
)); ?>
<?php if (!$disabled_to_update && isset($u) && $u->id == $album->member_id): ?>
<div class="btn-group edit">
	<?php echo render('_parts/button_edit'); ?>
	<ul class="dropdown-menu pull-right">
		<li><?php echo Html::anchor('album/edit/'.$album->id, '<i class="icon-pencil"></i> 編集'); ?></li>
		<li><a href="#" onclick="delete_item('album/delete/<?php echo $album->id; ?>');return false;"><i class="icon-trash"></i> 削除</a></li>
	</ul>
</div><!-- /btn-group -->
<?php endif; ?>
