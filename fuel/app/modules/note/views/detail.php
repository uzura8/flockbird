<p class="article_body"><?php echo nl2br($note->body) ?></p>

<ul class="thumbnails">
<?php foreach ($images as $album_image): ?>
	<li class="span4">
		<div class="thumbnail">
			<?php echo img($album_image->file, img_size('ai', 'M'), 'album/image/'.$album_image->id); ?>
<?php /*<img data-src="holder.js/300x200" alt="">*/ ; ?>
<?php if ($album_image->name): ?>
			<p><?php echo $album_image->name; ?></p>
<?php endif; ?>
		</div>
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
