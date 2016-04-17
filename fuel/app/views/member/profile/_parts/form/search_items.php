<?php
$name = 'member_name';
if ($val->fieldset()->field($name))
{
	echo form_input($val, $name, '', 7, $label_size, null, null, isset($inputs[$name]) ? $inputs[$name] : '');
}
?>

<?php
$name = 'member_sex';
if ($val->fieldset()->field($name))
{
	echo form_radio($val, $name, isset($inputs[$name]) ? $inputs[$name] : '', $label_size, 'grid', null, null, isset($inputs[$name]) ? $inputs[$name] : '');
}
?>

<?php
$name = 'member_birthyear';
if ($val->fieldset()->field($name))
{
	echo form_radio($val, $name, isset($inputs[$name]) ? $inputs[$name] : '', $label_size, 'grid');
	echo form_select($val, $name, isset($inputs[$name]) ? $inputs[$name] : '', 7, $label_size);
}
?>

<?php
$name_month = 'member_birthdate_month';
$name_day = 'member_birthdate_day';
if ($val->fieldset()->field($name_month) && $val->fieldset()->field($name_day))
{
	echo form_date($val, term('member.birthdate'), $name_month, $name_day, $label_size);
}
?>

<?php foreach ($profiles as $profile): ?>

<?php 	if (in_array($profile->form_type, array('input', 'textarea'))): ?>
<?php
		$name = $profile->name;
		echo form_input($val, $name, '', $profile->form_type == 'textarea' ? 12 : 7, $label_size, null, null, isset($inputs[$name]) ? $inputs[$name] : '');
?>

<?php elseif ($profile->form_type == 'select'): ?>
<?php
		$name = $profile->name;
		echo form_select($val, $name, '', 7, $label_size, false, false, null, null, isset($inputs[$name]) ? $inputs[$name] : '');
?>

<?php elseif ($profile->form_type == 'radio'): ?>
<?php
		$name = $profile->name;
		echo form_radio($val, $name, isset($inputs[$name]) ? $inputs[$name] : '', $label_size, 'grid', null, null, isset($inputs[$name]) ? $inputs[$name] : '');
?>

<?php elseif ($profile->form_type == 'checkbox'): ?>
<?php
		$name = $profile->name;
		echo form_checkbox($val, $name, isset($inputs[$name]) ? $inputs[$name] : array(), $label_size, 'grid', null, null, false, isset($inputs[$name]) ? $inputs[$name] : null);
?>

<?php endif; ?>
<?php endforeach; ?>

