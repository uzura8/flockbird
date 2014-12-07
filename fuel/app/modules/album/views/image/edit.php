<?php $label_col_size = 3; ?>
<div class="well">
<?php echo form_open(true); ?>
	<?php echo form_input($val, 'name', isset($album_image) ? $album_image->name : '', 12, $label_col_size); ?>
<?php if (!\Album\Site_Util::check_album_disabled_to_update($album_image->album->foreign_table, true)): ?>
	<?php echo form_public_flag($val, isset($album_image) ? $album_image->public_flag : null, false, $label_col_size); ?>
	<?php echo Form::hidden('original_public_flag', isset($album_image) ? $album_image->public_flag : null); ?>
<?php endif; ?>
	<?php echo form_input_datetime($val, 'shot_at_time', isset($album_image) ? check_and_get_datatime($album_image->shot_at, 'datetime_minutes') : '', null, 6, $label_col_size); ?>
	<?php echo form_button('form.do_edit'); ?>
</div><!-- well -->
