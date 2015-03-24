<p><?php echo nl2br($album->body) ?></p>

<?php if ($list): ?>
<div class="row" id="img_comment_box">
	<div class="col-md-8">
		<a name="slidetop" id="slidetop"></a>
		<div id="myCarousel" class="carousel carousel-flex slide" data-ride="carousel">
			<!-- Wrapper for slides -->
			<div class="carousel-inner"></div>
			<!-- Controls -->
			<a class="left carousel-control" href="#myCarousel" data-slide="prev">
				<span class="glyphicon glyphicon-chevron-left"></span>
			</a>
			<a class="right carousel-control" href="#myCarousel" data-slide="next">
				<span class="glyphicon glyphicon-chevron-right"></span>
			</a>
		</div>
		<div id="slideNumber"></div>
		<div id="link2detail"></div>
	</div>
	<div class="col-md-4">
		<div id="album_image_comment"></div>
		<h4 id="comments">Comments</h4>
		<div id="comment_list"></div>
	</div>
</div>
<?php endif; ?>
