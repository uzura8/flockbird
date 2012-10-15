<p><?php echo nl2br($album->body) ?></p>

<?php if (Config::get('album.display_setting.detail.display_slide_image')): ?>
<?php if ($album_images): ?>
<div id="myCarousel" class="carousel slide">
	<div class="carousel-inner">
<?php $i = 0; ?>
<?php foreach ($album_images as $album_image): ?>
		<div class="item<?php if (!$i): ?> active<?php endif; ?>">
			<?php echo img((!empty($album_image->file)) ? $album_image->file->name : '', '600x600', 'album/image/detail/'.$album_image->id); ?>
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
<?php echo Form::open(array('action' => 'album/upload_image', 'class' => 'form-stacked', 'enctype' => 'multipart/form-data', 'method' => 'post')); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Util_security::get_csrf()); ?>
<?php echo Form::hidden('id', $id); ?>
<div class="control-group">
	<div class="controls">
	<?php echo Form::input('image', '写真', array('type' => 'file', 'class' => 'input-file')); ?>
	</div>
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
<?php echo Html::anchor('album/slide/'.$album->id, sprintf('<i class="icon-picture"></i> %sを見る', \Config::get('album.term.album_image')), array('class' => 'btn mr')); ?>
<?php if (Auth::check() && $album->member_id == $u->id): ?>
<?php echo Html::anchor('album/manage_images/'.$album->id, '<i class="icon-upload"></i> 写真をアップロード', array('class' => 'btn mr')); ?>
<?php echo Html::anchor('album/edit_images/'.$album->id, sprintf('<i class="icon-th-list"></i> %s管理', \Config::get('album.term.album_image')), array('class' => 'btn')); ?>
<?php endif; ?>
</div>

<?php include('_parts/album_image_list.php'); ?>

<?php /*foreach ($comments as $comment): ?>
<div class="commentBox">
	<div class="member_img_box_s">
		<?php echo site_profile_image($comment->member->image, 'x-small', 'member/'.$comment->member_id); ?>
		<div class="content">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$comment->member_id, $comment->member->name); ?></b>
				<?php echo $comment->body ?>
			</div>
			<small><?php echo site_get_time($comment->created_at); ?></small>
		</div>
	</div>
<?php if (isset($u) && in_array($u->id, array($comment->member_id, $note->member_id))): ?>
	<a class="btn btn-mini boxBtn" href="javascript:void(0);" onclick="jConfirm('削除しますか？', 'Confirmation', function(r){if(r) location.href='<?php echo Uri::create(sprintf('note/comment/delete/%d?%s=%s', $comment->id, Config::get('security.csrf_token_key'), Util_security::get_csrf())); ?>';});"><i class="icon-trash"></i></a>
<?php endif ; ?>
</div>
<?php endforeach; ?>

<?php if (Auth::check()): ?>

<div class="commentBox">
	<div class="member_img_box_s">
		<?php echo site_profile_image($u->image, 'x-small', 'member/'.$u->id); ?>
		<div class="content">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$u->id, $u->name); ?></b>
<?php echo Form::open('note/comment/create/'.$note->id) ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Util_security::get_csrf()); ?>
		 <div class="input"><?php echo Form::textarea('body', null, array('cols' => 60, 'rows' => 1, 'class' => 'input-xlarge')); ?></div>
		 <div class="input"><?php echo Form::submit(array('name' => 'submit', 'value' => 'submit', 'class' => 'btn btn-mini')); ?></div>
<?php echo Form::close() ?>
			</div>
		</div>
	</div>
</div>
<?php endif; */?>
