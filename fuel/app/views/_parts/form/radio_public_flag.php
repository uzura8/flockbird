<div class="form-group">
	<?php echo Form::label(Config::get('term.public_flag.label').'<span class="required">*</span>', 'public_flag', array('class' => 'control-label col-sm-2')); ?>
	<div class="col-sm-10">
<?php if ($with_no_change_option): ?>
		<div class="radio">
		<?php echo Form::radio('public_flag', 99, is_null(Input::post('public_flag')) || Input::post('public_flag') == 99, array('id' => 'form_public_flag_99')); ?>
		<?php echo Form::label('変更しない', 'public_flag_99'); ?>
		</div>
<?php endif; ?>
<?php $public_flags = Site_Form::get_public_flag_options(); ?>
<?php foreach ($public_flags as $public_flag => $label): ?>
		<div class="radio">
		<?php echo Form::radio(
			'public_flag',
			$public_flag, Input::post('public_flag', $default_value) == $public_flag,
			array('id' => 'form_public_flag_'.$public_flag)
		); ?>
		<?php echo Form::label($label, 'public_flag_'.$public_flag); ?>
		</div>
<?php endforeach; ?>
	</div>
<?php if (!empty($val) && $val->error('public_flag')): ?>
	<div class="col-sm-10 col-sm-offset-2">
		<span class="help-inline error_msg"><?php echo $val->error('public_flag')->get_message(); ?></span>
	</div>
<?php endif; ?>
</div>
