<?php if (!isset($hide_form)) $hide_form = false; ?>
<?php if ($hide_form): ?>
<button type="button" class="btn btn-default btn-xs display_fileinput-button"><span class="glyphicon glyphicon-camera"></span> 写真を追加</button>
<?php endif; ?>
<!-- The fileinput-button span is used to style the file input field as button -->
<div class="fileinput<?php if ($hide_form): ?> hidden<?php endif; ?>">
<span class="btn btn-success fileinput-button">
		<i class="glyphicon glyphicon-plus"></i>
		<span>Select files...</span>
		<!-- The file input field used as target for the file upload widget -->
		<input id="fileupload" type="file" name="files[]" multiple>
</span>
<br>
<br>
<!-- The global progress bar -->
<div id="progress" class="progress">
		<div class="progress-bar progress-bar-success"></div>
</div>
<!-- The container for the uploaded files -->
<div id="files" class="files row">
<?php if (!empty($files)): ?>
<?php echo render('filetmp/_parts/upload_images', array('files' => $files)); ?>
<?php endif; ?>
</div>
</div><!-- fileinput -->
