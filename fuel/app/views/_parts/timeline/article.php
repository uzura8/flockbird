<?php
list($list, $is_all_records, $all_comment_count) = \Timeline\Model_TimelineComment::get_comments($timeline->id, \Config::get('timeline.articles.comment.limit'));
$comment = array(
	'list' => $list,
	'is_all_records' => $is_all_records,
	'all_comment_count' => $all_comment_count,
	'parent_obj' => $timeline,
);
?>
<div class="timelineBox" id="timelineBox_<?php echo $timeline->id; ?>" data-id="<?php echo $timeline->id; ?>">

<?php
$data = array(
	'member'  => $timeline_data->member,
	'size'    => 'M',
	'date'    => array('datetime' => $timeline->created_at),
	'content' => \Timeline\Site_Util::get_timeline_body($timeline_data->type, $timeline_data->body),
	'images'  => \Timeline\Site_Util::get_timeline_images($timeline_data->type, $timeline_data->foreign_table, $timeline_data->foreign_id),
	'comment' => $comment,
	'model'   => 'timeline',
	'id'      => $timeline->id,
	'public_flag' => $timeline->public_flag,
	'public_flag_view_icon_only' => IS_SP,
);
if (!empty($is_convert_nl2br)) $data['is_convert_nl2br'] = $is_convert_nl2br;
if (!empty($trim_width)) $data['trim_width'] = $trim_width;
if (!empty($truncate_lines))
{
	$data['truncate_lines'] = $truncate_lines;
	$data['read_more_uri']  = 'timeline/'.$timeline->id;
}
$view_member_contents_box = View::forge('_parts/member_contents_box', $data);

$quote_article = \Timeline\Site_Util::get_quote_article($timeline_data->type, $timeline_data->foreign_table, $timeline_data->foreign_id);
if ($quote_article) $view_member_contents_box->set_safe('quote_article', $quote_article);

echo $view_member_contents_box->render();

if (Auth::check() && $timeline->member_id == $u->id && \Timeline\Site_Util::check_is_editable($timeline_data->type))
{
	$attr = array(
		'id'      => 'btn_timeline_delete_'.$timeline->id,
		'data-id' => $timeline->id,
	);
	if (!empty($delete_uri)) $attr['data-uri'] = $delete_uri;
	echo anchor_button('#', 'ls-icon-delete', '', 'boxBtn btn_timeline_delete', $attr, true, IS_SP);
}
?>
</div><!-- timelineBox -->
