<div class="well">
<?php $label_size = 2; ?>
<?php echo form_open(); ?>
<?php foreach ($profiles as $profile): ?>
<?php
$optional_public_flag = array();
if ($profile->is_edit_public_flag)
{
	$value = isset($public_flags[$profile->id]) ? $public_flags[$profile->id] : $profile->default_public_flag;
	$optional_public_flag = array('name' => sprintf('public_flag[%s]', $profile->id), 'value' => $value);
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
	<?php echo form_checkbox($val, $profile->name, 0, $label_size, 'grid', $profile->information, $optional_public_flag); ?>
<?php endif; ?>

<?php endforeach; ?>
	<?php echo form_button(term('form.edit')); ?>
<?php echo form_close(); ?>
</div><!-- well -->
