<?php
$class_name = 'member_img_box_s';
$img_size   = '30x30xc';
if (isset($size))
{
	$class_name = 'member_img_box_'.strtolower($size);
	$img_size   = Config::get('site.upload.types.img.types.m.sizes.'.strtoupper($size));
}
?>
<div class="<?php echo $class_name; ?>">
	<?php echo empty($member) ? img('m', $img_size) : img($member->get_image(), $img_size, 'member/'.$member->id, false, site_get_screen_name($member), true); ?>
	<div class="content">
		<div class="main">
			<b class="fullname"><?php echo empty($member) ? Config::get('term.left_member') : Html::anchor('member/'.$member->id, $member->name); ?></b>
<?php
if (isset($content) && strlen($content))
{
	if (!empty($truncate_lines))
	{
		if (empty($read_more_uri)) $read_more_uri = '';
		$content = truncate_lines($content, $truncate_lines, $read_more_uri);
	}
	elseif (!empty($trim_width))
	{
		$content = strim($content, $trim_width);
	}
	elseif (!empty($is_convert_nl2br) && $is_convert_nl2br === true)
	{
		$content = nl2br($content);
	}

	echo $content;
}
?>
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
if (isset($public_flag, $model, $id))
{
	$is_mycontents = Auth::check() && $u->id == $member->id;
	$data = array(
		'model' => $model,
		'id' => $id,
		'public_flag' => $public_flag,
		'view_icon_only' => isset($public_flag_view_icon_only) ? $public_flag_view_icon_only : false,
		'have_children_public_flag' => isset($have_children_public_flag) ? $have_children_public_flag : false,
		'is_refresh_after_update_public_flag' => isset($is_refresh_after_update_public_flag) ? $is_refresh_after_update_public_flag : false,
		'disabled_to_update' => isset($public_flag_disabled_to_update) ? $public_flag_disabled_to_update : false,
		'is_mycontents' => $is_mycontents,
	);
	if (!empty($child_model)) $data['child_model'] = $child_model;
	echo render('_parts/public_flag_selecter', $data);
}
?>
		</div><!-- sub_info -->
<?php endif; ?>

<?php if (!empty($comment)): ?>
<?php $parent = $comment['parent_obj'] ?>
<div class="comment_info">
	<small><i class="icon-comment"></i> <span id="comment_count_<?php echo $parent->id; ?>"><?php echo $comment['all_comment_count']; ?><span></small>
<?php if (Auth::check()): ?>
	<small><?php echo Html::anchor('#', 'コメントする', array('class' => 'link_comment', 'data-id' => $parent->id)); ?></small>
<?php endif; ?>
</div>
<div id="comment_list_<?php echo $parent->id; ?>">
<?php echo render('_parts/comment/list', array(
	'u' => $u,
	'parent' => $parent,
	'comments' => $comment['list'],
	'is_all_records' => $comment['is_all_records'],
	'list_more_box_attrs' => array('id' => 'listMoreBox_comment_'.$parent->id, 'data-parent_id' => $parent->id),
)); ?>
</div>

<?php if (Auth::check()): ?>
<?php echo render('_parts/post_comment', array(
	'u' => $u,
	'button_attrs' => array('class' => 'btn btn-small btn_comment', 'id' => 'btn_comment_'.$parent->id, 'data-parent_id' => $parent->id,),
	'textarea_attrs' => array('class' => 'span12 autogrow', 'id' => 'textarea_comment_'.$parent->id),
	'parts_attrs' => array('id' => 'commentPostBox_'.$parent->id),
)); ?>
<?php endif; ?>
<?php endif; ?>

	</div>
</div>
