<?php if (!$is_raw_form): ?>
<div class="form-group">
<div class="<?php if ($is_horizontal): ?>col-sm-offset-2 col-sm-10<?php endif; ?>">
<?php endif; ?>
<?php echo render('filetmp/upload', array(
	'files' => $files,
	'hide_form' => $hide_form,
	'thumbnail_size' => empty($thumbnail_size) ? 'M' : $thumbnail_size,
	'selects' => $selects,
)); ?>
<?php if (!$is_raw_form): ?>
</div>
</div><!-- form-group -->
<?php endif; ?>
