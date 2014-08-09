<?php
$is_detail = true;
$attr = array(
	'class' => 'timelineBox js-hide-btn',
	'id' => 'timelineBox_'.$timeline_id,
	'data-id' => $timeline_id,
	'data-hidden_btn' => 'dropdown_'.$timeline_id,
	'data-hidden_btn_absolute' => 1,
);
if (!empty($timeline_cache_id))
{
	$is_detail = false;
	$attr['data-list_id'] = $timeline_cache_id;
}

$access_from_member_relation = null;
if (\Timeline\Site_Util::check_type_to_get_access_from($type))
{
	$access_from_member_relation = \Site_Member::get_access_from_member_relation($member_id, $self_member_id);
}

$member = Model_Member::check_authority($member_id);
$img_size = conf('upload.types.img.types.m.sizes.M');
?>
<div <?php echo Util_Array::conv_array2attr_string($attr); ?>>
	<div class="row member_contents">
		<div class="col-xs-1">
			<?php echo empty($member) ? img('m', $img_size, '', false, '', true, true) : img($member->get_image(), $img_size, 'member/'.$member->id, false, site_get_screen_name($member), true, true); ?>
		</div>
		<div class="col-xs-11">
			<div class="member_info">
				<b class="fullname"><?php echo empty($member) ? term('member.left') : Html::anchor('member/'.$member->id, $member->name); ?></b>
			</div>
			<div class="main">
				<?php echo \Timeline\Site_Util::get_article_main_view($timeline_id, $access_from_member_relation, $is_detail); ?>
			</div>
		</div>
	</div>
</div><!-- timelineBox -->
