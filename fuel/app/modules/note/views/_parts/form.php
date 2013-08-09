<?php echo form_open(true); ?>
<?php if (Site_Util::get_action_name() == 'edit'): ?>
	<?php echo Form::hidden('original_public_flag', isset($note) ? $note->public_flag : null); ?>
<?php endif; ?>
	<?php echo form_input($val, 'title', 'タイトル', isset($note) ? $note->title : '', true, 'input-xlarge'); ?>
	<?php echo form_textarea($val, 'body', '本文', isset($note) ? $note->title : '', true); ?>
	<?php echo form_radio_public_flag($val, isset($note) ? $note->public_flag : ''); ?>
	<?php echo form_button(); ?>
<?php echo form_close(); ?>
