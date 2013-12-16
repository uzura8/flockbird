<div class="well">
<?php echo form_open(true); ?>
<?php if (isset($is_edit) && $is_edit): ?>
	<?php echo Form::hidden('original_public_flag', isset($note) ? $note->public_flag : null); ?>
<?php endif; ?>
	<?php echo form_input($val, 'title', 'タイトル', isset($note) ? $note->title : '', true, 'input-xlarge'); ?>
	<?php echo form_textarea($val, 'body', '本文', isset($note) ? $note->body : '', true); ?>
	<?php echo form_upload_files($files, $files ? false : true); ?>
	<?php echo form_input($val, 'published_at_time', '日時', !empty($note->published_at) ? substr($note->published_at, 0, 16) : '', false, 'span4'); ?>
	<?php echo form_radio_public_flag($val, isset($note) ? $note->public_flag : null); ?>
<?php if (isset($is_edit) && $is_edit): ?>
	<?php echo form_button(empty($note->is_published) ? '公開する' : '編集する', 'button'); ?>
<?php else: ?>
	<?php echo form_button('公開する'); ?>
<?php endif; ?>
<?php if (empty($note->is_published)): ?>
	<?php echo form_button(Config::get('term.draft'), 'submit', 'is_draft', array('value' => 1, 'class' => 'btn btn-default btn-inverse')); ?>
<?php endif; ?>
<?php if (isset($is_edit) && $is_edit): ?>
	<?php echo form_anchor(sprintf('note/delete/%d%s', $note->id, get_csrf_query_str()), '削除する', array('class' => 'btn btn-default btn-danger')); ?>
<?php endif; ?>
<?php echo form_close(); ?>
</div><!-- well -->
