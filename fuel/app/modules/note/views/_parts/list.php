<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php echo term('note'); ?>がありません。
<?php else: ?>
<div id="article_list">
<?php foreach ($list as $id => $note): ?>
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
			<h4<?php if (!$note->is_published): ?> class="has_label"<?php endif; ?>>
				<?php echo Html::anchor('note/'.$id, strim($note->title, conf('view_params_default.list.trim_width.title'))); ?>
<?php if (!$note->is_published): ?>
				<?php echo label(term('form.draft'), null); ?>
<?php endif; ?>
			</h4>
			<div class="list_subtitle">
<?php echo render('_parts/member_contents_box', array(
	'member' => $note->member,
	'model' => 'note',
	'id' => $id,
	'size' => 'M',
	'public_flag' => $note->public_flag,
	'date' => array('datetime' => $note->published_at ? $note->published_at : $note->updated_at)
)); ?>
<?php
$dropdown_btn_group_attr = array(
	'id' => 'btn_dropdown_'.$id,
	'class' => array('dropdown', 'boxBtn'),
);
$dropdown_btn_attr = array(
	'class' => 'js-dropdown_content_menu',
	'data-uri' => sprintf('note/api/menu/%d.html', $id),
	'data-member_id' => $note->member_id,
	'data-menu' => '#menu_'.$note->id,
	'data-loaded' => 0,
);
$menus = array(array('icon_term' => 'site.show_detail', 'href' => 'note/'.$id));
echo btn_dropdown('noterm.dropdown', $menus, false, 'xs', null, true, $dropdown_btn_group_attr, $dropdown_btn_attr, false);
?>
			</div><!-- list_subtitle -->
		</div><!-- header -->
		<div class="body">
			<?php echo convert_body($note->body, array(
				'truncate_line' => conf('view_params_default.list.truncate_lines.body'),
				'read_more_uri' => 'note/'.$id,
			)); ?>
		</div>
<?php if (Module::loaded('album') && $images = \Note\Model_NoteAlbumImage::get_album_image4note_id($id, 4, array('id' => 'desc'))): ?>
<?php echo render('_parts/thumbnails', array('images' => array('list' => $images, 'additional_table' => 'note', 'size' => 'N_M', 'column_count' => 4))); ?>
<?php endif; ?>

<?php if ($note->is_published): ?>
<?php
// note_comment
list($comments, $comment_next_id, $all_comment_count)
	= \Note\Model_NoteComment::get_list(array('note_id' => $id), conf('view_params_default.list.comment.limit'), true, false, 0, 0, null, false, true);
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
<?php if (conf('like.isEnabled')): ?>
<?php
$data_like_link = array(
	'id' => $id,
	'post_uri' => \Site_Util::get_api_uri_update_like('note', $id),
	'get_member_uri' => \Site_Util::get_api_uri_get_liked_members('note', $id),
	'count_attr' => array('class' => 'unset_like_count'),
	'count' => $note->like_count,
	'left_margin' => true,
	'is_liked' => isset($liked_note_ids) && in_array($id, $liked_note_ids),
);
echo render('_parts/like/count_and_link_execute', $data_like_link);
?>
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
	'parent' => $note,
	'list' => $comments,
	'next_id' => $comment_next_id,
	'delete_uri' => 'note/comment/api/delete.json',
	'counter_selector' => '#comment_count_'.$id,
	'list_more_box_attrs' => array(
		'id' => 'listMoreBox_comment_'.$id,
		'data-uri' => sprintf('note/comment/api/list/%s.json', $id),
		'data-list' => '#comment_list_'.$id,
		'data-template' => '#comment-template',
	),
	'like_api_uri_prefix' => 'note/comment',
	'liked_ids' => (conf('like.isEnabled') && \Auth::check()) ? \Site_Model::get_liked_ids('note_comment', $u->id, $comments) : array(),
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
		'data-post_uri' => 'note/comment/api/create/'.$id.'.json',
		'data-get_uri' => 'note/comment/api/list/'.$id.'.json',
		'data-list' => '#comment_list_'.$id,
		'data-template' => '#comment-template',
		'data-counter' => '#comment_count_'.$id,
		'data-latest' => 1,
	),
	'textarea_attrs' => array('id' => 'textarea_comment_'.$id),
)); ?>
<?php endif; ?>

<?php endif; ?>
	</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($next_page): ?>
<nav id="page-nav">
<?php
$uri = sprintf('note/api/list.html?page=%d', $next_page);
if (!empty($member))   $uri .= '&member_id='.$member->id;
if (!empty($is_draft)) $uri .= '&is_draft='.$is_draft;
?>
<a href="<?php echo Uri::base_path($uri); ?>"></a>
</nav>
<?php endif; ?>

<?php if (IS_API): ?></body></html><?php endif; ?>
