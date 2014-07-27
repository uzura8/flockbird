<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><?php endif; ?>
<?php
$list_more_box_attrs_def = array(
	'class' => 'listMoreBox',
	'id' => 'listMoreBox_comment',
	'data-list' => '#comment_list',
	'data-limit' => conf('view_params_default.list.comment.limit'),
);
if (empty($uri_for_all_comments)) $list_more_box_attrs_def['class'] .=' js-ajax-loadList';
$list_more_box_attrs = empty($list_more_box_attrs) ? $list_more_box_attrs_def : array_merge($list_more_box_attrs_def, $list_more_box_attrs);
?>
<?php if (!$is_all_records): ?>
<?php echo Html::anchor(isset($uri_for_all_comments) ? $uri_for_all_comments : '#', term('site.see_more'), $list_more_box_attrs); ?>
<?php endif; ?>
<?php foreach ($comments as $comment): ?>
<?php
$box_attrs = array(
	'class' => 'js-hide-btn commentBox',
	'id' => 'commentBox_'.$comment->id,
	'data-id' => $comment->id,
	'data-hidden_btn' => 'btn_comment_delete_'.$comment->id,
	'data-auther_id' => $comment->member_id,
);
if ($parent && !empty($parent->member_id)) $box_attrs['data-parent_auther_id'] = $parent->member_id;
if ($parent || !empty($class_id)) $box_attrs['class'] .= sprintf(' commentBox_%d', isset($class_id) ? $class_id : $parent->id);
?>
<div <?php echo Util_Array::conv_array2attr_string($box_attrs); ?>>
<?php echo render('_parts/member_contents_box', array(
	'member' => $comment->member,
	'date' => array('datetime' => $comment->created_at),
	'content' => $comment->body,
	'trim_width' => empty($trim_width) ? 0 : $trim_width,
)); ?>
<?php if (!empty($absolute_display_delete_btn) || (isset($u) && in_array($u->id, array($comment->member_id, $parent->member_id)))): ?>
<?php echo render('_parts/btn_delete', array(
	'id' => $comment->id,
	'attr_id' => 'btn_comment_delete_'.$comment->id,
	'parrent_attr_id' => 'commentBox_'.$comment->id,
	'delete_uri' => (!empty($delete_uri)) ? $delete_uri : '',
	'counter_selector' => (!empty($counter_selector)) ? $counter_selector : '',
)); ?>
<?php endif ; ?>
</div>
<?php endforeach; ?>
