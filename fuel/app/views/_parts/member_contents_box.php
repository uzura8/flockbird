<?php
$size = empty($size) ? 'M' : strtoupper($size);
//$img_size = conf('upload.types.img.types.m.sizes.'.$size);
?>
<div class="row member_contents">
	<div class="col-xs-1"><?php echo img($member ? $member->get_image() : 'm', $size, $member ? 'member/'.$member->id : '', false, member_name($member), true, true); ?></div>
	<div class="col-xs-11">
		<div class="main">
			<b class="fullname"><?php echo member_name($member, true, true); ?></b>
<?php
if (isset($content) && strlen($content))
{
	if (empty($is_output_raw_content))
	{
		$convert_body_options = array();
		if (!empty($truncate_lines))
		{
			$convert_body_options['truncate_line'] = $truncate_lines;
			if (empty($read_more_uri)) $convert_body_options['read_more_uri'] = $read_more_uri;
		}
		elseif (!empty($trim_width))
		{
			$convert_body_options['truncate_width'] = $trim_width;
		}
		elseif (empty($is_convert_nl2br) || $is_convert_nl2br === false)
		{
			$convert_body_options['nl2br'] = false;
		}
		echo convert_body($content, $convert_body_options);
	}
	else
	{
		echo $content;
	}
}
?>
<?php if (!empty($quote_article)) echo $quote_article; ?>
<?php if (!empty($images)): ?>
<?php echo render('_parts/thumbnails', array('images' => $images)); ?>
<?php endif; ?>
		</div>
<?php if ($date || isset($public_flag, $model, $id)): ?>
		<div class="sub_info">
<?php if ($date): ?>
			<small><?php if (!empty($date['label'])) echo $date['label'].': '; ?><?php echo site_get_time($date['datetime']) ?></small>
<?php endif; ?>
<?php
// reply link
if (!empty($reply_link))
{
	echo render('notice::_parts/link_reply', $reply_link);
}

// like count and link
if (!empty($like_link))
{
	echo render('_parts/like/count_and_link_execute', $like_link);
}

// public_flag
if (isset($public_flag, $model, $id))
{
	$is_mycontents = Auth::check() && isset($member) && $u->id == $member->id;
	$data = array(
		'model' => $model,
		'id' => !empty($public_flag_target_id) ? $public_flag_target_id : $id,
		'public_flag' => $public_flag,
		'view_icon_only' => isset($public_flag_view_icon_only) ? $public_flag_view_icon_only : false,
		'have_children_public_flag' => isset($have_children_public_flag) ? $have_children_public_flag : false,
		'is_refresh_after_update_public_flag' => isset($is_refresh_after_update_public_flag) ? $is_refresh_after_update_public_flag : false,
		'disabled_to_update' => isset($public_flag_disabled_to_update) ? $public_flag_disabled_to_update : false,
		'is_mycontents' => $is_mycontents,
	);
	if (!empty($child_model)) $data['child_model'] = $child_model;
	if (!empty($public_flag_option_type)) $data['option_type'] = $public_flag_option_type;
	echo render('_parts/public_flag_selecter', $data);
}
?>
		</div><!-- sub_info -->
<?php endif; ?>

<?php if (!empty($comment)): ?>
<?php $parent = $comment['parent_obj'] ?>
<div class="comment_info">
	<small><span class="glyphicon glyphicon-comment"></span> <span id="comment_count_<?php echo $parent->id; ?>"><?php echo $comment['all_comment_count']; ?><span></small>
<?php if (Auth::check()): ?>
	<small><?php echo Html::anchor('#', 'コメントする', array('class' => 'link_comment', 'data-id' => $parent->id)); ?></small>
<?php endif; ?>
</div>
<div id="comment_list_<?php echo $parent->id; ?>">
<?php
$list_more_box_attrs_def = array('id' => 'listMoreBox_comment_'.$parent->id, 'data-parent_id' => $parent->id);
$list_more_box_attrs     = empty($list_more_box_attrs) ? $list_more_box_attrs_def : array_merge($list_more_box_attrs_def, $list_more_box_attrs);
$data = array(
	'u' => $u,
	'parent' => $parent,
	'comments' => $comment['list'],
	'list_more_box_attrs' => $list_more_box_attrs,
);
if (!empty($comment_delete_uri)) $data['delete_uri'] = $comment_delete_uri;
echo render('_parts/comment/list', $data);
?>
</div>

<?php if (Auth::check()): ?>
<?php if ($comment['all_comment_count']): ?>
<?php echo Html::anchor('#', 'コメントする', array('class' => 'link_comment hide-after_click showCommentBox', 'data-id' => $parent->id)); ?>
<?php endif; ?>
<?php
$post_comment_button_attrs_def = array('class' => 'btn btn-default btn-sm btn_comment', 'id' => 'btn_comment_'.$parent->id, 'data-parent_id' => $parent->id,);
$post_comment_button_attrs     = empty($post_comment_button_attrs) ? $post_comment_button_attrs_def : array_merge($post_comment_button_attrs_def, $post_comment_button_attrs);
echo render('_parts/comment/post', array(
	'u' => $u,
	'button_attrs' => $post_comment_button_attrs,
	'textarea_attrs' => array('id' => 'textarea_comment_'.$parent->id),
	'parts_attrs' => array('id' => 'commentPostBox_'.$parent->id),
));
?>
<?php endif; ?>
<?php endif; ?>

	</div>
</div>

