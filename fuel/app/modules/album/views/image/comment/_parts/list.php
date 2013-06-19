<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><?php endif; ?>
<?php if ($show_more_link && count($comments) < \Album\Model_AlbumImageComment::get_count4album_image_id($album_image->id)): ?>
<a href="#" class="listMoreBox" id="listMoreBox_comment">全てみる</a>
<?php endif; ?>

<?php foreach ($comments as $comment): ?>
<div class="commentBox" id="commentBox_<?php echo $comment->id; ?>">
<?php echo render('_parts/member_contents_box', array('member' => $comment->member, 'date' => array('datetime' => $comment->created_at), 'content' => $comment->body)); ?>
<?php if (isset($u) && in_array($u->id, array($comment->member_id, $album_image->album->member_id))): ?>
<a class="btn btn-mini boxBtn btn_album_image_comment_delete" id="btn_album_image_comment_delete_<?php echo $comment->id ?>" href="#"><i class="icon-trash"></i></a>
<?php endif ; ?>
</div>
<?php endforeach; ?>
