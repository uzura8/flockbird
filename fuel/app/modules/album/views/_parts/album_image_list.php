<?php if ($album_images): ?>
<div id="ai_container">
<?php $i = 0; ?>
<?php foreach ($album_images as $album_image): ?>
	<div class="ai_item">
		<div class="imgBox" id="imgBox_<?php echo $album_image->id ?>">
			<div><?php echo img($album_image->file->name, img_size('ai', 'M'), 'album/image/detail/'.$album_image->id); ?></div>
			<h5><?php echo Html::anchor('album/image/detail/'.$album_image->id, \Album\Site_util::get_album_image_display_name($album_image)); ?></h5>
			<div class="article btn-toolbar">
				<small><i class="icon-comment"></i> <?php echo $comment_count = \Album\Model_AlbumImageComment::get_count4album_image_id($album_image->id); ?></small>
<?php if (Auth::check() && $album_image->album->member_id == $u->id): ?>
				<div class="btn-group btn_album_image_edit" id="btn_album_image_edit_<?php echo $album_image->id ?>">
					<button data-toggle="dropdown" class="btn btn-mini dropdown-toggle"><i class="icon-edit"></i><span class="caret"/></button>
					<ul class="dropdown-menu">
						<li><?php echo Html::anchor('album/image/edit/'.$album_image->id, '<i class="icon-pencil"></i> 編集'); ?></li>
						<li><a href="javascript:void(0);" onclick="set_cover(<?php echo $album_image->id; ?>);"><i class="icon-book"></i> カバーに指定</a></li>
						<li><a href="javascript:void(0);" onclick="jConfirm('削除しますか？', 'Confirmation', function(r){if(r) location.href='<?php echo Uri::create(sprintf('album/image/delete/%d?%s=%s', $album_image->id, Config::get('security.csrf_token_key'), Util_security::get_csrf())); ?>';});"><i class="icon-trash"></i> 削除</a></li>
					</ul>
				</div><!-- /btn-group -->
<?php endif; ?>
			</div>
		</div>

<?php if (isset($album_image->album_image_comment)): ?>
		<div class="list_album_image_comment">
<?php foreach ($album_image->album_image_comment as $comment): ?>
			<div class="commentBox" id="commentBox_<?php echo $comment->id ?>">
				<div class="member_img_box_ss">
					<?php echo img($comment->member->get_image(), '20x20', 'member/'.$comment->member_id); ?>
					<div class="content">
						<div class="main">
							<b class="fullname"><?php echo Html::anchor('member/'.$comment->member_id, $comment->member->name); ?></b>
							<?php echo $comment->body ?>
						</div>
						<small><?php echo site_get_time($comment->created_at); ?></small>
					</div>
				</div>
<?php if (isset($u) && in_array($u->id, array($comment->member_id, $album_image->album->member_id))): ?>
				<a class="btn btn-mini boxBtn btn_album_image_comment_delete" id="btn_album_image_comment_delete_<?php echo $comment->id ?>" href="javascript:void(0);"><i class="icon-trash"></i></a>
<?php endif; ?>
			</div>
<?php endforeach; ?>
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
