<div class="well">
<!-- The file upload form used as target for the file upload widget -->
<?php echo form_open(false, true, array('action' => 'album/upload_images/'.$album->id, 'id' => 'fileupload')); ?>
<?php echo Form::hidden('contents', isset($contents) ? $contents : ''); ?>
<?php echo Form::hidden('tmp_hash_upload', isset($tmp_hash) ? $tmp_hash : ''); ?>
<?php if (!empty($is_hide_form_public_flag)): ?>
<?php echo Form::hidden('public_flag', Config::get('site.public_flag.default')); ?>
<?php else: ?>
<?php echo form_radio_public_flag(null, $album->public_flag); ?>
<?php endif; ?>

	<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
	<div class="row fileupload-buttonbar">
			<div class="span8 btn_box">
					<!-- The fileinput-button span is used to style the file input field as button -->
					<span class="btn btn-success fileinput-button">
							<i class="icon-plus icon-white"></i>
							<span>Add files...</span>
							<input type="file" name="files[]" multiple>
					</span>
					<button type="submit" class="btn btn-primary start">
							<i class="icon-upload icon-white"></i>
							<span>Start upload</span>
					</button>
					<button type="reset" class="btn btn-warning cancel">
							<i class="icon-ban-circle icon-white"></i>
							<span>Cancel upload</span>
					</button>
<?php if (isset($display_delete_button) && $display_delete_button === true): ?>
					<button type="button" class="btn btn-danger delete">
							<i class="icon-trash icon-white"></i>
							<span>Delete</span>
					</button>
					<input type="checkbox" class="toggle">
<?php endif; ?>
					<!-- The loading indicator is shown during file processing -->
					<span class="fileupload-loading"></span>
			</div>
			<!-- The global progress information -->
			<div class="span4 fileupload-progress fade">
					<!-- The global progress bar -->
					<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
							<div class="bar" style="width:0%;"></div>
					</div>
					<!-- The extended global progress information -->
					<div class="progress-extended">&nbsp;</div>
			</div>
	</div>

	<!-- The table listing the files available for upload/download -->
	<table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
<?php echo form_close(); ?>
</div>
