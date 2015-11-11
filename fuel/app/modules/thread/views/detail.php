<div class="article_body">
<?php echo convert_body($thread->body, array('is_truncate' => false)); ?>
</div>

<?php echo render('_parts/thumbnails', array('is_display_name' => true, 'images' => array('list' => $images, 'file_cate' => 't', 'size' => 'M', 'column_count' => 3))); ?>

<div class="comment_info">
<?php // comment_count_and_link
echo render('_parts/comment/count_and_link_display', array(
	'id' => $thread->id,
	'count' => $all_comment_count,
	'link_hide_absolute' => true,
)); ?>

<?php // like_count_and_link ?>
<?php if (conf('like.isEnabled') && Auth::check()): ?>
<?php
$data_like_link = array(
	'id' => $thread->id,
	'post_uri' => \Site_Util::get_api_uri_update_like('thread', $thread->id),
	'get_member_uri' => \Site_Util::get_api_uri_get_liked_members('thread', $thread->id),
	'count_attr' => array('class' => 'unset_like_count'),
	'count' => $thread->like_count,
	'is_liked' => $is_liked_self,
);
echo render('_parts/like/count_and_link_execute', $data_like_link);
?>
<?php endif; ?>

<!-- share button -->
<?php if (conf('site.common.shareButton.isEnabled', 'page')): ?>
<?php echo render('_parts/services/share'); ?>
<?php endif; ?>

</div><!-- .comment_info -->

<div id="comment_list">
<?php echo render('_parts/comment/list', array(
	'is_detail' => true,
	'parent' => $thread,
	'list' => $comments,
	'next_id' => $comment_next_id,
	'delete_uri' => 'thread/comment/api/delete.json',
	'counter_selector' => '#comment_count_'.$thread->id,
	'list_more_box_attrs' => array(
		'data-uri' => 'thread/comment/api/list/'.$thread->id.'.json',
		'data-template' => '#comment-template',
	),
	'like_api_uri_prefix' => 'thread/comment',
	'liked_ids' => $liked_ids,
)); ?>
</div>
<?php
if (Auth::check())
{
	$button_attrs = array(
		'data-post_uri' => 'thread/comment/api/create/'.$thread->id.'.json',
		'data-get_uri' => 'thread/comment/api/list/'.$thread->id.'.json',
		'data-list' => '#comment_list',
		'data-template' => '#comment-template',
		'data-counter' => '#comment_count_'.$thread->id,
		'data-latest' => 1,
	);
	echo render('_parts/comment/post', array(
		'id' => $thread->id,
		'size' => 'M',
		'button_attrs' => $button_attrs,
		'textarea_attrs' => array('id' => 'textarea_comment_'.$thread->id))
	);
}
?>
