<?php
$class_name = 'member_img_box_s';
$img_size   = '30x30xc';
if (isset($size) && $size == 'ss')
{
	$class_name = 'member_img_box_ss';
	$img_size   = '20x20xc';
}
?>
<div class="<?php echo $class_name; ?>">
	<?php echo empty($member) ? img('m', $img_size) : img($member->get_image(), $img_size, 'member/'.$member->id); ?>
	<div class="content">
		<div class="main">
			<b class="fullname"><?php echo empty($member) ? Config::get('term.left_member') : Html::anchor('member/'.$member->id, $member->name); ?></b>
<?php if (!empty($content)): ?><?php echo empty($trim_width) ? $content : strim($content, $trim_width); ?><?php endif; ?>
		</div>
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
		'is_mycontents' => $is_mycontents,
	);
	if (!empty($child_model)) $data['child_model'] = $child_model;
	echo render('_parts/public_flag_selecter', $data);
}
?>
	</div>
</div>
