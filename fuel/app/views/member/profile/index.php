<div class="well">
	<div class="row">
		<div class="col-md-4">
			<div><?php echo img($member->get_image(), '180x180xc', '', true, site_get_screen_name($member), true, true); ?></div>
			<div><?php echo Html::anchor(sprintf('member/profile/image%s', $is_mypage ? '' : '/'.$member->id), '<i class="glyphicon glyphicon-camera"></i> '.term('profile').'写真', array('class' => 'btn btn-default')); ?></div>
		</div>
		<div class="col-md-8">
			<div class="row"><h3><?php echo site_get_screen_name($member); ?></h3></div>
<?php foreach ($member_profiles as $member_profile): ?>
<?php if (!check_profile_public_flag($member_profile->public_flag, $access_from)) continue; ?>
			<div class="row">
<?php
$is_checkbox = $member_profile->profile->form_type == 'checkbox';
$is_view_label = true;
if ($is_checkbox)
{
	$is_view_label = false;
	if (!$is_checkbox_before) $is_view_label = true;
}
$is_checkbox_before = $is_checkbox;
?>
				<div class="col-sm-4"><?php if ($is_view_label): ?><label><?php echo $member_profile->profile->caption; ?></label><?php endif; ?></div>
				<div class="col-sm-8"><?php echo profile_value($member_profile); ?></div>
			</div>
<?php endforeach; ?>
		</div>
	</div>
</div>
