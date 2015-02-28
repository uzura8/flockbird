<?php
$data = array('image_obj' => $album_image);
if ($before_id) $data['before_uri'] = 'album/image/'.$before_id;
if ($after_id) $data['after_uri'] = 'album/image/'.$after_id;
echo render('_parts/image/detail', $data);
?>
<hr>

<?php if (Auth::check() || $comments): ?>
<h3 id="comments">Comments</h3>
<?php endif; ?>

<div class="comment_info">
<?php // comment_count_and_link
echo render('_parts/comment/count_and_link_display', array(
	'id' => $album_image->id,
	'count' => $all_comment_count,
	'link_hide_absolute' => true,
)); ?>

<?php // like_count_and_link ?>
<?php if (conf('like.isEnabled') && Auth::check()): ?>
<?php
$data_like_link = array(
	'id' => $album_image->id,
	'post_uri' => \Album\Site_Util::get_like_api_uri($album_image->id),
	'get_member_uri' => \Site_Util::get_api_uri_get_liked_members('album_image', $album_image->id),
	'count_attr' => array('class' => 'unset_like_count'),
	'count' => $album_image->like_count,
	'left_margin' => true,
	'is_liked' => $is_liked_self,
);
echo render('_parts/like/count_and_link_execute', $data_like_link);
?>
<?php endif; ?>
</div><!-- .comment_info -->

<div id="comment_list">
<?php echo render('_parts/comment/list', array(
	'parent' => $album_image,
	'list' => $comments,
	'next_id' => $comment_next_id,
	'delete_uri' => 'album/image/comment/api/delete.json',
	'counter_selector' => '#comment_count_'.$album_image->id,
	'list_more_box_attrs' => array(
		'data-uri' => 'album/image/comment/api/list/'.$album_image->id.'.json',
		'data-template' => '#comment-template',
	),
	'like_api_uri_prefix' => 'album/image/comment',
	'liked_ids' => $liked_ids,
)); ?>
</div>

<?php if (Auth::check()): ?>
<?php echo render('_parts/comment/post', array('id' => $album_image->id, 'size' => 'M', 'textarea_attrs' => array('id' => 'textarea_comment_'.$album_image->id), 'button_attrs' => array(
	'data-post_uri' => 'album/image/comment/api/create/'.$album_image->id.'.json',
	'data-get_uri' => 'album/image/comment/api/list/'.$album_image->id.'.json',
	'data-list' => '#comment_list',
	'data-template' => '#comment-template',
	'data-counter' => '#comment_count_'.$album_image->id,
))); ?>
<?php endif; ?>

<?php if (is_enabled_map('image/detail', 'album')): ?>
<?php echo render('_parts/map/detail', array(
	'auther_member_id' => $album_image->album->member_id,
	'locations' => $locations,
	'save_uri' => \Album\Site_Util::get_save_location_api_uri($album_image->id),
	'markers' => $locations ? \Album\Site_Util::get_map_markers($locations) : array(),
	'marker_template' => '#map-marker-image-template',
	'marker_images' => array(
		'uri' => img_uri($album_image->get_image(), 'M'),
		'alt' => $album_image->name,
	),
)); ?>
<?php endif; ?>

