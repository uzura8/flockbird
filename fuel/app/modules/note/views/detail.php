<p class="article_body"><?php echo nl2br($note->body) ?></p>

<ul class="thumbnails">
<?php foreach ($images as $album_image): ?>
	<li class="span4">
		<a class="thumbnail" href="<?php echo Uri::create('album/image/'.$album_image->id); ?>">
			<?php echo img($album_image->file, img_size('ai', 'M'), '', false, $album_image->name ?: ''); ?>
		</a>
<?php if ($album_image->name): ?>
		<small><?php echo $album_image->name; ?></small>
<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>

<?php if (Auth::check() || $comments): ?>
<h3 id="comments">Comments</h3>
<?php endif; ?>

<div id="comment_list">
<?php echo render('_parts/comment/list', array('u' => $u, 'parent' => $note, 'comments' => $comments, 'is_all_records' => $is_all_records)); ?>
</div>

<?php if (Auth::check()): ?>
<?php echo render('_parts/post_comment', array('u' => $u, 'textarea_attrs' => array('class' => 'span12 autogrow'))); ?>
<?php endif; ?>
