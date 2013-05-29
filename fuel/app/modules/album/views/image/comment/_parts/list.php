<?php if (!empty($show_more_link) && count($comments) < count($album_image->album_image_comment)): ?>
<div class="listMoreBox" id="listMoreBox_comment"><a href="#" id="link_list_more">全てみる</a></div>
<?php endif; ?>

<?php foreach ($comments as $comment): ?>
	<div class="commentBox" id="commentBox_<?php echo $comment->id; ?>">
		<div class="member_img_box_s">
			<?php echo (empty($comment->member))? img('m', '30x30') : img($comment->member->get_image(), '30x30', 'member/'.$comment->member_id); ?>
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
<?php endif ; ?>
	</div>
<?php endforeach; ?>
