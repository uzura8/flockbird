<?php
if ($is_required)
{
	$label .= '<span class="required">*</span>';
	$input_atter['required'] = 'required';
}
?>
<div class="form-group">
<?php if (strlen($label)): ?>
	<?php echo Form::label($label, $name, array('class' => 'control-label col-sm-2')); ?>
<?php endif; ?>
	<div class="<?php if (isset($label)): ?>col-sm-10 col-sm-offset-2<?php else: ?>col-sm-12<?php endif; ?>">
		<?php echo Form::input($name, null, $input_atter); ?>
<?php if (!empty($val) && $val->error($name)): ?>
		<span class="help-inline error_msg"><?php echo $val->error($name)->get_message(); ?></span>
<?php endif; ?>
<?php if (FBD_UPLOAD_MAX_FILESIZE): ?>
		<p class="help-block"><?php echo Num::format_bytes(FBD_UPLOAD_MAX_FILESIZE); ?> まで</p>
<?php endif; ?>
	</div>
</div>
