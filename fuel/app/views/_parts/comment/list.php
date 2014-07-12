<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><?php endif; ?>
<?php
$list_more_box_attrs_def = array(
	'class' => 'listMoreBox js-ajax-loadList',
	'id' => 'listMoreBox_comment',
	'data-list' => '#comment_list',
	'data-limit' => conf('view_params_default.list.comment.limit'),
);
$list_more_box_attrs = empty($list_more_box_attrs) ? $list_more_box_attrs_def : array_merge($list_more_box_attrs_def, $list_more_box_attrs);
?>
<?php if (!$is_all_records): ?>
<?php echo Html::anchor(isset($uri_for_all_comments) ? $uri_for_all_comments : '#', 'もっと見る', $list_more_box_attrs); ?>
<?php endif; ?>

<?php foreach ($comments as $comment): ?>
<div class="hide-btn commentBox<?php if ($parent || !empty($class_id)): ?> commentBox_<?php echo isset($class_id) ? $class_id : $parent->id; ?><?php endif; ?>" id="commentBox_<?php echo $comment->id; ?>" data-id="<?php echo $comment->id; ?>" data-hidden_btn="btn_comment_delete_<?php echo $comment->id; ?>">
<?php echo render('_parts/member_contents_box', array(
	'member' => $comment->member,
	'date' => array('datetime' => $comment->created_at),
	'content' => $comment->body,
	'trim_width' => empty($trim_width) ? 0 : $trim_width,
)); ?>
<?php if (!empty($absolute_display_delete_btn) || (isset($u) && in_array($u->id, array($comment->member_id, $parent->member_id)))): ?>
<?php
$attrs = array(
	'class' => 'btn btn-default btn-xs boxBtn js-ajax-delete',
	'id' => 'btn_comment_delete_'.$comment->id,
	'data-id' => $comment->id,
	'data-uri' => 'note/comment/api/delete.json',
	'data-parent' => 'commentBox_'.$comment->id,
);
if (!empty($delete_uri)) $attrs['data-uri'] = $delete_uri;
echo Html::anchor('#', '<span class="glyphicon glyphicon-trash"></span>', $attrs);
?>
<?php endif ; ?>
</div>
<?php endforeach; ?>
