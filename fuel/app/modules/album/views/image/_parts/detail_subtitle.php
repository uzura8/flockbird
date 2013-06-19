<?php
$date = isset($album_image->file->shot_at) ? $album_image->file->shot_at : $album_image->created_at;
echo render('_parts/member_contents_box', array('member' => $album_image->album->member, 'date' => array('datetime' => $date, 'label' => '撮影日時')));
?>
<?php if (isset($u) && $u->id == $album_image->album->member_id): ?>
<div class="btn-group">
	<button data-toggle="dropdown" class="btn dropdown-toggle"><i class="icon-edit"></i> edit <span class="caret"/></button>
	<ul class="dropdown-menu">
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
