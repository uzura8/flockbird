<?php $is_api_request = Site_Util::check_is_api_request(); ?>
<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><?php endif; ?>
<?php if (!$list): ?>
<?php if (!$is_api_request): ?><?php echo \Config::get('album.term.album_image'); ?>がありません。<?php endif; ?>
<?php else: ?>
<div id="main_container">
<?php $i = 0; ?>
<?php foreach ($list as $album_image): ?>
	<div class="main_item" id="main_item_<?php echo $album_image->id; ?>">
		<div class="imgBox" id="imgBox_<?php echo $album_image->id ?>"<?php if (!Agent::is_smartphone()): ?> onmouseover="$('#btn_album_image_edit_<?php echo $album_image->id ?>').show();" onmouseout="$('#btn_album_image_edit_<?php echo $album_image->id ?>').hide();"<?php endif; ?>>
			<div><?php echo img($album_image->file->name, img_size('ai', 'M'), 'album/image/'.$album_image->id); ?></div>
			<h5><?php echo Html::anchor('album/image/'.$album_image->id, \Album\Site_Util::get_album_image_display_name($album_image)); ?></h5>
			<div class="article">
<?php list($album_image_comment, $is_all_records, $all_comment_count) = \Album\Model_AlbumImageComment::get_comments($album_image->id, \Config::get('site.record_limit.default.comment.s')); ?>
			<div class="comment_info">
				<small><i class="icon-comment"></i> <?php echo $all_comment_count; ?></small>
				<small><?php echo Html::anchor('album/image/'.$album_image->id.'?write_comment=1#comments', 'コメントする'); ?></small>
			</div>
<?php if (Auth::check() && $album->member_id == $u->id): ?>
				<div class="btn-group btn_album_image_edit" id="btn_album_image_edit_<?php echo $album_image->id ?>">
					<button data-toggle="dropdown" class="btn btn-mini dropdown-toggle"><i class="icon-edit"></i></button>
					<ul class="dropdown-menu pull-right">
						<li><?php echo Html::anchor('album/image/edit/'.$album_image->id, '<i class="icon-pencil"></i> 編集'); ?></li>
						<li><a href="#" class="link_album_image_set_cover" id="link_album_image_set_cover_<?php echo $album_image->id; ?>"><i class="icon-book"></i> カバーに指定</a></li>
						<li><a href="#" onclick="delete_item('album/image/api/delete.json', <?php echo $album_image->id; ?>, '#main_item');return false;"><i class="icon-trash"></i> 削除</a></li>
					</ul>
				</div><!-- /btn-group -->
<?php endif; ?>
			</div>
		</div>

<?php if ($album_image_comment): ?>
		<div class="list_album_image_comment">
		<?php echo render('_parts/comment/list', array('parent' => $album, 'comments' => $album_image_comment, 'is_all_records' => $is_all_records)); ?>
<?php if (!$is_all_records): ?>
			<div class="listMoreBox"><a href="<?php echo Uri::create(sprintf('album/image/%d?all_comment=1#comments', $album_image->id)); ?>">もっと見る</a></div>
<?php endif; ?>
		</div>
<?php endif; ?>
	</div>
<?php $i++; ?>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($is_next): ?>
<nav id="page-nav">
	<a href="<?php echo Uri::create(sprintf('album/image/api/list/%d.html?page=%d', $album->id, $page + 1)); ?>"></a>
</nav>
<?php endif; ?>
