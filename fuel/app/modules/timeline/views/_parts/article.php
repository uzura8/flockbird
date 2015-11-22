<?php
$is_detail = true;
$attr = array(
	'class' => 'timelineBox js-hide-btn',
	'id' => 'timelineBox_'.$timeline_id,
	'data-id' => $timeline_id,
	'data-hidden_btn' => 'btn_dropdown_'.$timeline_id,
	'data-hidden_btn_absolute' => 1,
);
if (!empty($timeline_cache_id))
{
	$is_detail = false;
	$attr['data-list_id'] = $timeline_cache_id;
	$attr['data-comment_count'] = $comment_count;
	$attr['data-like_count'] = $like_count;
}

$access_from_member_relation = null;
if (\Timeline\Site_Util::check_type_to_get_access_from($type))
{
	$access_from_member_relation = \Site_Member::get_access_from_member_relation($member_id, $self_member_id);
}

$member = Model_Member::check_authority($member_id);
?>
<?php if (isset($liked_timeline_ids)): ?>
<?php echo Form::hidden('liked_timeline_ids', json_encode($liked_timeline_ids), array('id' => 'liked_timeline_ids')); ?>
<?php endif; ?>
<div <?php echo Util_Array::conv_array2attr_string($attr); ?>>
	<div class="row member_contents">
		<div class="col-xs-1"><?php echo member_image($member); ?></div>
		<div class="col-xs-11">
			<div class="member_info">
				<b class="fullname"><?php echo member_name($member, true, true); ?></b>
			</div>
			<div class="main">
				<?php echo \Timeline\Site_Util::get_article_main_view($timeline_id, $access_from_member_relation, $is_detail); ?>
			</div>
		</div>
	</div>
</div><!-- timelineBox -->
