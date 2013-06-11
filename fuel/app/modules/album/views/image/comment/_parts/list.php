<?php echo Html::doctype('html5'); ?>
<?php if ($show_more_link && count($comments) < \Album\Model_AlbumImageComment::get_count4album_image_id($album_image->id)): ?>
<a href="#" class="listMoreBox" id="listMoreBox_comment">全てみる</a>
<?php endif; ?>

<?php foreach ($comments as $comment): ?>
	<div class="commentBox" id="commentBox_<?php echo $comment->id; ?>">
		<div class="member_img_box_s">
			<?php echo (empty($comment->member_id))? img('m', '30x30') : img($comment->member->get_image(), '30x30', 'member/'.$comment->member_id); ?>
			<div class="content">
				<div class="main">
					<b class="fullname"><?php echo (empty($comment->member_id))? Config::get('site.term.left_member') : Html::anchor('member/'.$comment->member_id, $comment->member->name); ?></b>
					<?php echo $comment->body ?>
				</div>
				<small><?php echo site_get_time($comment->created_at); ?></small>
			</div>
		</div>

<?php if (isset($u) && in_array($u->id, array($comment->member_id, $album_image->album->member_id))): ?>
	<a class="btn btn-mini boxBtn btn_album_image_comment_delete" id="btn_album_image_comment_delete_<?php echo $comment->id ?>" href="#"><i class="icon-trash"></i></a>
<?php endif ; ?>
	</div>
<?php endforeach; ?>
