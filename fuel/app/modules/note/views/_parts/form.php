<div class="well">
<?php echo form_open(true, !empty($is_upload['simple'])); ?>
<?php if (isset($is_edit) && $is_edit): ?>
	<?php echo Form::hidden('original_public_flag', isset($note) ? $note->public_flag : null); ?>
<?php if (isset($album_image_name_uploaded_posteds) && $album_image_name_uploaded_posteds): ?>
<?php foreach ($album_image_name_uploaded_posteds as $album_image_id => $value): ?>
	<?php echo Form::hidden('album_image_name_uploaded_posted_'.$album_image_id, $value, array(
		'class' => 'album_image_name_uploaded_posted',
		'id' => 'album_image_name_uploaded_posted_'.$album_image_id,
	)); ?>
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>
	<?php echo Form::hidden('tmp_hash', isset($tmp_hash) ? $tmp_hash : '', array('id' => 'tmp_hash')); ?>
	<?php echo form_input($val, 'title', 'タイトル', isset($note) ? $note->title : '', true, 'input-xlarge'); ?>
	<?php echo form_textarea($val, 'body', '本文', isset($note) ? $note->body : '', true); ?>
<?php if (!empty($is_upload['simple'])): ?>
	<?php echo form_file('image', '写真'); ?>
<?php elseif (!empty($is_upload['multiple'])): ?>
	<?php echo form_button('<i class="ls-icon-camera"></i> 写真を追加', 'button', '', array(
		'id' => 'upload_images_btn',
		'class' => 'btn btn-default',
		'data-toggle' => 'modal',
	)); ?>
	<div id="upload_images" class="modal container hide fade" tabindex="-1"></div>
<?php if (isset($is_edit) && $is_edit): ?>
	<div id="uploaded_images"></div>
<?php endif; ?>
	<div id="tmp_images"></div>
<?php endif; ?>
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
