<div class="well">
<?php $label_size = 3; ?>
<?php echo form_open(); ?>
<?php if ($val->fieldset()->field('member_name')): ?>
	<?php echo form_input($val, 'member_name', '', 7, $label_size); ?>
<?php endif; ?>
<?php if ($val->fieldset()->field('member_sex')): ?>
<?php
$optional_public_flag = array();
if (!empty($site_configs_profile['sex_is_edit_public_flag']))
{
	$value = Config::get('site.public_flag.default');
	if (isset($site_configs_profile['sex_default_public_flag'])) $value = $site_configs_profile['sex_default_public_flag'];
	if (isset($member_public_flags['sex'])) $value = $member_public_flags['sex'];
	$optional_public_flag = array('name' => 'member_public_flag[sex]', 'value' => $value);
}
?>
	<?php echo form_radio($val, 'member_sex', 'male', $label_size, 'grid', null, $optional_public_flag); ?>
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
	<?php echo form_select($val, $profile->name, 0, 7, $label_size, $profile->information, $optional_public_flag); ?>
<?php elseif ($profile->form_type == 'radio'): ?>
	<?php echo form_radio($val, $profile->name, 0, $label_size, 'grid', $profile->information, $optional_public_flag); ?>
<?php elseif ($profile->form_type == 'checkbox'): ?>
	<?php echo form_checkbox($val, $profile->name, array(), $label_size, 'grid', $profile->information, $optional_public_flag); ?>
<?php endif; ?>
<?php endforeach; ?>
	<?php echo form_button(term('form.do_edit'), 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
