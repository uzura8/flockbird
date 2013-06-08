<?php if ($album_images): ?>
<div id="main_container">
<?php $i = 0; ?>
<?php foreach ($album_images as $album_image): ?>
	<div class="main_item" id="main_item_<?php echo $album_image->id; ?>">
		<div class="imgBox" id="imgBox_<?php echo $album_image->id ?>">
			<div><?php echo img($album_image->file->name, img_size('ai', 'M'), 'album/image/detail/'.$album_image->id); ?></div>
			<h5><?php echo Html::anchor('album/image/detail/'.$album_image->id, \Album\Site_util::get_album_image_display_name($album_image)); ?></h5>
			<div class="article">
				<small><i class="icon-comment"></i> <?php echo $all_comment_count = \Album\Model_AlbumImageComment::get_count4album_image_id($album_image->id); ?></small>
<?php if (Auth::check() && $album_image->album->member_id == $u->id): ?>
				<div class="btn-group btn_album_image_edit" id="btn_album_image_edit_<?php echo $album_image->id ?>">
					<button data-toggle="dropdown" class="btn btn-mini dropdown-toggle"><i class="icon-edit"></i><span class="caret"/></button>
					<ul class="dropdown-menu">
						<li><?php echo Html::anchor('album/image/edit/'.$album_image->id, '<i class="icon-pencil"></i> 編集'); ?></li>
						<li><a href="#" class="link_album_image_set_cover" id="link_album_image_set_cover_<?php echo $album_image->id; ?>"><i class="icon-book"></i> カバーに指定</a></li>
						<li><a href="#" onclick="delete_item('album/image/api/delete.json', <?php echo $album_image->id; ?>, '#main_item');return false;"><i class="icon-trash"></i> 削除</a></li>
					</ul>
				</div><!-- /btn-group -->
<?php endif; ?>
			</div>
		</div>

<?php $album_image_comment = \Album\Model_AlbumImageComment::get_comments($album_image->id, \Config::get('site.record_limit.default.comment.s')); ?>
<?php if ($album_image_comment): ?>
		<div class="list_album_image_comment">
<?php foreach ($album_image_comment as $comment): ?>
			<div class="commentBox" id="commentBox_<?php echo $comment->id ?>">
				<div class="member_img_box_ss">
				<?php echo (empty($comment->member))? img('m', '20x20') : img($comment->member->get_image(), '20x20', 'member/'.$comment->member_id); ?>
					<div class="content">
						<div class="main">
							<b class="fullname"><?php echo (empty($comment->member))? Config::get('site.term.left_member') : Html::anchor('member/'.$comment->member_id, $comment->member->name); ?></b>
							<?php echo $comment->body ?>
						</div>
						<small><?php echo site_get_time($comment->created_at); ?></small>
					</div>
				</div>
<?php if (isset($u) && in_array($u->id, array($comment->member_id, $album_image->album->member_id))): ?>
				<a class="btn btn-mini boxBtn btn_album_image_comment_delete" id="btn_album_image_comment_delete_<?php echo $comment->id ?>" href="#"><i class="icon-trash"></i></a>
<?php endif; ?>
			</div>
<?php endforeach; ?>
<?php if (count($album_image_comment) < $all_comment_count): ?>
			<div class="listMoreBox"><a href="<?php echo Uri::create(sprintf('album/image/detail/%d?all_comment=1#comments', $album_image->id)); ?>">もっと見る</a></div>
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
	<a href="<?php echo Uri::create(sprintf('album/image_list/%d?page=%d', $id, $page + 1)); ?>"><?php if (\Agent::is_robot()): ?>もっと見る<?php endif; ?></a>
</nav>
<?php endif; ?>
