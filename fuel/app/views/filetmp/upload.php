<?php
if (!isset($upload_type)) $upload_type = 'img';
?>
<!-- The container for the uploaded files -->
<div id="upload_files_<?php echo $upload_type; ?>">
<div id="files_<?php echo $upload_type; ?>" class="files row">
<?php if (!empty($files)): ?>
<?php
$data = array(
	'files' => $files,
	'thumbnail_size' => $thumbnail_size,
	'model' => $model,
	'upload_type' => $upload_type
);
if (!empty($insert_target)) $data['insert_target'] = $insert_target;
echo render('filetmp/_parts/upload_images', $data);
?>
<?php endif; ?>
</div><!-- #files -->

<!-- The fileinput-button span is used to style the file input field as button -->
<div class="fileinput">
<?php if (!empty($selects)): ?>
<form class="form-inline">
<?php endif; ?>

	<?php echo Form::hidden('thumbnail_size', isset($thumbnail_size) ? $thumbnail_size : 'M', array('id' => 'thumbnail_size')); ?>

<?php if (!empty($insert_target)): ?>
	<?php echo Form::hidden('insert_target', $insert_target, array('id' => 'insert_target')); ?>
<?php endif; ?>

<?php if (!empty($post_uri)): ?>
	<?php echo Form::hidden('post_uri', $post_uri, array('id' => 'post_uri')); ?>
<?php endif; ?>

<?php if (!empty($selects)): ?>
	<div class="form-group">
<?php endif; ?>

		<label class="sr-only" for="files">Select files...</label>
		<span class="btn btn-default fileinput-button">
			<i class="glyphicon glyphicon-camera"></i>
			<span class="hidden-xs">Select files...</span>
			<!-- The file input field used as target for the file upload widget -->
			<input class="file_select" type="file" name="files[]" multiple id="file_select_<?php echo $upload_type; ?>"<?php if ($upload_type == 'img'): ?> accept="image/*"<?php endif; ?>>
		</span>
<?php if (empty($selects) && FBD_UPLOAD_MAX_FILESIZE): ?>
		<span class="text-muted"><?php echo Num::format_bytes(FBD_UPLOAD_MAX_FILESIZE); ?> まで</span>
<?php endif; ?>
<?php if (!empty($selects)): ?>
	</div><!-- .form-group -->
	<div class="form-group">
		<label class="sr-only" for="<?php echo $selects['name']; ?>"><?php echo $selects['label']; ?></label>
<?php
$atters = array_merge(array('class' => 'form-control input-sm'), $selects['atters']);
echo Form::select($selects['name'], $selects['value'], $selects['options'], $atters);
?>
<?php if (FBD_UPLOAD_MAX_FILESIZE): ?>
		<span class="text-muted"><?php echo Num::format_bytes(FBD_UPLOAD_MAX_FILESIZE); ?> まで</span>
<?php endif; ?>
	</div><!-- .form-group -->
</form>
<?php endif; ?>
</div><!-- fileinput -->
</div><!-- #upload_files -->

<!-- The blueimp Gallery widget -->
<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
	<div class="slides"></div>
	<h3 class="title"></h3>
	<a class="prev">‹</a>
	<a class="next">›</a>
	<a class="close">×</a>
	<a class="play-pause"></a>
	<ol class="indicator"></ol>
</div>

