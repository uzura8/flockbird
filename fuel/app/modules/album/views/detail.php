<p><?php echo nl2br($album->body) ?></p>

<?php if (Config::get('album.display_setting.detail.display_slide_image')): ?>
<?php if (!empty($album_images)): ?>
<div id="myCarousel" class="carousel slide">
	<div class="carousel-inner">
<?php $i = 0; ?>
<?php foreach ($album_images as $album_image): ?>
		<div class="item<?php if (!$i): ?> active<?php endif; ?>">
			<?php echo img((!empty($album_image->file)) ? $album_image->file : '', '600x600', 'album/image/'.$album_image->id); ?>
<?php if (!empty($album_image->name)): ?>
			<div class="carousel-caption">
				<p><?php echo $album_image->name; ?></p>
			</div>
<?php endif; ?>
		</div>
<?php $i++; ?>
<?php endforeach; ?>
	</div>
	<a class="left carousel-control" href="#myCarousel" data-slide="prev">&lsaquo;</a>
	<a class="right carousel-control" href="#myCarousel" data-slide="next">&rsaquo;</a>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if (Config::get('album.display_setting.detail.display_upload_form') && !$disabled_to_update && Auth::check() && $album->member_id == $u->id): ?>
<div class="well">
<h5><?php echo Config::get('term.album_image'); ?>アップロード</h5>
<?php echo form_open(false, true, array('action' => 'album/upload_image'), array('id' => $id)); ?>
<?php echo form_file('image'); ?>
<?php echo form_radio_public_flag(); ?>
<?php echo form_button(); ?>
<?php echo form_close(); ?>
<?php echo Form::close(); ?>
</div><!-- well -->
<?php endif; ?>

<div id="btn_menu">
<?php if ($list): ?>
<?php echo Html::anchor('album/slide/'.$album->id, sprintf('<i class="icon-picture"></i> %sを見る', Config::get('term.album_image')), array('class' => 'btn btn-default mr')); ?>
<?php endif; ?>
<?php if (Auth::check() && $album->member_id == $u->id): ?>
<?php if (!$disabled_to_update): ?>
<?php echo Html::anchor('album/upload/'.$album->id, '<i class="icon-upload"></i> 写真をアップロード', array('class' => 'btn btn-default mr')); ?>
<?php endif; ?>
<?php echo Html::anchor('album/edit_images/'.$album->id, sprintf('<i class="icon-th-list"></i> %s管理', \Config::get('term.album_image')), array('class' => 'btn btn-default')); ?>
<?php endif; ?>
</div>

<?php echo render('image/_parts/list', array('id' => $id, 'album' => $album, 'list' => $list, 'page' => $page, 'is_next' => $is_next, 'is_member_page' => $is_member_page)); ?>
