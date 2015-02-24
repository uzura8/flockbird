<?php
if (empty($thumbnail_size)) $thumbnail_size = 'M';
switch ($thumbnail_size)
{
	case 'S':
		$box_class_attr = 'col-sm-4 col-md-3';
		$is_display_original_name = false;
		$is_display_textarea = false;
		$is_subinfo_pull_right = true;
		break;
	case 'M':
	default:
		$box_class_attr = 'col-sm-6 col-md-4';
		$is_display_original_name = true;
		$is_display_textarea = true;
		$is_subinfo_pull_right = false;
		break;
}

$prefix = $file->is_tmp ? 'image_tmp' : 'image';
$delete_btn_attr = array(
	'class' => 'btn btn-xs btn-default delete_file_tmp',
	'data-type' => $prefix,
	'data-file_type' => 'img',
);
if (!empty($file->id)) $delete_btn_attr['data-id'] = $file->id;
if (empty($file->is_tmp) && !empty($model)) $delete_btn_attr['data-model'] = $model;
?>
<div<?php if (!empty($file->id)): ?> id="<?php echo $prefix; ?>_<?php echo $file->id; ?>"<?php endif; ?> class="<?php echo $box_class_attr; ?>">
<?php if (!empty($file->error)): ?>
	<div class="caption">
		<h5><?php echo $file->original_name; ?></h5>
		<p class="text-danger"><?php echo $file->error; ?></p>
	</div><!-- caption -->
<?php else: ?>
	<div class="thumbnail">
<?php if (!empty($file->thumbnail_uri)): ?>
		<?php echo Html::img($file->thumbnail_uri, array('class' => 'thumbnail', 'alt' => $file->original_name)); ?>
<?php endif; ?>
		<div class="caption clearfix">
<?php if ($is_display_original_name): ?>
			<h5><?php echo $file->original_name; ?></h5>
<?php endif; ?>
			<p class="subinfo<?php if ($is_subinfo_pull_right): ?> pull-right<?php endif; ?>">
				<?php echo Num::format_bytes($file->size); ?>
				<?php echo Html::anchor('#', '<i class="glyphicon glyphicon-trash"></i>', $delete_btn_attr); ?>
			</p>
<?php if ($is_display_textarea): ?>
			<p><?php echo Form::textarea(
				sprintf($prefix.'_description[%d]', $file->id),
				isset($file->description) ? $file->description : '',
				array('rows' => 2, 'placeholder' => '写真の説明', 'class' => 'form-control', 'id' => sprintf($prefix.'_description_%d', $file->id))
			); ?></p>
<?php endif; ?>
			<?php echo Form::hidden(sprintf($prefix.'[%d]', $file->id), $file->name_prefix.$file->name, array('class' => $prefix)); ?>
<?php // select insert image size ?>
<?php if (!empty($insert_target) && !empty($file->accept_sizes)): ?>
			<p><?php echo field_select(null, $value = null, Util_Array::set_key_from_value($file->accept_sizes), array('id' => 'select_size_'.$file->id)); ?></p>
<?php endif; ?>
<?php
// insert image tag button.
if (!empty($insert_target))
{
	$data = array(
		'class' => 'js-insert_img',
		'data-id' => $file->id,
		'data-body' => $insert_target,
		'data-file_name' => $file->name,
		'data-file_name_prefix' => $file->name_prefix,
	);
	echo btn('form.insert_picture', '#', '', true, 'sm', null, $data, null, 'button', null, false);
}
?>
		</div><!-- caption -->
	</div><!-- thumbnail -->
<?php endif; ?>
</div><!-- col-sm-6 col-md-4 -->
