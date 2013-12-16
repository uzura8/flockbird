<?php
$date = isset($album_image->file->shot_at) ? $album_image->file->shot_at : $album_image->created_at;
echo render('_parts/member_contents_box', array(
	'member'      => $album_image->album->member,
	'id'          => $album_image->id,
	'public_flag' => $album_image->public_flag,
	'public_flag_view_icon_only' => IS_SP,
	'public_flag_disabled_to_update' => \Album\Site_Util::check_album_disabled_to_update($album_image->album->foreign_table),
	'model'       => 'album_image',
	'date'        => array('datetime' => $date, 'label' => '撮影')
)); ?>
<?php if (isset($u) && $u->id == $album_image->album->member_id): ?>
<div class="edit btn-group" data-toggle="dropdown">
	<?php echo render('_parts/button_edit'); ?>
	<ul class="dropdown-menu pull-right">
		<li><?php echo Html::anchor('album/image/edit/'.$album_image->id, '<i class="icon-pencil"></i> 編集'); ?></li>
<?php if ($album_image->album->cover_album_image_id == $album_image->id): ?>
		<li><span class="disabled"><i class="icon-book"></i> カバーに設定済み</span></li>
<?php else: ?>
		<li><a href="#" class="link_album_image_set_cover" id="link_album_image_set_cover_<?php echo $album_image->id; ?>"><i class="icon-book"></i> カバーに指定</a></li>
<?php endif; ?>
		<li><a href="#" onclick="delete_item('album/image/delete/<?php echo $album_image->id; ?>');return false;"><i class="icon-trash"></i> 削除</a></li>
	</ul>
</div><!-- /btn-group -->
<?php endif; ?>
