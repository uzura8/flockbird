<div class="img_box">
	<?php echo ($before_id) ? Html::anchor('album/image/detail/'.$before_id, '<i class="icon-backward"></i><br>前へ', array('class' => 'btn btn-mini backward')) : ''; ?>
	<?php echo img($album_image->get_image(), '600x600', '', true); ?>
	<?php echo ($after_id) ? Html::anchor('album/image/detail/'.$after_id, '<i class="icon-forward"></i><br>次へ', array('class' => 'btn btn-mini forward')) : ''; ?>
</div>
<hr>

<?php if (Auth::check() || $comments): ?>
<h3 id="comments">Comments</h3>
<?php endif; ?>

<div id="comment_list">
<?php echo render('image/comment/_parts/list', array('u' => $u, 'album_image' => $album_image, 'comments' => $comments, 'show_more_link' => true)); ?>
</div>

<?php if (Auth::check()): ?>
<div class="commentPostBox">
	<div class="member_img_box_s">
		<?php echo img($u->get_image(), '30x30', 'member/'.$u->id); ?>
		<div class="content">
			<div class="main">
				<b class="fullname"><?php echo Html::anchor('member/'.$u->id, $u->name); ?></b>
				<div class="input"><?php echo Form::textarea('body', null, array('rows' => 1, 'class' => 'span12 autogrow', 'id' => 'input_album_image_comment')); ?></div>
				<div class="input"><a href="#" class="btn btn-mini" id="btn_album_image_comment_create">送信</a></div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
