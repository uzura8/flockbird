<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if ($list): ?>
<div class="row">
<div id="main_container">
<?php foreach ($list as $album_image): ?>
	<div class="main_item js-hide-btn" id="main_item_<?php echo $album_image->id; ?>" data-hidden_btn="btn_album_image_edit_<?php echo $album_image->id; ?>">
		<div class="imgBox" id="imgBox_<?php echo $album_image->id ?>"<?php if (!IS_SP): ?> onmouseover="$('#btn_album_image_edit_<?php echo $album_image->id ?>').show();" onmouseout="$('#btn_album_image_edit_<?php echo $album_image->id ?>').hide();"<?php endif; ?>>
			<div class="content"><?php echo img($album_image->file, img_size('ai', 'M'), 'album/image/'.$album_image->id); ?></div>
<?php if (!empty($is_simple_view)): ?>
			<div class="description">
				<small><?php echo strim(\Album\Site_Util::get_album_image_display_name($album_image)); ?></small>
			</div>
<?php else: ?>
			<h5><?php echo Html::anchor('album/image/'.$album_image->id, strim(\Album\Site_Util::get_album_image_display_name($album_image), Config::get('album.articles.trim_width.name'))); ?></h5>
<?php endif; ?>

<?php if (empty($is_simple_view)): ?>
<?php if (!empty($is_member_page)): ?>
		<div class="date_box">
			<small><?php echo site_get_time($album_image->created_at) ?></small>
<?php $is_mycontents = Auth::check() && $u->id == $album_image->album->member_id; ?>
<?php echo render('_parts/public_flag_selecter', array(
	'model'          => 'album_image',
	'id'             => $album_image->id,
	'public_flag'    => $album_image->public_flag,
	'is_mycontents'  => $is_mycontents,
	'view_icon_only' => true,
	'disabled_to_update' => \Album\Site_Util::check_album_disabled_to_update($album_image->album->foreign_table),
)); ?>
		</div>
<?php else: ?>
<?php echo render('_parts/member_contents_box', array(
	'member'      => $album_image->album->member,
	'id'          => $album_image->id,
	'public_flag' => $album_image->public_flag,
	'public_flag_view_icon_only' => true,
	'public_flag_disabled_to_update' => \Album\Site_Util::check_album_disabled_to_update($album_image->album->foreign_table),
	'model'       => 'album_image',
	'date'        => array('datetime' => $album_image->album->created_at)
)); ?>
<?php endif; ?>
<?php endif; ?>

<?php if (empty($is_simple_view)): ?>
			<div class="article">
<?php if (empty($album)): ?>
				<div class="subinfo">
					<small><?php echo term('album'); ?>: <?php echo Html::anchor('album/'.$album_image->album->id, strim($album_image->album->name, Config::get('album.articles.trim_width.subinfo'))); ?></small>
				</div>
<?php endif; ?>

<?php
// comment
list($comments, $comment_next_id, $all_comment_count)
	= \Album\Model_AlbumImageComment::get_list(array('album_image_id' => $album_image->id), \Config::get('album.articles.comment.limit'), true, false, 0, 0, null, false, true);
?>
				<div class="comment_info">
					<small><span class="glyphicon glyphicon-comment"></span> <?php echo $all_comment_count; ?></small>
<?php if (Auth::check()): ?>
					<small><?php echo Html::anchor('album/image/'.$album_image->id.'?write_comment=1#comments', 'コメントする'); ?></small>
<?php endif; ?>
				</div>
			</div><!-- article -->
<?php endif; ?>

<?php if (Auth::check()): ?>
<?php
$menus = array();
if (!empty($is_setting_profile_image) && $album_image->album->member_id == $u->id)
{
	if ($album_image->file->id != $u->file_id)
	{
		$menus[] = array('icon_term' => 'form.set_profile_image', 'href' => '#', 'attr' => array(
			'class' => 'js-simplePost',
			'data-uri' => 'member/profile/image/set/'.$album_image->id,
			'data-msg' => 'プロフィール写真に設定しますか？',
		));
	}
	$menus[] = array('icon_term' => 'form.do_delete', 'href' => '#', 'attr' => array(
		'class' => 'js-simplePost',
		'data-uri' => 'member/profile/image/delete/'.$album_image->id,
	));
}
elseif (((!empty($album) && $album->member_id == $u->id) || (!empty($member) && $member->id == $u->id)))
{
	$menus[] = array('icon_term' => 'form.do_edit', 'href' => 'album/image/edit/'.$album_image->id);
	if ($album_image->album->cover_album_image_id == $album_image->id)
	{
		$menus[] = array('tag' => 'disabled', 'icon_term' => 'form.set_cover');
	}
	else
	{
		$menus[] = array('icon_term' => 'form.set_cover', 'attr' => array(
			'class' => 'link_album_image_set_cover',
			'id' => 'link_album_image_set_cover_'.$album_image->id,
		));
	}
	$menus[] = array('icon_term' => 'form.do_delete', 'attr' => array(
		'class' => 'js-ajax-delete',
		'data-parent' => 'main_item_'.$album_image->id,
		'data-uri' => 'album/image/api/delete/'.$album_image->id.'.json',
	));
}
if ($menus)
{
	echo btn_dropdown('form.edit', $menus, false, 'xs', null, true, array('class' => 'btn_album_image_edit', 'id' => 'btn_album_image_edit_'.$album_image->id));
}
?>
<?php endif; ?>
		</div><!-- imgBox -->

<?php if (empty($is_simple_view ) && $comments): ?>
<?php
$comment_list_attr = array(
	'class' => 'comment_list list_album_image_comment',
	'id' => 'comment_list_'.$album_image->id,
);
?>
		<div <?php echo Util_Array::conv_array2attr_string($comment_list_attr); ?>>
<?php
$data = array(
	'parent' => (!empty($album)) ? $album : $album_image->album,
	'list' => $comments,
	'next_id' => $comment_next_id,
	'uri_for_all_comments' => sprintf('album/image/%d?limit=all#comments', $album_image->id),
	'delete_uri' => 'album/image/comment/api/delete.json',
	'trim_width' => Config::get('album.articles.comment.trim_width'),
	'counter_selector' => '#comment_count_'.$id,
	'list_more_box_attrs' => array(
		'id' => 'listMoreBox_comment_'.$id,
		'data-uri' => sprintf('album/image/comment/api/list/%s.html', $id),
		'data-list' => '#comment_list_'.$id,
	),
);
echo render('_parts/comment/list', $data);
?>
		</div>
<?php endif; ?>
	</div><!-- main_item -->
<?php endforeach; ?>
</div><!-- main_container -->
</div><!-- row -->
<?php endif; ?>

<?php if (empty($is_simple_view)): ?>
<nav id="page-nav">
<?php
$uri = sprintf('album/image/api/list.html?page=%d', $page + 1);
if (!empty($album))
{
	$uri .= '&album_id='.$album->id;
}
elseif (!empty($member))
{
	$uri .= '&member_id='.$member->id;
}
if (!empty($is_member_page))
{
	$uri .= '&is_member_page='.$is_member_page;
}
echo Html::anchor($uri, '');
?>
</nav>
<?php endif; ?>

<?php if (IS_API): ?></body></html><?php endif; ?>
