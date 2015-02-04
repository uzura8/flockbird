<div class="article_body">
<?php echo convert_body($note->body, array('is_truncate' => false)); ?>
</div>

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

<?php // like reply ?>
<?php if (conf('mention.isEnabled', 'notice') && $note->member): ?>
<?php
$data_reply_link = array(
	'id' => $note->id,
	'target_id' => $note->id,
	'member_name' => $note->member->name,
	'left_margin' => true,
);
echo render('notice::_parts/link_reply', $data_reply_link);
?>
<?php endif; ?>

<?php // like_count_and_link ?>
<?php if (conf('like.isEnabled') && Auth::check()): ?>
<?php
$data_like_link = array(
	'id' => $note->id,
	'post_uri' => \Site_Util::get_api_uri_update_like('note', $note->id),
	'get_member_uri' => \Site_Util::get_api_uri_get_liked_members('note', $note->id),
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
	'is_detail' => true,
	'parent' => $note,
	'list' => $comments,
	'next_id' => $comment_next_id,
	'delete_uri' => 'note/comment/api/delete.json',
	'counter_selector' => '#comment_count_'.$note->id,
	'list_more_box_attrs' => array(
		'data-uri' => 'note/comment/api/list/'.$note->id.'.json',
		'data-template' => '#comment-template',
	),
	'like_api_uri_prefix' => 'note/comment',
	'liked_ids' => $liked_ids,
)); ?>
</div>
<?php
if (Auth::check())
{
	$button_attrs = array(
		'data-post_uri' => 'note/comment/api/create/'.$note->id.'.json',
		'data-get_uri' => 'note/comment/api/list/'.$note->id.'.json',
		'data-list' => '#comment_list',
		'data-template' => '#comment-template',
		'data-counter' => '#comment_count_'.$note->id,
		'data-latest' => 1,
	);
	echo render('_parts/comment/post', array('size' => 'M', 'button_attrs' => $button_attrs, 'textarea_attrs' => array('id' => 'textarea_comment_'.$note->id)));
}
?>
<?php endif; ?>
