<p><?php echo nl2br($album->body) ?></p>

<?php if ($album_images): ?>
<div class="row-fluid" id="img_comment_box">
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
		<div id="loading_list"></div>
		<div id="comment_list"></div>

<?php if (Auth::check()): ?>
		<div class="commentBox">
			<div class="member_img_box_s">
				<?php echo img($u->get_image(), '30x30', 'member/'.$u->id); ?>
				<div class="content">
					<div class="main">
						<b class="fullname"><?php echo Html::anchor('member/'.$u->id, $u->name); ?></b>
						<div class="input"><?php echo Form::textarea('body', null, array('rows' => 1, 'class' => 'w90 autogrow', 'id' => 'input_album_image_comment')); ?></div>
						<div class="input"><a href="javaScript:void(0);" class="btn btn-mini" id="btn_album_image_comment_create">送信</a></div>
					</div>
				</div>
			</div>
		</div>
<?php endif; ?>
	</div>
</div>
<?php endif; ?>
