<p class="article_body"><?php echo nl2br($note->body) ?></p>

<?php if (Module::loaded('album')): ?>
<?php echo render('album::image/_parts/list', array('list' => $images, 'is_simple_view' => true)); ?>
<?php endif; ?>

<?php if ($note->is_published): ?>
<?php if (Auth::check() || $comments): ?>
<h3 id="comments">Comments</h3>
<?php endif; ?>

<div id="comment_list">
<?php echo render('_parts/comment/list', array(
	'parent' => $note,
	'comments' => $comments,
	'is_all_records' => $is_all_records,
	'delete_uri' => 'note/comment/api/delete.json',
	'list_more_box_attrs' => array(
		'data-uri' => 'note/comment/api/list/'.$note->id.'.html',
		'data-is_before' => true,
	),
)); ?>
</div>
<?php if (Auth::check()): ?>
<?php echo render('_parts/post_comment', array('button_attrs' => array(
	'data-post_uri' => 'note/comment/api/create/'.$note->id.'.json',
	'data-get_uri' => 'note/comment/api/list/'.$note->id.'.html',
	'data-list' => '#comment_list',
))); ?>
<?php endif; ?>
<?php endif; ?>
