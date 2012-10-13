<p><?php echo nl2br($album->body) ?></p>

<hr />

<h3 id="comments">写真をアップロード</h3>

<div class="well">
<!-- The file upload form used as target for the file upload widget -->
<?php echo Form::open(array('action' => 'album/upload_images/'.$album->id, 'id' => 'fileupload', 'class' => 'form-stacked', 'enctype' => 'multipart/form-data', 'method' => 'post')); ?>
		<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
		<div class="row-fluid fileupload-buttonbar">
				<div class="span8">
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
<?php if (Config::get('album.display_setting.manage_images.display_delete_button', false)): ?>
						<button type="button" class="btn btn-danger delete">
								<i class="icon-trash icon-white"></i>
								<span>Delete</span>
						</button>
						<input type="checkbox" class="toggle">
<?php endif; ?>
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
		<!-- The loading indicator is shown during file processing -->
		<div class="fileupload-loading"></div>
		<br>
		<!-- The table listing the files available for upload/download -->
		<table role="presentation" class="table table-striped"><tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table>
<?php echo Form::close(); ?>
</div>
