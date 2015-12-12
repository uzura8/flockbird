<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><?php endif; ?>
<?php if (!$list): ?>
<?php if (!IS_API) echo \Message\Site_Util::get_no_data_talks(); ?>
<?php else: ?>
<?php 	if (!empty($next_id)): ?>
<?php
$list_more_box_attrs_def = array(
	'class' => 'listMoreBox',
	'id' => 'listMoreBox_comment',
	'data-list' => '#comment_list',
	'data-uri' => $get_uri,
);
if (empty($uri_for_all_comments)) $list_more_box_attrs_def['class'] .=' js-ajax-loadList';
$get_data_list = array('max_id' => $next_id);
if (!empty($since_id)) $get_data_list['since_id'] = $since_id;
$list_more_box_attrs_def['data-get_data'] = json_encode($get_data_list);
$list_more_box_attrs = empty($list_more_box_attrs) ? $list_more_box_attrs_def : array_merge($list_more_box_attrs_def, $list_more_box_attrs);
?>
<?php echo Html::anchor(isset($uri_for_all_comments) ? $uri_for_all_comments : '#', term('site.see_more'), $list_more_box_attrs); ?>
<?php 	endif; ?>
<?php 	foreach ($list as $message_sent): ?>
<?php
$box_attrs = array(
	'class' => 'js-hide-btn commentBox',
	'id' => 'commentBox_'.$message_sent->id,
	'data-id' => $message_sent->id,
	'data-hidden_btn' => 'btn_comment_delete_'.$message_sent->id,
	'data-auther_id' => $message_sent->message->member_id,
);
?>
<div <?php echo Util_Array::conv_array2attr_string($box_attrs); ?>>
<?php
$data = array(
	'member' => $message_sent->message->member,
	'date' => array('datetime' => $message_sent->created_at),
	'is_output_raw_content' => true,
);
if (!empty($image_size)) $data['size'] = $image_size;
if ($message_sent->message->member_id == get_uid())
{
	$data['unread_flag'] = $unread_message_ids && in_array($message_sent->message_id, $unread_message_ids);
}
//if (empty($is_hide_reply_link) && conf('mention.isEnabled', 'notice') && $message_sent->message->member)
//{
//	$data['reply_link'] = array(
//		'id' => $message_sent->id,
//		'target_id' => $parent->id,
//		'member_name' => member_name($message_sent->message->member),
//	);
//}
$content_view = View::forge('_parts/member_contents_box', $data);
$content_view->set_safe('content', convert_body($message_sent->message->body, array(
	'nl2br' => isset($nl2br) ? $nl2br : view_params('nl2br', 'message'),
	'is_truncate' => false,
	//'truncate_width' => empty($is_detail) ? conf('view_params_default.list.comment.trim_width') : null,
	'mention2link' => false,
)));
echo $content_view->render();
?>
<div>
</div>
<?php
$is_display_delete_btn = false;
if (!empty($absolute_display_delete_btn))
{
	$is_display_delete_btn = true;
}
elseif (\Auth::check())
{
	if (!isset($auther_member_ids)) $auther_member_ids = array();
	$auther_member_ids[] = $message_sent->message->member_id;
//	if (isset($parent->member_id)) $auther_member_ids[] = $parent->member_id;
	if (in_array($u->id, $auther_member_ids)) $is_display_delete_btn = true;
}
?>
<?php if ($is_display_delete_btn): ?>
<?php /*echo render('_parts/btn_delete', array(
	'id' => $message_sent->id,
	'attr_id' => 'btn_comment_delete_'.$message_sent->id,
	'parrent_attr_id' => 'commentBox_'.$message_sent->id,
	//'delete_uri' => sprintf('message/api/delete/%d.html', $message_sent->message_id),
));*/ ?>
<?php endif ; ?>
</div>
<?php 	endforeach; ?>
<?php endif; ?>

