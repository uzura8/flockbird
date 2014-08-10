<?php
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
echo \Timeline\Site_Util::get_timeline_content($timeline, $foreign_table_obj, $optional_info, $is_detail);
?>


<?php /* quote_article */; ?>
<?php
if ($foreign_table_obj || $timeline->type == \Config::get('timeline.types.album_image'))
{
	$quote_obj = ($timeline->type == \Config::get('timeline.types.album_image')) ? $timeline : $foreign_table_obj;
	$quote_article = \Timeline\Site_Util::get_quote_article($timeline->type, $quote_obj, $is_detail);
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
	'id' => $public_flag_info['public_flag_target_id'],
	'public_flag' => $timeline->public_flag,
	'view_icon_only' => false,
	'have_children_public_flag' => $public_flag_info['have_children_public_flag'],
	'disabled_to_update' => $public_flag_info['disabled_to_update'],
	'use_in_cache' => true,
	'member_id' => $timeline->member_id,
);
if (!empty($child_model)) $data['child_model'] = $child_model;
echo render('_parts/public_flag_selecter', $data);
?>
		</div><!-- sub_info -->


<?php /* comment_list */; ?>
<?php
$link_comment_attr = array(
	'class' => 'link_comment',
	'id' => 'link_show_comment_'.$timeline->id,
	'data-id' => $timeline->id,
);
?>
<div class="comment_info">
	<small><?php echo icon('comment'); ?> <span id="comment_count_<?php echo $timeline->id; ?>"><span></small>
	<small><?php echo anchor('#', term('form.comment'), false, $link_comment_attr); ?></small>
</div>
<?php
$comment_get_uri = \Timeline\Site_Util::get_comment_api_uri('get', $timeline->type, $timeline->foreign_table, $timeline->id, $timeline->foreign_id);
$comment_post_uri = \Timeline\Site_Util::get_comment_api_uri('create', $timeline->type, $timeline->foreign_table, $timeline->id, $timeline->foreign_id);
$comment_delete_uri = \Timeline\Site_Util::get_comment_api_uri('delete', $timeline->type, $timeline->foreign_table, $timeline->id, $timeline->foreign_id);
$comment_list_attr = array(
	'class' => 'comment_list unloade_comments',
	'id' => 'comment_list_'.$timeline->id,
	'data-id' => $timeline->id,
	'data-post_uri' => $comment_post_uri,
	'data-get_uri' => $comment_get_uri,
);
?>
<div <?php echo Util_Array::conv_array2attr_string($comment_list_attr); ?>></div>

<?php
$link_comment_attr = array(
	'class' => 'link_show_comment_form showCommentBox',
	'id' => 'link_show_comment_form_'.$timeline->id,
	'data-id' => $timeline->id,
	'data-block' => 'form_comment_'.$timeline->id,
);
$link_comment_attr['class'] .= ' hidden';
?>
<?php echo anchor('#', term('form.do_comment'), false, $link_comment_attr); ?>

<?php /* post_comment_link */; ?>
<div id="form_comment_<?php echo $timeline->id; ?>"></div>


<?php /* edit_button */; ?>
<?php
$dropdown_btn_attr = array(
	'data-toggle' => 'dropdown',
	'data-delete_uri' => \Timeline\Site_Util::get_delete_api_info($timeline),
	'data-parent' => 'timelineBox_'.$timeline->id,
	'data-member_id' => $timeline->member_id,
);
if (!$is_detail) $dropdown_btn_attr['data-detail_uri'] = \Timeline\Site_Util::get_detail_uri($timeline->id, $timeline->type, $foreign_table_obj);
?>
<div class="dropdown boxBtn" id="dropdown_<?php echo $timeline->id; ?>">
	<?php echo btn('', '#', 'js-dropdown_tl_menu', false, 'xs', null, $dropdown_btn_attr, 'chevron-down'); ?>
	<ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dLabel"></ul>
</div>

