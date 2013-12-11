<?php $prefix = $file->is_tmp ? 'file_tmp' : 'file'; ?>
<div id="<?php echo $prefix; ?>_<?php echo $file->id; ?>" class="col-sm-6 col-md-4">
<?php if (!empty($file->error)): ?>
	<div class="caption">
		<h5><?php echo $file->original_name; ?></h5>
		<p class="text-danger"><?php echo $file->error; ?></p>
	</div><!-- caption -->
<?php else: ?>
	<div class="thumbnail">
		<?php echo Html::img($file->thumbnail_uri, array('class' => 'thumbnail', 'alt' => $file->original_name)); ?>
		<div class="caption">
			<h5><?php echo $file->original_name; ?></h5>
			<p><?php echo Num::format_bytes($file->size); ?></p>
			<p><?php echo Form::textarea(
				sprintf($prefix.'_description[%d]', $file->id),
				isset($file->description) ? $file->description : '',
				array('rows' => 2, 'placeholder' => '写真の説明', 'class' => 'col-xs-12')
			); ?></p>
			<?php echo Html::anchor('#', '<i class="ls-icon-delete"></i>', array(
				'class' => 'btn btn-xs btn-danger delete_file_tmp',
				'data-id' => $file->id,
			)); ?>
			<?php echo Form::hidden(sprintf($prefix.'[%d]', $file->id), $file->name); ?>
		</div><!-- caption -->
	</div><!-- thumbnail -->
<?php endif; ?>
</div><!-- col-sm-6 col-md-4 -->
