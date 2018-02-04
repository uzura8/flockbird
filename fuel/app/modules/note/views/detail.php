<div class="article_body">
<?php echo convert_body($note->body, array('is_truncate' => false)); ?>
</div>

<?php if (Module::loaded('album')): ?>
<?php echo render('album::image/_parts/list', array('list' => $images, 'is_simple_view' => true)); ?>
<?php endif; ?>

<?php if ($note->is_published): ?>
<div class="comment_info">
<?php // comment_count_and_link
echo render('_parts/comment/count_and_link_display', array(
	'id' => $note->id,
	'count' => $all_comment_count,
	'link_hide_absolute' => true,
)); ?>

<?php // like_count_and_link ?>
<?php 	if (conf('like.isEnabled') && Auth::check()): ?>
<?php
$data_like_link = array(
	'id' => $note->id,
	'post_uri' => \Site_Util::get_api_uri_update_like('note', $note->id),
	'get_member_uri' => \Site_Util::get_api_uri_get_liked_members('note', $note->id),
	'count_attr' => array('class' => 'unset_like_count'),
	'count' => $note->like_count,
	'is_liked' => $is_liked_self,
);
echo render('_parts/like/count_and_link_execute', $data_like_link);
?>
<?php 	endif; ?>

<?php // Facebook feed ?>
<?php 	if (FBD_FACEBOOK_APP_ID && conf('service.facebook.shareDialog.note.isEnabled') && check_public_flag($note->public_flag)): ?>
<?php echo render('_parts/facebook/share_btn', array(
	'images' => $images,
	'link_uri' => 'note/'.$note->id,
	'name' => $note->title,
	'description' => $note->body,
)); ?>
<?php 	endif; ?>

<!-- share button -->
<?php if (conf('site.common.shareButton.isEnabled', 'page') && check_public_flag($note->public_flag)): ?>
<?php echo render('_parts/services/share', array(
  'disableds' => FBD_FACEBOOK_APP_ID && conf('service.facebook.shareDialog.note.isEnabled') ? array('facebook') : array(),
  'text' => $note->title),
); ?>
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
	echo render('_parts/comment/post', array(
		'id' => $note->id,
		'size' => 'M',
		'button_attrs' => $button_attrs,
		'textarea_attrs' => array('id' => 'textarea_comment_'.$note->id))
	);
}
?>
<?php endif; ?>
