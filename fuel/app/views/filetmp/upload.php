<?php if (!isset($hide_form)) $hide_form = false; ?>
<!-- The container for the uploaded files -->
<div id="upload_files" class="<?php if ($hide_form): ?> hidden<?php endif; ?>">
<div id="files" class="files row">
<?php if (!empty($files)): ?>
<?php echo render('filetmp/_parts/upload_images', array('files' => $files, 'thumbnail_size' => $thumbnail_size)); ?>
<?php endif; ?>
</div><!-- #files -->
<!-- The global progress bar -->
<div id="progress" class="progress">
		<div class="progress-bar progress-bar-success"></div>
</div>
<!-- The fileinput-button span is used to style the file input field as button -->
<div class="fileinput">
<?php if (!empty($selects)): ?>
<form class="form-inline">
<?php endif; ?>
	<?php echo Form::hidden('thumbnail_size', isset($thumbnail_size) ? $thumbnail_size : 'M', array('id' => 'thumbnail_size')); ?>
<?php if (!empty($selects)): ?>
	<div class="form-group">
<?php endif; ?>
		<label class="sr-only" for="files">Select files...</label>
		<span class="btn btn-success btn-sm fileinput-button">
			<i class="glyphicon glyphicon-plus"></i>
			<span>Select files...</span>
			<!-- The file input field used as target for the file upload widget -->
			<input id="fileupload" type="file" name="files[]" multiple>
		</span>
<?php if (!empty($selects)): ?>
	</div><!-- .form-group -->
	<div class="form-group">
		<label class="sr-only" for="<?php echo $selects['name']; ?>"><?php echo $selects['label']; ?></label>
<?php
$atters = array_merge(array('class' => 'form-control input-sm'), $selects['atters']);
echo Form::select($selects['name'], $selects['value'], $selects['options'], $atters);
?>
	</div><!-- .form-group -->
</form>
<?php endif; ?>
</div><!-- fileinput -->
</div><!-- #upload_files -->
<?php if ($hide_form): ?>
<button type="button" class="btn btn-default btn-ms display_fileinput-button"><span class="glyphicon glyphicon-camera"></span> 写真を追加</button>
<?php endif; ?>
