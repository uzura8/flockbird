<div class="img_box">
	<?php echo ($before_id) ? Html::anchor('album/image/'.$before_id, '<span class="glyphicon glyphicon-backward"></span><br>前へ', array('class' => 'btn btn-default btn-xs backward')) : ''; ?>
	<?php echo img($album_image->get_image(), '600x600', '', true, $album_image->name ?: '', false, true); ?>
	<?php echo ($after_id) ? Html::anchor('album/image/'.$after_id, '<span class="glyphicon glyphicon-forward"></span><br>次へ', array('class' => 'btn btn-default btn-xs forward')) : ''; ?>
</div>
<hr>

<?php if (Auth::check() || $comments): ?>
<h3 id="comments">Comments</h3>
<?php endif; ?>

<div id="comment_list">
<?php echo render('_parts/comment/list', array(
	'parent' => $album_image->album,
	'comments' => $comments,
	'is_all_records' => $is_all_records,
	'delete_uri' => 'album/image/comment/api/delete.json',
	'list_more_box_attrs' => array(
		'data-uri' => 'album/image/comment/api/list/'.$album_image->id.'.html',
		'data-is_before' => true,
	),
)); ?>
</div>

<?php if (Auth::check()): ?>
<?php echo render('_parts/post_comment'); ?>
<?php endif; ?>
