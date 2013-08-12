<?php echo render('_parts/upload_header'); ?>
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h4>写真アップロード</h4>
</div>
<div class="modal-body">
<?php echo render('_parts/upload_images', array(
	'album' => $album,
	'tmp_hash' => $tmp_hash,
	'contents' => $contents,
	'is_hide_form_public_flag' => true,
	'display_delete_button' => Config::get('note.display_setting.form.modal.display_delete_button'),
)); ?>
</div>
<div class="modal-footer">
<button type="button" data-dismiss="modal" class="btn">Close</button>
<!-- <button type="button" class="btn btn-primary">Save changes</button> -->
</div>
<?php echo render('_parts/upload_footer', array('display_delete_button' => Config::get('note.display_setting.form.modal.display_delete_button'))); ?>
