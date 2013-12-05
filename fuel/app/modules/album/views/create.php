<div class="well">
<?php echo form_open(true); ?>
	<?php echo form_input($val, 'name', Config::get('term.album').'名', isset($album) ? $album->name : '', true, 'input-xlarge'); ?>
	<?php echo form_textarea($val, 'body', '説明', isset($album) ? $album->body : ''); ?>
	<?php echo form_upload_files($files); ?>
	<?php echo form_radio_public_flag($val, isset($album) ? $album->public_flag : null); ?>
	<?php echo form_button('送信する', 'button'); ?>
<?php echo form_close(); ?>
</div>
