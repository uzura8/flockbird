<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><?php endif; ?>
<?php if ($list): ?>
<?php 	if (!empty($next_id)): ?>
<?php
$list_more_box_attrs_def = array(
	'class' => 'listMoreBox',
	'id' => 'listMoreBox_comment',
	'data-list' => '#comment_list',
);
if (empty($uri_for_all_comments)) $list_more_box_attrs_def['class'] .=' js-ajax-loadList';
$get_data_list = array('max_id' => $next_id);
if (!empty($since_id)) $get_data_list['since_id'] = $since_id;
$list_more_box_attrs_def['data-get_data'] = json_encode($get_data_list);
$list_more_box_attrs = empty($list_more_box_attrs) ? $list_more_box_attrs_def : array_merge($list_more_box_attrs_def, $list_more_box_attrs);
?>
<?php echo Html::anchor(isset($uri_for_all_comments) ? $uri_for_all_comments : '#', term('site.see_more'), $list_more_box_attrs); ?>
<?php 	endif; ?>
<?php 	foreach ($list as $comment): ?>
<?php
$box_attrs = array(
	'class' => 'js-hide-btn commentBox',
	'id' => 'commentBox_'.$comment->id,
	'data-id' => $comment->id,
	'data-hidden_btn' => 'btn_comment_delete_'.$comment->id,
	'data-auther_id' => $comment->member_id,
);
if ($parent && !empty($parent->member_id)) $box_attrs['data-parent_auther_id'] = $parent->member_id;
if ($parent) $box_attrs['class'] .= sprintf(' commentBox_%d', $parent->id);
?>
<div <?php echo Util_Array::conv_array2attr_string($box_attrs); ?>>
<?php
$data = array(
	'member' => $comment->member,
	'date' => array('datetime' => $comment->created_at),
	'is_output_raw_content' => true,
);
if (conf('like.isEnabled'))
{
	$data['like_link'] = array(
		'id' => $comment->id,
		'post_uri' => \Site_Util::get_api_uri_update_like($like_api_uri_prefix, $comment->id),
		'get_member_uri' => \Site_Util::get_api_uri_get_liked_members($like_api_uri_prefix, $comment->id),
		'count_attr' => array('class' => 'unset_like_count'),
		'count' => $comment->like_count,
		'left_margin' => true,
		'is_liked' => isset($liked_ids) ? in_array($comment->id, $liked_ids) : false,
	);
}
if (empty($trim_width)) $trim_width = empty($is_detail) ? conf('view_params_default.list.comment.trim_width') : null;
$content_view = View::forge('_parts/member_contents_box', $data);
$content_view->set_safe('content', convert_body($comment->body, array('width' => $trim_width, 'is_detail' => !empty($is_detail)), array('url2link', 'mention2link')));
echo $content_view->render();
?>
<?php
$is_display_delete_btn = false;
if (!empty($absolute_display_delete_btn))
{
	$is_display_delete_btn = true;
}
elseif (\Auth::check())
{
	if (!isset($auther_member_ids)) $auther_member_ids = array();
	$auther_member_ids[] = $comment->member_id;
	if (isset($parent->member_id)) $auther_member_ids[] = $parent->member_id;
	if (in_array($u->id, $auther_member_ids)) $is_display_delete_btn = true;
}
?>
<?php if ($is_display_delete_btn): ?>
<?php echo render('_parts/btn_delete', array(
	'id' => $comment->id,
	'attr_id' => 'btn_comment_delete_'.$comment->id,
	'parrent_attr_id' => 'commentBox_'.$comment->id,
	'delete_uri' => (!empty($delete_uri)) ? $delete_uri : '',
	'counter_selector' => (!empty($counter_selector)) ? $counter_selector : '',
)); ?>
<?php endif ; ?>
</div>
<?php 	endforeach; ?>
<?php endif; ?>
