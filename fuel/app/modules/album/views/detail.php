<p><?php echo nl2br($album->body) ?></p>

<hr />

<h3 id="comments">Comments</h3>

<?php if ($album_images): ?>
<div id="myCarousel" class="carousel slide">
	<div class="carousel-inner">
<?php $i = 0; ?>
<?php foreach ($album_images as $album_image): ?>
		<div class="item<?php if (!$i): ?> active<?php endif; ?>">
			<?php echo img($album_image->file->name, '600x600', '', true); ?>
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

<?php if (Auth::check()): ?>
<div class="well">
<?php echo Form::open(array('action' => 'album/upload_image', 'class' => 'form-stacked', 'enctype' => 'multipart/form-data', 'method' => 'post')); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Security::fetch_token()); ?>
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

<div>
<?php echo Html::anchor('album/manage_images/'.$album->id, '<i class="icon-th-list"></i> 写真管理', array('class' => 'btn')); ?>
</div>
<?php endif; ?>


<?php if ($album_images): ?>
<dl>
<?php $i = 0; ?>
<?php foreach ($album_images as $album_image): ?>
	<dt><?php echo img($album_image->file->name, '200x200', 'album/image/detail/'.$album_image->id); ?></dt>
	<dd>
<?php if (!empty($album_image->name)): ?>
				<?php echo $album_image->name; ?>
<?php endif; ?>
	</dd>
<?php $i++; ?>
<?php endforeach; ?>
</dl>
<?php endif; ?>

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
	<a class="btn btn-mini boxBtn" href="javascript:void(0);" onclick="jConfirm('削除しますか？', 'Confirmation', function(r){if(r) location.href='<?php echo Uri::create(sprintf('note/comment/delete/%d?%s=%s', $comment->id, Config::get('security.csrf_token_key'), Security::fetch_token())); ?>';});"><i class="icon-trash"></i></a>
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
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Security::fetch_token()); ?>
		 <div class="input"><?php echo Form::textarea('body', null, array('cols' => 60, 'rows' => 1, 'class' => 'input-xlarge')); ?></div>
		 <div class="input"><?php echo Form::submit(array('name' => 'submit', 'value' => 'submit', 'class' => 'btn btn-mini')); ?></div>
<?php echo Form::close() ?>
			</div>
		</div>
	</div>
</div>
<?php endif; */?>
