<div class="well">
<?php echo form_open(true); ?>
<?php if (isset($is_edit) && $is_edit): ?>
	<?php echo Form::hidden('original_public_flag', isset($note) ? $note->public_flag : null); ?>
<?php endif; ?>
	<?php echo form_input($val, 'title', isset($note) ? $note->title : ''); ?>
	<?php echo form_textarea($val, 'body', isset($note) ? $note->body : ''); ?>
	<?php echo form_upload_files($files); ?>
	<?php echo form_input_datetime($val, 'published_at_time', isset($note) ? check_and_get_datatime($note->published_at, 'datetime_minutes') : ''); ?>
	<?php echo form_public_flag($val, isset($note) ? $note->public_flag : null); ?>
<?php if (empty($note->is_published)): ?>
	<?php echo form_button('form.draft', 'submit', 'is_draft', array('value' => 1, 'class' => 'btn btn-default btn-inverse submit_btn')); ?>
<?php endif; ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_button(empty($note->is_published) ? 'form.do_publish' : 'form.do_edit'); ?>
<?php else: ?>
	<?php echo form_button('form.do_publish'); ?>
<?php endif; ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_anchor_delete('note/delete/'.$note->id); ?>
<?php endif; ?>
<?php echo form_close(); ?>
</div><!-- well -->
