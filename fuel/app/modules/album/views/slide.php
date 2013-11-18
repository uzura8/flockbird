<p><?php echo nl2br($album->body) ?></p>

<?php if ($list): ?>
<div class="row" id="img_comment_box">
	<div class="span8">
		<a name="slidetop" id="slidetop"></a>
		<div id="myCarousel" class="carousel">
			<div class="carousel-inner"></div>
				<a class="carousel-control left" href="#slidetop" data-action="prev">&lsaquo;</a>
				<a class="carousel-control right" href="#slidetop" data-action="next">&rsaquo;</a>
			</div>
		<div id="slideNumber"></div>
		<div id="link2detail"></div>
	</div>
	<div class="span4">
		<div id="album_image_comment"></div>
		<h4 id="comments">Comments</h4>
		<div id="comment_list"></div>

<?php if (Auth::check()): ?>
<?php echo render('_parts/post_comment', array('u' => $u, 'attributes' => array('class' => 'w90 autogrow'))); ?>
<?php endif; ?>

	</div>
</div>
<?php endif; ?>
