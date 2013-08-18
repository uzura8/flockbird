<div class="well">
<?php echo form_open(true, !empty($is_upload['simple'])); ?>
<?php if (Site_Util::get_action_name() == 'edit'): ?>
	<?php echo Form::hidden('original_public_flag', isset($note) ? $note->public_flag : null); ?>
<?php endif; ?>
	<?php echo Form::hidden('tmp_hash', isset($tmp_hash) ? $tmp_hash : '', array('id' => 'tmp_hash')); ?>
	<?php echo form_input($val, 'title', 'タイトル', isset($note) ? $note->title : '', true, 'input-xlarge'); ?>
	<?php echo form_textarea($val, 'body', '本文', isset($note) ? $note->body : '', true); ?>
<?php if (!empty($is_upload['simple'])): ?>
	<?php echo form_file('image', '写真'); ?>
<?php elseif (!empty($is_upload['multiple'])): ?>
	<?php echo form_button('<i class="ls-icon-camera"></i> 写真を追加', 'button', '', array(
		'id' => 'upload_images_btn',
		'class' => 'btn',
		'data-toggle' => 'modal',
	)); ?>
	<div id="upload_images" class="modal container hide fade" tabindex="-1"></div>
	<div id="uploaded_images"></div>
<?php endif; ?>
	<?php echo form_radio_public_flag($val, isset($note) ? $note->public_flag : null); ?>
	<?php echo form_button(); ?>
<?php echo form_close(); ?>
</div><!-- well -->
