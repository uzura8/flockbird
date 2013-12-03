<div id="file_tmp_<?php echo $file->file_tmp_id; ?>" class="col-sm-6 col-md-4">
<?php if (!empty($file->error)): ?>
	<div class="caption">
		<h5><?php echo $file->original_name; ?></h5>
		<p class="text-danger"><?php echo $file->error; ?></p>
	</div><!-- caption -->
<?php else: ?>
	<div class="thumbnail">
		<?php echo Html::img($file->thumbnailUrl, array('class' => 'thumbnail', 'alt' => $file->original_name)); ?>
		<div class="caption">
			<h5><?php echo $file->original_name; ?></h5>
			<p><?php echo Num::format_bytes($file->size); ?></p>
			<p><?php echo Form::textarea(
				sprintf('file_tmps_description[%d]', $file->file_tmp_id),
				isset($file->description) ? $file->description : '',
				array('rows' => 2, 'placeholder' => '写真の説明', 'class' => 'col-xs-12')
			); ?></p>
			<?php echo Html::anchor('#', '<i class="ls-icon-delete"></i>', array(
				'class' => 'btn btn-xs btn-danger delete_file_tmp',
				'data-id' => $file->file_tmp_id,
			)); ?>
			<?php echo Form::hidden(sprintf('file_tmps[%d]', $file->file_tmp_id), $file->name); ?>
		</div><!-- caption -->
	</div><!-- thumbnail -->
<?php endif; ?>
</div><!-- col-sm-6 col-md-4 -->
