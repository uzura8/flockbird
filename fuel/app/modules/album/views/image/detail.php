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
	'list' => $comments,
	'next_id' => $comment_next_id,
	'delete_uri' => 'album/image/comment/api/delete.json',
	'counter_selector' => '#comment_count_'.$album_image->id,
	'list_more_box_attrs' => array(
		'data-uri' => 'album/image/comment/api/list/'.$album_image->id.'.html',
	),
)); ?>
</div>

<?php if (Auth::check()): ?>
<?php echo render('_parts/post_comment', array('button_attrs' => array(
	'data-post_uri' => 'album/image/comment/api/create/'.$album_image->id.'.json',
	'data-get_uri' => 'album/image/comment/api/list/'.$album_image->id.'.html',
	'data-list' => '#comment_list',
))); ?>
<?php endif; ?>
