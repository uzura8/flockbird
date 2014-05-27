<?php
$prefix = $file->is_tmp ? 'file_tmp' : 'file';
$delete_btn_attr = array(
	'class' => 'btn btn-sm btn-default delete_file_tmp',
	'data-type' => $prefix,
	'data-file_type' => 'file',
);
if (!empty($file->id)) $delete_btn_attr['data-id'] = $file->id;
if (!empty($file->is_tmp) && !empty($model)) $delete_btn_attr['data-model'] = $model;
?>
<div<?php if (!empty($file->id)): ?> id="<?php echo $prefix; ?>_<?php echo $file->id; ?>"<?php endif; ?> class="col-sm-12 file_tmp">
	<h5><?php echo $file->original_name; ?></h5>
<?php if (!empty($file->error)): ?>
	<p class="text-danger"><?php echo $file->error; ?></p>
<?php else: ?>
	<div class="row">
		<div class="col-xs-8">
			<?php echo Form::input(
				sprintf($prefix.'_description[%d]', $file->id),
				isset($file->description) ? $file->description : '',
				array('placeholder' => '表示名', 'class' => 'form-control')
			); ?>
		</div>
		<div class="col-xs-4">
			<?php echo Num::format_bytes($file->size); ?>
			<?php echo Html::anchor('#', '<i class="glyphicon glyphicon-trash"></i>', $delete_btn_attr); ?>
		</div>
	</div>
<?php endif; ?>
	<?php echo Form::hidden(sprintf($prefix.'[%d]', $file->id), $file->name); ?>
</div><!-- panel -->
