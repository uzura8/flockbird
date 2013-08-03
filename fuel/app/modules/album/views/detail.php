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

<?php if (Config::get('album.display_setting.detail.display_upload_form') && Auth::check() && $album->member_id == $u->id): ?>
<div class="well">
<h5><?php echo Config::get('term.album_image'); ?>アップロード</h5>
<?php echo Form::open(array('action' => 'album/upload_image', 'class' => 'form-stacked form-horizontal', 'enctype' => 'multipart/form-data', 'method' => 'post')); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Util_security::get_csrf()); ?>
<?php echo Form::hidden('id', $id); ?>
<div class="control-group">
	<div class="controls">
	<?php echo Form::input('image', '写真', array('type' => 'file', 'class' => 'input-file')); ?>
	</div>
</div>
<div class="control-group">
	<?php echo Form::label(Config::get('term.public_flag.label'), 'public_flag', array('class' => 'control-label')); ?>
<?php $public_flags = Site_Form::get_public_flag_options() ; ?>
<?php foreach ($public_flags as $public_flag => $label): ?>
	<div class="controls">
		<?php echo Form::radio('public_flag', $public_flag, Config::get('site.public_flag.default') == $public_flag, array('id' => 'form_public_flag_'.$public_flag)); ?>
		<?php echo Form::label($label, 'public_flag_'.$public_flag); ?>
	</div>
<?php endforeach; ?>
</div>
<div class="control-group">
	<div class="controls">
	<?php echo Form::input('submit', '送信', array('type' => 'submit', 'class' => 'btn')); ?>
	</div>
</div>
<?php echo Form::close(); ?>
</div>
<?php endif; ?>

<div id="btn_menu">
<?php if ($list): ?>
<?php echo Html::anchor('album/slide/'.$album->id, sprintf('<i class="icon-picture"></i> %sを見る', Config::get('term.album_image')), array('class' => 'btn mr')); ?>
<?php endif; ?>
<?php if (Auth::check() && $album->member_id == $u->id): ?>
<?php echo Html::anchor('album/upload/'.$album->id, '<i class="icon-upload"></i> 写真をアップロード', array('class' => 'btn mr')); ?>
<?php echo Html::anchor('album/edit_images/'.$album->id, sprintf('<i class="icon-th-list"></i> %s管理', \Config::get('term.album_image')), array('class' => 'btn')); ?>
<?php endif; ?>
</div>

<?php echo render('image/_parts/list.php', array('id' => $id, 'album' => $album, 'list' => $list, 'page' => $page, 'is_next' => $is_next, 'is_member_page' => $is_member_page)); ?>
