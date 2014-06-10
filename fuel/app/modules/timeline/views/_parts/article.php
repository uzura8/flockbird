<?php
list($list, $is_all_records, $all_comment_count) = \Timeline\Site_Model::get_comments($timeline->type, $timeline->id, $timeline->foreign_id);
$comment = array(
	'list' => $list,
	'is_all_records' => $is_all_records,
	'all_comment_count' => $all_comment_count,
	'parent_obj' => $timeline,
);
?>
<div class="timelineBox" id="timelineBox_<?php echo $timeline_cache_id; ?>" data-id="<?php echo $timeline->id; ?>">

<?php
$comment_get_uri = \Timeline\Site_Util::get_comment_api_uri('get', $timeline->type, $timeline->foreign_table, $timeline->id, $timeline->foreign_id);
$images = \Timeline\Site_Util::get_timeline_images(
	$timeline->type,
	$timeline->foreign_id,
	$timeline->id,
	$timeline->member_id,
	$self_member_id
);
$public_flag_info = \Timeline\Site_Util::get_public_flag_info($timeline);
$data = array(
	'member'  => $timeline->member,
	'size'    => 'M',
	'date'    => array('datetime' => $timeline->created_at),
	'images'  => $images,
	'comment' => $comment,
	'id'      => $timeline->id,
	'model'   => $public_flag_info['model'],
	'public_flag' => $timeline->public_flag,
	'public_flag_target_id'  => $public_flag_info['public_flag_target_id'],
	'have_children_public_flag' => $public_flag_info['have_children_public_flag'],
	'child_model' => $public_flag_info['child_model'],
	'public_flag_disabled_to_update' => $public_flag_info['disabled_to_update'],
	'list_more_box_attrs' => array('data-get_uri' => $comment_get_uri),
	'post_comment_button_attrs' => array(
		'data-get_uri' => $comment_get_uri,
		'data-post_parent_id' => \Timeline\Site_Util::get_comment_parent_id($timeline->type, $timeline->id, $timeline->foreign_id),
		'data-post_uri' => \Timeline\Site_Util::get_comment_api_uri('create', $timeline->type, $timeline->foreign_table),
	),
	'comment_delete_uri' => \Timeline\Site_Util::get_comment_api_uri('delete', $timeline->type, $timeline->id, $timeline->foreign_id, $timeline->foreign_table),
);

if (!empty($is_convert_nl2br)) $data['is_convert_nl2br'] = $is_convert_nl2br;
if (!empty($trim_width)) $data['trim_width'] = $trim_width;
if (!empty($truncate_lines))
{
	$data['truncate_lines'] = $truncate_lines;
	$data['read_more_uri']  = 'timeline/'.$timeline->id;
}

$view_member_contents_box = View::forge('_parts/member_contents_box', $data);

$foreign_table_obj = \Timeline\Site_Model::get_foreign_table_obj($timeline->type, $timeline->foreign_id);
$optional_info = array();
if (isset($images['count'])) $optional_info['count'] = $images['count'];
if (isset($images['count_all'])) $optional_info['count_all'] = $images['count_all'];
list($content, $is_safe_content) = \Timeline\Site_Util::get_timeline_body(
	$timeline->type,
	$timeline->body,
	$foreign_table_obj,
	$optional_info
);
$method = $is_safe_content ? 'set_safe' : 'set';
$view_member_contents_box->$method('content', $content);
$view_member_contents_box->set('is_output_raw_content', $is_safe_content);

$quote_article = '';
if ($foreign_table_obj || $timeline->type == \Config::get('timeline.types.album_image'))
{
	$quote_obj = ($timeline->type == \Config::get('timeline.types.album_image')) ? $timeline : $foreign_table_obj;
	$quote_article = \Timeline\Site_Util::get_quote_article($timeline->type, $quote_obj);
	unset($quote_obj);
}
if ($quote_article) $view_member_contents_box->set_safe('quote_article', $quote_article);

echo $view_member_contents_box->render();

if ($self_member_id && $timeline->member_id == $self_member_id && \Timeline\Site_Util::check_is_editable($timeline->type))
{
	list($post_id, $post_uri) = \Timeline\Site_Util::get_delete_api_info($timeline);
	$attr = array(
		'class'        => 'boxBtn',
		'id'           => 'btn_timeline_delete_'.$timeline_cache_id,
		'data-id'      => $timeline->id,
		'data-post_id' => $post_id,
		'data-uri'     => $post_uri,
	);
	echo btn('form.delete', '#', 'btn_timeline_delete', false, 'xs', 'default', $attr);
}
?>
</div><!-- timelineBox -->
