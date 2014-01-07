<?php if (Site_Util::check_is_api_request()): ?><?php echo Html::doctype('html5'); ?><?php endif; ?>
<?php
$list_more_box_attrs_def = array('class' => 'listMoreBox', 'id' => 'listMoreBox_comment');
$list_more_box_attrs     = empty($list_more_box_attrs) ? $list_more_box_attrs_def : array_merge($list_more_box_attrs_def, $list_more_box_attrs);
?>
<?php if (!$is_all_records): ?>
<?php echo Html::anchor(isset($uri_for_all_comments) ? $uri_for_all_comments : '#', 'もっと見る', $list_more_box_attrs); ?>
<?php endif; ?>

<?php foreach ($comments as $comment): ?>
<div class="commentBox<?php if ($parent || !empty($class_id)): ?> commentBox_<?php echo isset($class_id) ? $class_id : $parent->id; ?><?php endif; ?>" id="commentBox_<?php echo $comment->id; ?>">
<?php echo render('_parts/member_contents_box', array(
	'member' => $comment->member,
	'date' => array('datetime' => $comment->created_at),
	'content' => $comment->body,
	'trim_width' => empty($trim_width) ? 0 : $trim_width,
)); ?>
<?php if (isset($u) && in_array($u->id, array($comment->member_id, $parent->member_id))): ?>
<a class="btn btn-default btn-xs boxBtn btn_comment_delete" id="btn_comment_delete_<?php echo $comment->id ?>" href="#"><i class="icon-trash"></i></a>
<?php endif ; ?>
</div>
<?php endforeach; ?>
