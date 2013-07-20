<?php
$class_name = 'member_img_box_s';
$img_size   = '30x30';
if (isset($size) && $size == 'ss')
{
	$class_name = 'member_img_box_ss';
	$img_size   = '20x20';
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
<?php if (isset($public_flag)) echo get_public_flag_label($public_flag); ?>
	</div>
</div>
