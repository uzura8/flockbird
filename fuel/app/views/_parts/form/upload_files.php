<?php
if (empty($upload_type)) $upload_type = 'img';
if ($label)
{
	$label_class = 'col-sm-'.$offset_size;
	$label_class .= ' control-label';
	$offset = '';
}
else
{
	$offset = $offset_size ? 'col-sm-offset-'.$offset_size : '';
}
$col = 'col-sm-'.(12 - $offset_size);
?>
<?php if (!$is_raw_form): ?>
<div class="form-group">
<?php 	if ($label): ?>
	<?php echo Form::label($label, null, array('class' => $label_class)); ?>
<?php 	endif; ?>
<div class="<?php if ($is_horizontal): ?><?php echo $col; ?><?php if ($offset): ?> <?php echo $offset; ?><?php endif; ?><?php endif; ?>">
<?php endif; ?>
<?php
$data = array(
	'files' => $files,
	'thumbnail_size' => empty($thumbnail_size) ? 'M' : $thumbnail_size,
	'selects' => $selects,
	'model' => $model,
	'upload_type' => $upload_type,
);
if (!empty($post_uri)) $data['post_uri'] = $post_uri;
if (!empty($insert_target)) $data['insert_target'] = $insert_target;
echo render('filetmp/upload', $data);
?>
<?php if (!$is_raw_form): ?>
</div>
</div><!-- form-group -->
<?php endif; ?>
