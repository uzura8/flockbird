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
