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
<?php echo render('_parts/post_comment', array('u' => $u, 'textarea_attrs' => array('class' => 'span12 autogrow'))); ?>
<?php endif; ?>
