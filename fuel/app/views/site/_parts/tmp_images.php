<?php
$upload_uri = sprintf('%s%s', Config::get('site.upload.types.img.tmp.root_path.cache_dir'), Config::get('site.upload.types.img.types.ai.default_size'));
?>
<?php if ($file_tmps): ?>
<?php foreach ($file_tmps as $file_tmp): ?>
<div class="row-fluid note_image">
	<a class="pull-left span1 offset1"><?php echo Html::img(sprintf('%s/%s%s', $upload_uri, $file_tmp->path, $file_tmp->name), array('class' => 'img-polaroid')); ?></a>
	<div class="span5 item">
		<h6>Image</h6>
		<?php echo Form::textarea('description', '', array('rows' => 1, 'placeholder' => '写真の説明', 'class' => 'span11')); ?>
	</div>
	<div class="span5 item">
<?php if (Config::get('note.display_setting.form.tmp_images.image_position_radio_button')): ?>
		<div class="btn-group" data-toggle="buttons-radio">
			<button type="button" class="btn">Left</button>
			<button type="button" class="btn">Middle</button>
			<button type="button" class="btn">Right</button>
		</div>
<?php endif; ?>
		<?php echo Form::button('name', '<i class="ls-icon-delete"></i>', array('class' => 'btn btn-danger')); ?>
	</div>
</div><!-- row-fluid -->
<?php endforeach; ?>
<?php endif; ?>
