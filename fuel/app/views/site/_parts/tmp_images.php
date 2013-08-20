<?php
if ($is_tmp)
{
	$upload_uri = sprintf('%s%s', Config::get('site.upload.types.img.tmp.root_path.cache_dir'), Config::get('site.upload.types.img.types.ai.default_size'));
}
?>
<?php if ($file_tmps): ?>
<?php foreach ($file_tmps as $file_tmp): ?>
<?php if ($is_tmp): ?>
	<?php echo Form::hidden('file_tmp_id[]', $file_tmp->id); ?>
<?php else: ?>
	<?php echo Form::hidden('album_image_id[]', $file_tmp->id); ?>
<?php endif; ?>
<div class="row-fluid note_image" id="note_image_<?php if ($is_tmp): ?>tmp<?php else: ?>uploaded<?php endif; ?>_<?php echo $file_tmp->id; ?>">
	<a class="pull-left span1 offset1">
<?php if ($is_tmp): ?>
		<?php echo Html::img(sprintf('%s/%s%s', $upload_uri, $file_tmp->path, $file_tmp->name), array('class' => 'img-polaroid')); ?>
<?php else: ?>
			<?php echo img($file_tmp->file, img_size('ai', 'S')); ?>
<?php endif; ?>
	</a>
	<div class="span10 item">
		<h6>Image</h6>
<?php
$album_image_name = '';
if ($is_tmp)
{
	foreach ($file_tmp->file_tmp_config as $file_tmp_config)
	{
		if ($file_tmp_config->name == 'album_image_name')
		{
			$album_image_name = $file_tmp_config->value;
			break;
		}
	}
}
else
{
	$album_image_name = $file_tmp->name;
	$album_image_name_posted = Input::post(sprintf('album_image_name_uploaded.%d', $file_tmp->id), '');
	if ($album_image_name_posted) $album_image_name = $album_image_name_posted;
}
?>
		<?php echo Form::textarea(sprintf($is_tmp ? 'album_image_name[%d]' : 'album_image_name_uploaded[%d]', $file_tmp->id), $album_image_name, array('rows' => 1, 'placeholder' => '写真の説明', 'class' => 'span5')); ?>
		<div class="btn-group" data-toggle="buttons-radio">
			<button type="button" class="btn">Left</button>
			<button type="button" class="btn">Middle</button>
			<button type="button" class="btn">Right</button>
		</div>
		<?php echo Html::anchor('#', '<i class="ls-icon-delete"></i>', array(
			'onclick' => sprintf('delte_image(%d%s)', $file_tmp->id, $is_tmp ? ', true' : '').';return false;',
			'class' => 'btn btn-danger delete_tmp_image',
			'data-id' => $file_tmp->id
		)); ?>
	</div>
</div><!-- row-fluid -->
<?php endforeach; ?>
<?php endif; ?>
