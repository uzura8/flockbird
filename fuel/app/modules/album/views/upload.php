<p><?php echo nl2br($album->body) ?></p>

<hr />

<h3 id="comments">写真をアップロード</h3>

<div class="well">
<!-- The file upload form used as target for the file upload widget -->
<?php echo Form::open(array('action' => 'album/upload_images/'.$album->id, 'id' => 'fileupload', 'class' => 'form-stacked form-horizontal', 'enctype' => 'multipart/form-data', 'method' => 'post')); ?>

	<div class="control-group">
		<?php echo Form::label(Config::get('term.public_flag.label'), 'public_flag', array('class' => 'control-label')); ?>
<?php $public_flags = Site_Form::get_public_flag_options() ; ?>
<?php foreach ($public_flags as $public_flag => $label): ?>
		<div class="controls">
			<?php echo Form::radio('public_flag', $public_flag, Input::post('public_flag', Config::get('site.public_flag.default')) === $public_flag, array('id' => 'form_public_flag_'.$public_flag)); ?>
			<?php echo Form::label($label, 'public_flag_'.$public_flag); ?>
		</div>
<?php endforeach; ?>
<?php /*if ($val->error('public_flag')): ?>
		<span class="help-inline error_msg"><?php echo $val->error('public_flag')->get_message(); ?></span>
<?php endif;*/ ?>
	</div>

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
<?php if (Config::get('album.display_setting.upload.display_delete_button', false)): ?>
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
