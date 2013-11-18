<div class="img_box">
	<?php echo ($before_id) ? Html::anchor('album/image/'.$before_id, '<i class="icon-backward"></i><br>前へ', array('class' => 'btn btn-default btn-xs backward')) : ''; ?>
	<?php echo img($album_image->get_image(), '600x600', '', true); ?>
	<?php echo ($after_id) ? Html::anchor('album/image/'.$after_id, '<i class="icon-forward"></i><br>次へ', array('class' => 'btn btn-default btn-xs forward')) : ''; ?>
</div>
<hr>

<?php if (Auth::check() || $comments): ?>
<h3 id="comments">Comments</h3>
<?php endif; ?>

<div id="comment_list">
<?php echo render('_parts/comment/list', array('parent' => $album_image->album, 'comments' => $comments, 'is_all_records' => $is_all_records)); ?>
</div>

<?php if (Auth::check()): ?>
<?php echo render('_parts/post_comment'); ?>
<?php endif; ?>
