<?php if ($val->fieldset()->field('member_name')): ?>
	<?php echo form_input($val, 'member_name', '', 7, $label_size,
		(is_enabled('notice') && conf('mention.isEnabled', 'notice')) ? __('member_registration_input_help_name') : ''); ?>
<?php endif; ?>

<?php if ($val->fieldset()->field('member_sex')): ?>
<?php
$optional_public_flag = array();
if (conf('profile.sex.publicFlag.isEdit'))
{
	$value = conf('profile.sex.publicFlag.default', null, conf('public_flag.default'));
	if (isset($member_public_flags['sex'])) $value = $member_public_flags['sex'];
	$optional_public_flag = array('name' => 'member_public_flag[sex]', 'value' => $value);
}
?>
	<?php echo form_radio($val, 'member_sex', 'male', $label_size, 'grid', null, $optional_public_flag); ?>
<?php endif; ?>

<?php if ($val->fieldset()->field('member_birthyear')): ?>
<?php
$optional_public_flag = array();
if (conf('profile.birthday.birthyear.publicFlag.isEdit'))
{
	$value = conf('profile.birthday.birthyear.publicFlag.default', null, conf('public_flag.default'));
	if (isset($member_public_flags['birthyear'])) $value = $member_public_flags['birthyear'];
	$optional_public_flag = array('name' => 'member_public_flag[birthyear]', 'value' => $value);
}
?>
	<?php echo form_select($val, 'member_birthyear', 0, 7, $label_size, false, false, null, $optional_public_flag); ?>
<?php endif; ?>

<?php if ($val->fieldset()->field('member_birthdate_month') && $val->fieldset()->field('member_birthdate_month')): ?>
<?php
$optional_public_flag = array();
if (conf('profile.birthday.birthdate.publicFlag.isEdit'))
{
	$value = conf('profile.birthday.birthdate.publicFlag.default', null, conf('public_flag.default'));
	if (isset($member_public_flags['birthdate'])) $value = $member_public_flags['birthdate'];
	$optional_public_flag = array('name' => 'member_public_flag[birthdate]', 'value' => $value);
}
?>
	<?php echo form_date($val, term('member.birthdate'), 'member_birthdate_month', 'member_birthdate_day', $label_size, null, $optional_public_flag); ?>
<?php endif; ?>

<?php if ($val->fieldset()->field('member_country')): ?>
<?php
$optional_public_flag = array();
if (conf('profile.country.publicFlag.isEdit'))
{
	$value = conf('profile.country.publicFlag.default', null, conf('public_flag.default'));
	if (isset($member_public_flags['country'])) $value = $member_public_flags['country'];
	$optional_public_flag = array('name' => 'member_public_flag[country]', 'value' => $value);
}
?>
	<?php echo form_select($val, 'member_country', 0, 7, $label_size, false, false, null, $optional_public_flag, null, true); ?>
<?php endif; ?>

<?php foreach ($profiles as $profile): ?>
<?php
$optional_public_flag = array();
if ($profile->is_edit_public_flag)
{
	$value = isset($member_profile_public_flags[$profile->id]) ? $member_profile_public_flags[$profile->id] : $profile->default_public_flag;
	$optional_public_flag = array('name' => sprintf('member_profile_public_flag[%s]', $profile->id), 'value' => $value);
}
?>
<?php if ($profile->form_type == 'input'): ?>
	<?php echo form_input($val, $profile->name, '', 7, $label_size, $profile->information, $optional_public_flag); ?>
<?php elseif ($profile->form_type == 'textarea'): ?>
	<?php echo form_textarea($val, $profile->name, '', $label_size, true, $profile->information, $optional_public_flag); ?>
<?php elseif ($profile->form_type == 'select'): ?>
	<?php echo form_select($val, $profile->name, 0, 7, $label_size, false, false, $profile->information, $optional_public_flag); ?>
<?php elseif ($profile->form_type == 'radio'): ?>
	<?php echo form_radio($val, $profile->name, 0, $label_size, 'grid', $profile->information, $optional_public_flag); ?>
<?php elseif ($profile->form_type == 'checkbox'): ?>
	<?php echo form_checkbox($val, $profile->name, array(), $label_size, 'grid', $profile->information, $optional_public_flag); ?>
<?php endif; ?>
<?php endforeach; ?>
