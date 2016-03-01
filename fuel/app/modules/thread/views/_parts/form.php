<div class="well">
<?php echo form_open(true); ?>
<?php if (isset($is_edit) && $is_edit): ?>
	<?php echo Form::hidden('original_public_flag', isset($thread) ? $thread->public_flag : null); ?>
<?php endif; ?>
	<?php echo form_input($val, 'title', isset($thread) ? $thread->title : ''); ?>
	<?php echo form_textarea($val, 'body', isset($thread) ? $thread->body : ''); ?>
	<?php echo form_upload_files($images, false, true, 'M', array(), 'thread', term('site.picture')); ?>
	<?php echo form_public_flag($val, isset($thread) ? $thread->public_flag : null); ?>
	<?php echo form_button(!empty($is_edit) ? 'form.do_edit' : 'form.do_create'); ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_anchor_delete('thread/delete/'.$thread->id); ?>
<?php endif; ?>
<?php echo form_close(); ?>
</div><!-- well -->
