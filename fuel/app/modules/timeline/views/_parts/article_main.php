<?php
$is_auth = ($access_from_member_relation == 'others') ? false : true;
$images = \Timeline\Site_Util::get_timeline_images(
	$timeline->type,
	$timeline->foreign_id,
	$timeline->id,
	$access_from_member_relation
);
?>

<?php /* content */; ?>
<?php
$optional_info = array();
if (isset($images['count'])) $optional_info['count'] = $images['count'];
if (isset($images['count_all'])) $optional_info['count_all'] = $images['count_all'];
$foreign_table_obj = \Timeline\Site_Model::get_foreign_table_obj($timeline->type, $timeline->foreign_id);
echo \Timeline\Site_Util::get_timeline_content($timeline, $foreign_table_obj, $optional_info);
?>


<?php /* quote_article */; ?>
<?php
if ($foreign_table_obj || $timeline->type == \Config::get('timeline.types.album_image'))
{
	$quote_obj = ($timeline->type == \Config::get('timeline.types.album_image')) ? $timeline : $foreign_table_obj;
	$quote_article = \Timeline\Site_Util::get_quote_article($timeline->type, $quote_obj);
	echo $quote_article;
}
?>


<?php /* images */; ?>
<?php if (!empty($images['list'])): ?>
<div class="thumbnails">
<?php echo render('_parts/thumbnails', array('images' => $images)); ?>
</div>
<?php endif; ?>


<?php /* time & public_flag */; ?>
		<div class="sub_info">
			<small><?php echo site_get_time($timeline->created_at) ?></small>
<?php
$public_flag_info = \Timeline\Site_Util::get_public_flag_info($timeline);
$data = array(
	'model' => $public_flag_info['model'],
	'id' => $timeline->id,
	'public_flag' => $timeline->public_flag,
	'view_icon_only' => false,
	'have_children_public_flag' => $public_flag_info['have_children_public_flag'],
	'disabled_to_update' => $public_flag_info['disabled_to_update'],
	'is_mycontents' => ($access_from_member_relation == 'self'),
);
if (!empty($child_model)) $data['child_model'] = $child_model;
echo render('_parts/public_flag_selecter', $data);
?>
		</div><!-- sub_info -->


<?php /* comment_list */; ?>
<?php
list($list, $is_all_records, $all_comment_count) = \Timeline\Site_Model::get_comments($timeline->type, $timeline->id, $timeline->foreign_id);
$comment_get_uri = \Timeline\Site_Util::get_comment_api_uri('get', $timeline->type, $timeline->foreign_table, $timeline->id, $timeline->foreign_id);
$comment_post_uri = \Timeline\Site_Util::get_comment_api_uri('create', $timeline->type, $timeline->foreign_table, $timeline->id, $timeline->foreign_id);
$comment_delete_uri = \Timeline\Site_Util::get_comment_api_uri('delete', $timeline->type, $timeline->foreign_table, $timeline->id, $timeline->foreign_id);
?>
<?php $parent = $timeline; ?>
<div class="comment_info">
	<small><?php echo icon('comment'); ?> <span id="comment_count_<?php echo $parent->id; ?>"><?php echo $all_comment_count; ?><span></small>
<?php if ($is_auth): ?>
<?php
$link_comment_attr = array(
	'class' => 'link_comment',
	'data-id' => $parent->id,
	'data-block' => 'form_comment_'.$timeline->id,
	'data-post_uri' => $comment_post_uri,
	'data-get_uri' => $comment_get_uri,
);
?>
	<small><?php echo anchor('#', term('form.do_comment'), false, $link_comment_attr); ?></small>
<?php endif; ?>
</div>
<?php if ($list): ?>
<div id="comment_list_<?php echo $parent->id; ?>">
<?php
$list_more_box_attrs = array('id' => 'listMoreBox_comment_'.$parent->id, 'data-parent_id' => $parent->id, 'data-uri' => $comment_get_uri, 'data-is_before' => 1);
$data = array(
	'parent' => $parent,
	'comments' => $list,
	'is_all_records' => $is_all_records,
	'list_more_box_attrs' => $list_more_box_attrs,
	'delete_uri' => $comment_delete_uri,
	'absolute_display_delete_btn' => $is_auth,
);
echo render('_parts/comment/list', $data);
?>
</div>

<?php /* post_comment */; ?>
<?php if ($is_auth): ?>
<?php
$link_comment_attr['id'] = 'link_comment_box_'.$timeline->id;
$link_comment_attr['class'] .= ' showCommentBox';
?>
<?php echo anchor('#', term('form.do_comment'), false, $link_comment_attr); ?>
<?php endif; ?>
<?php endif; ?>
<div id="comment_list_<?php echo $timeline->id; ?>"></div>
<div id="form_comment_<?php echo $timeline->id; ?>"></div>


<?php /* edit_button */; ?>
<?php
if ($access_from_member_relation  == 'self' && \Timeline\Site_Util::check_is_editable($timeline->type))
{
	list($post_id, $post_uri) = \Timeline\Site_Util::get_delete_api_info($timeline);
	echo render('_parts/btn_delete', array(
		'id' => $post_id,
		'attr_id' => 'btn_timeline_delete_'.$timeline->id,
		'parrent_attr_id' => 'timelineBox_'.$timeline->id,
		'delete_uri' => $post_uri,
	));
}
?>

