<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php echo term('thread'); ?>がありません。
<?php else: ?>
<div id="article_list">
<?php foreach ($list as $id => $thread): ?>
<?php
$attr = array(
	'class' => 'article js-hide-btn',
	'id' => 'article_'.$id,
	'data-hidden_btn' => 'btn_dropdown_'.$id,
	'data-hidden_btn_absolute' => 1,
);
?>
	<div <?php echo Util_Array::conv_array2attr_string($attr); ?>>
		<div class="header">
			<h4><?php echo Html::anchor('thread/'.$id, strim($thread->title, conf('view_params_default.list.trim_width.title'))); ?></h4>
			<div class="list_subtitle">
<?php echo render('_parts/member_contents_box', array(
	'member' => $thread->member,
	'model' => 'thread',
	'id' => $id,
	'size' => 'M',
	'public_flag' => $thread->public_flag,
	'public_flag_option_type' => 'public',
	'date' => array('datetime' => $thread->sort_datetime)
)); ?>
<?php
$dropdown_btn_group_attr = array(
	'id' => 'btn_dropdown_'.$id,
	'class' => array('dropdown', 'boxBtn'),
);
$dropdown_btn_attr = array(
	'class' => 'js-dropdown_content_menu',
	'data-uri' => sprintf('thread/api/menu/%d.html', $id),
	'data-member_id' => $thread->member_id,
	'data-menu' => '#menu_'.$thread->id,
	'data-loaded' => 0,
);
$menus = array(array('icon_term' => 'site.show_detail', 'href' => 'thread/'.$id));
echo btn_dropdown('noterm.dropdown', $menus, false, 'xs', null, true, $dropdown_btn_group_attr, $dropdown_btn_attr, false);
?>
			</div><!-- list_subtitle -->
		</div><!-- header -->
		<div class="body">
			<?php echo convert_body($thread->body, array(
				'truncate_line' => conf('view_params_default.list.truncate_lines.body'),
				'read_more_uri' => 'thread/'.$id,
			)); ?>
		</div>

<?php
list($images, $count) = \Thread\Model_ThreadImage::get4thread_id($id, 3, true);
if ($images)
{
	echo render('_parts/thumbnails', array('is_display_name' => true, 'images' => array(
		'list' => $images,
		'file_cate' => 't',
		'size' => 'M',
		'column_count' => 3,
		'parent_page_uri' => 'thread/'.$id,
		'count_all' => $count,
	)));
}
?>

<?php
// thread_comment
list($comments, $comment_next_id, $all_comment_count)
	= \Thread\Model_ThreadComment::get_list(array('thread_id' => $id), conf('view_params_default.list.comment.limit'), true, false, 0, 0, null, false, true);
?>

<div class="comment_info">
<?php // comment_count_and_link
$link_comment_attr = array(
	'id' => 'link_show_comment_form_'.$id,
	'class' => 'js-display_parts link_show_comment_'.$id,
	'data-target_id' => 'commentPostBox_'.$id,
	'data-hide_selector' => '.link_show_comment_'.$id,
	'data-focus_selector' => '#textarea_comment_'.$id,
);
echo render('_parts/comment/count_and_link_display', array(
	'id' => $id,
	'count' => $all_comment_count,
	'link_attr' => $link_comment_attr,
)); ?>

<?php // like reply ?>
<?php if (conf('mention.isEnabled', 'notice') && $thread->member): ?>
<?php
$data_reply_link = array(
	'id' => $thread->id,
	'target_id' => $thread->id,
	'member_name' => member_name($thread->member),
);
echo render('notice::_parts/link_reply', $data_reply_link);
?>
<?php endif; ?>

<?php // like_count_and_link ?>
<?php if (conf('like.isEnabled')): ?>
<?php
$data_like_link = array(
	'id' => $id,
	'post_uri' => \Site_Util::get_api_uri_update_like('thread', $id),
	'get_member_uri' => \Site_Util::get_api_uri_get_liked_members('thread', $id),
	'count_attr' => array('class' => 'unset_like_count'),
	'count' => $thread->like_count,
	'is_liked' => isset($liked_thread_ids) && in_array($id, $liked_thread_ids),
);
echo render('_parts/like/count_and_link_execute', $data_like_link);
?>
<?php endif; ?>

<!-- share button -->
<?php if (conf('site.common.shareButton.isEnabled', 'page') && check_public_flag($thread->public_flag)): ?>
<?php echo render('_parts/services/share', array('uri' => 'thread/'.$id, 'text' => $thread->title)); ?>
<?php endif; ?>

</div><!-- .comment_info -->

<?php
$comment_list_attr = array(
	'class' => 'comment_list',
	'id' => 'comment_list_'.$id,
);
?>
<?php if ($comments): ?>
<div <?php echo Util_Array::conv_array2attr_string($comment_list_attr); ?>>
<?php
$data = array(
	'parent' => $thread,
	'list' => $comments,
	'next_id' => $comment_next_id,
	'delete_uri' => 'thread/comment/api/delete.json',
	'counter_selector' => '#comment_count_'.$id,
	'list_more_box_attrs' => array(
		'id' => 'listMoreBox_comment_'.$id,
		'data-uri' => sprintf('thread/comment/api/list/%s.json', $id),
		'data-list' => '#comment_list_'.$id,
		'data-template' => '#comment-template',
	),
	'like_api_uri_prefix' => 'thread/comment',
	'liked_ids' => (conf('like.isEnabled') && \Auth::check()) ? \Site_Model::get_liked_ids('thread_comment', $u->id, $comments) : array(),
);
echo render('_parts/comment/list', $data);
?>
</div>

<?php /* post_comment_link */; ?>
<?php if (Auth::check()): ?>
<?php $link_comment_attr['class'] .= ' showCommentBox'; ?>
<?php echo anchor('#', term('form.do_comment'), false, $link_comment_attr); ?>
<?php endif; ?>

<?php else: ?>
<div <?php echo Util_Array::conv_array2attr_string($comment_list_attr); ?>></div>
<?php endif; ?>

<?php if (Auth::check()): ?>
<?php echo render('_parts/comment/post', array(
	'id' => $id,
	'size' => 'M',
	'parts_attrs' => array('class' => 'commentPostBox hidden', 'id' => 'commentPostBox_'.$id),
	'button_attrs' => array(
		'class' => 'js-ajax-postComment btn-sm',
		'id' => 'btn_comment_'.$id,
		'data-post_uri' => 'thread/comment/api/create/'.$id.'.json',
		'data-get_uri' => 'thread/comment/api/list/'.$id.'.json',
		'data-list' => '#comment_list_'.$id,
		'data-template' => '#comment-template',
		'data-counter' => '#comment_count_'.$id,
		'data-latest' => 1,
	),
	'textarea_attrs' => array('id' => 'textarea_comment_'.$id),
)); ?>
<?php endif; ?>

	</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($next_page): ?>
<nav id="page-nav">
<?php
$uri = sprintf('thread/api/list.html?page=%d', $next_page);
if (!empty($member))   $uri .= '&member_id='.$member->id;
if (!empty($is_draft)) $uri .= '&is_draft='.$is_draft;
?>
<a href="<?php echo Uri::base_path($uri); ?>"></a>
</nav>
<?php endif; ?>

<?php if (IS_API): ?></body></html><?php endif; ?>
