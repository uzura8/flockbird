<div class="well">
<?php echo form_open(true); ?>
<?php if (isset($is_edit) && $is_edit): ?>
	<?php echo Form::hidden('original_public_flag', isset($album) ? $album->public_flag : null); ?>
	<?php echo Form::hidden('is_update_children_public_flag', 0, array('id' => 'is_update_children_public_flag')); ?>
<?php endif; ?>
	<?php echo form_input($val, 'name', isset($album) ? $album->name : ''); ?>
	<?php echo form_textarea($val, 'body', isset($album) ? $album->body : ''); ?>
<?php if (empty($is_edit)): ?>
	<?php echo form_upload_files($files); ?>
<?php endif; ?>
	<?php echo form_public_flag($val, isset($album) ? $album->public_flag : null); ?>
	<?php echo form_button('form.do_submit', 'button'); ?>
<?php if (isset($is_edit) && $is_edit): ?>
	<?php echo form_anchor(sprintf('album/delete/%d%s', $album->id, get_csrf_query_str()), term('form.do_delete'), array('class' => 'btn btn-default btn-danger')); ?>
<?php endif; ?>
<?php echo form_close(); ?>
</div>
