<?php
$images = \Timeline\Site_Util::get_timeline_images(
	$timeline->type,
	$timeline->foreign_id,
	$timeline->id,
	$access_from_member_relation,
  $is_detail
);
$foreign_table_obj = \Timeline\Site_Model::get_foreign_table_obj($timeline->type, $timeline->foreign_id);
$detail_page_uri = \Timeline\Site_Util::get_detail_uri($timeline->id, $timeline->type, $foreign_table_obj);
?>

<?php /* content */; ?>
<?php
$optional_info = array();
if (isset($images['count'])) $optional_info['count'] = $images['count'];
if (isset($images['count_all'])) $optional_info['count_all'] = $images['count_all'];
echo \Timeline\Site_Util::get_timeline_content(
	$timeline->id,
	$timeline->type,
	$timeline->body,
	$foreign_table_obj,
	$optional_info,
	$is_detail
);
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
<?php echo render('_parts/thumbnails', array('images' => $images, 'is_modal_link' => true)); ?>
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
if (!empty($public_flag_info['option_type'])) $data['option_type'] = $public_flag_info['option_type'];
echo render('_parts/public_flag_selecter', $data);
?>
		</div><!-- sub_info -->


<?php /* comment_list */; ?>
<?php
$list = null;
$next_id = null;
$all_comment_count = null;
if ($is_detail)
{
	list($list, $next_id, $all_records_count) = \Timeline\Site_Model::get_comments($timeline->type, $timeline->id, $timeline->foreign_id);
}
?>

<div class="comment_info">
<?php // comment_count_and_link
$data_comment_link = array(
	'id' => $timeline->id,
	'count_attr' => array('class' => 'unset_comment_count'),
	'link_display_absolute' => true,
);
if ($is_detail) $data_comment_link['count'] = $all_records_count;
echo render('_parts/comment/count_and_link_display', $data_comment_link);
?>

<?php // like_count_and_link ?>
<?php if (conf('like.isEnabled')): ?>
<?php
$data_like_link = array(
	'id' => $timeline->id,
	'post_uri' => \Timeline\Site_Util::get_like_api_uri($timeline->type, $timeline->id, $timeline->foreign_id),
	'get_member_uri' => \Timeline\Site_Util::get_liked_member_api_uri4foreign_table($timeline->type, $timeline->id, $timeline->foreign_id),
	'count_attr' => array('class' => 'unset_like_count'),
	'link_display_absolute' => true,
	'attr_prefix' => 'timeline_',
);
if ($is_detail) $data_like_link['count'] = \Timeline\Model_TimelineLike::get_count4timeline_id($timeline->id);
echo render('_parts/like/count_and_link_execute', $data_like_link);
?>
<?php endif; ?>

<!-- share button -->
<?php if (conf('site.common.shareButton.isEnabled', 'page') && check_public_flag($timeline->public_flag)): ?>
<?php echo render('_parts/services/share', array(
	'uri' => $detail_page_uri,
	'text' => \Timeline\Site_Util::get_timeline_ogp_contents($timeline->type, $timeline->body, true),
)); ?>
<?php endif; ?>

</div><!-- comment_info -->

<?php
$comment_get_uri = \Timeline\Site_Util::get_comment_api_uri('get', $timeline->type, $timeline->id, $timeline->foreign_id);
$comment_post_uri = \Timeline\Site_Util::get_comment_api_uri('create', $timeline->type, $timeline->id, $timeline->foreign_id);
$comment_delete_uri = \Timeline\Site_Util::get_comment_api_uri('delete', $timeline->type, $timeline->id, $timeline->foreign_id);
$comment_list_attr = array(
	'class' => 'comment_list unloade_comments',
	'id' => 'comment_list_'.$timeline->id,
	'data-id' => $timeline->id,
	'data-post_uri' => $comment_post_uri,
	'data-get_uri' => $comment_get_uri,
	'data-get_data' => array('image_size' => 'S'),
	'data-input' => '#textarea_comment_'.$timeline->id,
);
?>
<div <?php echo Util_Array::conv_array2attr_string($comment_list_attr); ?>>
<?php if ($is_detail): ?>
<?php
$data = array(
	'parent' => $timeline,
	'list' => $list,
	'next_id' => $next_id,
	'image_size' => 'S',
	'list_more_box_attrs' => array(
		'id' => 'listMoreBox_comment_'.$timeline->id,
		'data-uri' => $comment_get_uri,
		'data-list' => '#comment_list_'.$timeline->id,
		'data-template' => '#comment-template',
		'data-is_before' => 1,
	),
	'delete_uri' => $comment_delete_uri,
	'counter_selector' => '#comment_count_'.$timeline->id,
	'absolute_display_delete_btn' => true,
	'like_api_uri_prefix' => 'timeline/comment',
	'liked_ids' => (conf('like.isEnabled') && \Auth::check()) ? \Site_Model::get_liked_ids('timeline_comment', $u->id, $list) : array(),
);
echo render('_parts/comment/list', $data);
?>
<?php endif; ?>
</div>

<?php
$link_comment_attr = array(
	'class' => 'link_show_comment_form showCommentBox',
	'id' => 'link_show_comment_form_'.$timeline->id,
	'data-id' => $timeline->id,
	'data-block' => 'form_comment_'.$timeline->id,
);
if (!$is_detail) $link_comment_attr['class'] .= ' hidden';
?>
<?php if (!$is_detail || $list): ?>
<?php echo anchor('#', term('form.do_comment'), false, $link_comment_attr); ?>
<?php endif; ?>

<?php /* post_comment_link */; ?>
<div id="form_comment_<?php echo $timeline->id; ?>"></div>


<?php /* edit_button */; ?>
<?php
$dropdown_btn_group_attr = array(
	'id' => 'btn_dropdown_'.$timeline->id,
	'class' => array('dropdown', 'boxBtn'),
);
$dropdown_btn_attr = array(
	'class' => 'js-dropdown_content_menu',
	'data-uri' => sprintf('timeline/api/menu/%d.html', $timeline->id),
	'data-member_id' => $timeline->member_id,
	'data-loaded' => 0,
	'data-parent' => 'timelineBox_'.$timeline->id,
	'data-menu' => '#menu_'.$timeline->id,
);
if ($is_detail) $dropdown_btn_attr['data-uri'] .= '?is_detail=1';

$menus = array();
$menu_detail_link = array('icon_term' => 'site.show_detail');
if (!$is_detail)
{
	$menu_detail_link['href'] = $detail_page_uri;
	$menus[] = $menu_detail_link;
}
elseif (!is_enabled('notice'))
{
	$menu_detail_link['tag'] = 'disabled';
	$menus[] = $menu_detail_link;
}
echo btn_dropdown('noterm.dropdown', $menus, false, 'xs', null, true, $dropdown_btn_group_attr, $dropdown_btn_attr, false);
?>
