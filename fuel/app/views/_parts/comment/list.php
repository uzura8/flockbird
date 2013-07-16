<?php if (Site_Util::check_is_api_request()): ?><?php echo Html::doctype('html5'); ?><?php endif; ?>
<?php if (!$is_all_records): ?><a href="#" class="listMoreBox" id="listMoreBox_comment">全てみる</a><?php endif; ?>

<?php foreach ($comments as $comment): ?>
<div class="commentBox" id="commentBox_<?php echo $comment->id; ?>">
<?php echo render('_parts/member_contents_box', array(
	'member' => $comment->member,
	'date' => array('datetime' => $comment->created_at),
	'content' => $comment->body,
	'trim_width' => empty($trim_width) ? 0 : $trim_width,
)); ?>
<?php if (isset($u) && in_array($u->id, array($comment->member_id, $parent->member_id))): ?>
<a class="btn btn-mini boxBtn btn_comment_delete" id="btn_comment_delete_<?php echo $comment->id ?>" href="#"><i class="icon-trash"></i></a>
<?php endif ; ?>
</div>
<?php endforeach; ?>
