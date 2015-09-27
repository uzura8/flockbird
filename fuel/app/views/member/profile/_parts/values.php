<?php foreach ($member_profiles as $member_profile): ?>
<?php if (!empty($display_type) && $member_profile->profile->display_type < conf('member.profile.display_type.'.$display_type)) continue; ?>
<?php if (!check_public_flag($member_profile->public_flag, $access_from)) continue; ?>
<?php if (!$value = profile_value($member_profile)) continue; ?>
			<div class="row">
<?php
$is_checkbox = $member_profile->profile->form_type == 'checkbox';
$is_view_label = true;
if ($is_checkbox)
{
	$is_view_label = false;
	if (empty($is_checkbox_before)) $is_view_label = true;
}
$is_checkbox_before = $is_checkbox;
?>
				<div class="col-xs-4 u-alr"><?php if ($is_view_label): ?><label><?php echo $member_profile->profile->caption; ?></label><?php endif; ?></div>
				<div class="col-xs-8"><?php echo $value; ?></div>
			</div>
<?php endforeach; ?>
