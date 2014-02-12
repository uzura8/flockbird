<div class="well">
<?php $label_size = 3; ?>
<?php echo form_open(); ?>
	<?php echo form_input($val, 'name', isset($profile) ? $profile->name : '', 6, $label_size, 'アンダースコアと半角英数字のみが使えます。識別名にはアルファベットが含まれていなければなりません。'); ?>
	<?php echo form_input($val, 'caption', isset($profile) ? $profile->caption : '', 6, $label_size); ?>
	<?php echo form_textarea($val, 'information', isset($profile) ? $profile->information : '', $label_size); ?>
	<?php echo form_input($val, 'placeholder', isset($profile) ? $profile->placeholder : '', 12, $label_size); ?>
	<?php echo form_checkbox($val, 'is_required', isset($profile) ? $profile->is_required : array(), $label_size); ?>
	<?php echo form_radio($val, 'is_edit_public_flag', isset($profile) ? $profile->is_edit_public_flag : 0, $label_size, true); ?>
	<?php echo form_public_flag($val, isset($profile) ? $profile->default_public_flag : null, false, $label_size, false, 'default_public_flag'); ?>
	<?php echo form_radio($val, 'is_unique', isset($profile) ? $profile->is_unique : 0, $label_size, true, 'この設定はフォームタイプが「テキスト」または「テキスト（複数行）」の場合のみ適用されます。'); ?>
	<?php echo form_radio($val, 'is_disp_regist', isset($profile) ? $profile->is_disp_regist : 1, $label_size, true); ?>
	<?php echo form_radio($val, 'is_disp_config', isset($profile) ? $profile->is_disp_config : 1, $label_size, true); ?>
	<?php echo form_radio($val, 'is_disp_search', isset($profile) ? $profile->is_disp_search : 1, $label_size, true); ?>
	<?php echo form_select($val, 'form_type', isset($profile) ? $profile->form_type : 'input', 6, $label_size); ?>
	<?php echo form_select($val, 'value_type', isset($profile) ? $profile->value_type : 'input', 6, $label_size); ?>

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

	<?php echo form_textarea($val, 'value_regexp', isset($profile) ? $profile->value_regexp : '', $label_size, true, '入力値タイプで「正規表現」を選んだ場合のみ有効(PHPのPerl互換(PCRE)正規表現関数を使用)'); ?>
	<?php echo form_button(empty($is_edit) ? '作成する' : '編集する', 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
