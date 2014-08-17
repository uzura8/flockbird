<p class="article_body"><?php echo nl2br($note->body) ?></p>

<?php if (Module::loaded('album')): ?>
<?php echo render('album::image/_parts/list', array('list' => $images, 'is_simple_view' => true)); ?>
<?php endif; ?>

<?php if ($note->is_published): ?>
<?php if (Auth::check() || $comments): ?>
<h3 id="comments">Comments</h3>
<?php endif; ?>

<div class="comment_info">
<?php // comment_count_and_link
echo render('_parts/comment/count_and_link_display', array(
	'id' => $note->id,
	'count' => $all_comment_count,
	'link_hide_absolute' => true,
)); ?>

<?php // like_count_and_link ?>
<?php if (conf('like.isEnabled') && Auth::check()): ?>
<?php
$data_like_link = array(
	'id' => $note->id,
	'uri' => \Note\Site_Util::get_like_api_uri($note->id),
	'count_attr' => array('class' => 'unset_like_count'),
	'count' => $note->like_count,
	'left_margin' => true,
	'is_liked' => $is_liked_self,
);
echo render('_parts/like/count_and_link_execute', $data_like_link);
?>
<?php endif; ?>
</div><!-- .comment_info -->

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
	'data-counter' => '#comment_count_'.$note->id,
))); ?>
<?php endif; ?>
<?php endif; ?>
