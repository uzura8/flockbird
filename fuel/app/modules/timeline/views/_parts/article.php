<?php
$size = 'M';
if (IS_SP) $size = Site_Util::convert_img_size_down($size) ?: $size;
$class_name = 'member_img_box_'.strtolower($size);
$img_size   = conf('upload.types.img.types.m.sizes.'.$size);
$member = Model_Member::check_authority($member_id);
$access_from_member_relation = \Site_Member::get_access_from_member_relation($member_id, $self_member_id);
$attr = array(
	'class' => 'timelineBox js-hide-btn',
	'id' => 'timelineBox_'.$timeline_id,
	'data-id' => $timeline_id,
	'data-hidden_btn' => 'btn_timeline_delete_'.$timeline_id,
	'data-list_id' => $timeline_cache_id,
);
?>
<div <?php echo Util_Array::conv_array2attr_string($attr); ?>>
	<div class="<?php echo $class_name; ?>">
		<?php echo empty($member) ? img('m', $img_size, '', false, '', true) : img($member->get_image(), $img_size, 'member/'.$member->id, false, site_get_screen_name($member), true); ?>
		<div class="content">
			<div class="member_info">
				<b class="fullname"><?php echo empty($member) ? term('member.left') : Html::anchor('member/'.$member->id, $member->name); ?></b>
			</div>
			<div class="main">
				<?php echo \Timeline\Site_Util::get_article_main_view($timeline_id, $access_from_member_relation); ?>
			</div>
		</div>
	</div>
</div><!-- timelineBox -->
