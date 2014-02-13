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
<?php endif; ?>

<?php /*
	<?php echo form_checkbox($val, 'is_required', isset($profile) ? $profile->is_required : array(), $label_size); ?>
	<?php echo form_radio($val, 'is_edit_public_flag', isset($profile) ? $profile->is_edit_public_flag : 0, $label_size, true); ?>
	<?php echo form_public_flag($val, isset($profile) ? $profile->default_public_flag : null, false, $label_size, false, 'default_public_flag'); ?>
	<?php echo form_radio($val, 'is_unique', isset($profile) ? $profile->is_unique : 0, $label_size, true, 'この設定はフォームタイプが「テキスト」または「テキスト（複数行）」の場合のみ適用されます。'); ?>
	<?php echo form_radio($val, 'is_disp_regist', isset($profile) ? $profile->is_disp_regist : 1, $label_size, true); ?>
	<?php echo form_radio($val, 'is_disp_config', isset($profile) ? $profile->is_disp_config : 1, $label_size, true); ?>
	<?php echo form_radio($val, 'is_disp_search', isset($profile) ? $profile->is_disp_search : 1, $label_size, true); ?>

<?php
$input_atter = array('class' => 'form-control');
$field_value_min = $val->fieldset()->field('value_min');
$field_value_max = $val->fieldset()->field('value_max');
$label = sprintf('%s〜%s', $field_value_min->get_attribute('label'), $field_value_max->get_attribute('label'));
unset($field_value_min, $field_value_max);
?>
	<div class="form-group<?php if ($val->error('value_min') || $val->error('value_max')): ?> has-error<?php endif; ?>" id="form_value_min_max_block">
		<?php echo Form::label($label, '', array('class' => 'col-sm-'.$label_size.' control-label')); ?>
		<div class="col-sm-<?php echo (12 - $label_size); ?>">
			<div class="row">
				<div class="col-xs-4">
					<?php echo Form::input('value_min', Input::post('value_min'), $input_atter); ?>
				</div>
				<div class="col-xs-1">〜</div>
				<div class="col-xs-4">
					<?php echo Form::input('value_max', Input::post('value_max'), $input_atter); ?>
				</div>
			</div>
<?php if ($val->error('value_min')): ?>
			<div class="row">
				<div class="col-sm-12">
					<span class="help-block error_msg"><?php echo $val->error('value_min')->get_message(); ?></span>
				</div>
			</div>
<?php endif; ?>
<?php if ($val->error('value_max')): ?>
			<div class="row">
				<div class="col-sm-12">
					<span class="help-block error_msg"><?php echo $val->error('value_max')->get_message(); ?></span>
				</div>
			</div>
<?php endif; ?>
		</div>
	</div>
*/ ?>


<?php endforeach; ?>
	<?php echo form_button(term('form.edit')); ?>
<?php echo form_close(); ?>
</div><!-- well -->
