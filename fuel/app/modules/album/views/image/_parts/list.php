<?php $is_api_request = Site_Util::check_is_api_request(); ?>
<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php if (!$is_api_request): ?><?php echo Config::get('term.album_image'); ?>がありません。<?php endif; ?>
<?php else: ?>
<div class="row-fluid">
<div id="main_container">
<?php foreach ($list as $album_image): ?>
	<div class="main_item" id="main_item_<?php echo $album_image->id; ?>">
		<div class="imgBox" id="imgBox_<?php echo $album_image->id ?>"<?php if (!Agent::is_smartphone()): ?> onmouseover="$('#btn_album_image_edit_<?php echo $album_image->id ?>').show();" onmouseout="$('#btn_album_image_edit_<?php echo $album_image->id ?>').hide();"<?php endif; ?>>
			<div><?php echo img($album_image->file->name, img_size('ai', 'M'), 'album/image/'.$album_image->id); ?></div>
			<h5><?php echo Html::anchor('album/image/'.$album_image->id, strim(\Album\Site_Util::get_album_image_display_name($album_image), Config::get('album.articles.trim_width.name'))); ?></h5>
			<div class="article">
<?php if (empty($album)): ?>
			<div class="subinfo"><small><?php echo Config::get('term.album'); ?>: <?php echo Html::anchor('album/'.$album_image->album->id, strim($album_image->album->name, Config::get('album.articles.trim_width.subinfo'))); ?></small></div>
<?php endif; ?>
<?php list($album_image_comment, $is_all_records, $all_comment_count) = \Album\Model_AlbumImageComment::get_comments($album_image->id, \Config::get('album.articles.comment.limit')); ?>
			<div class="comment_info">
				<small><i class="icon-comment"></i> <?php echo $all_comment_count; ?></small>
<?php if (Auth::check()): ?>
				<small><?php echo Html::anchor('album/image/'.$album_image->id.'?write_comment=1#comments', 'コメントする'); ?></small>
<?php endif; ?>
			</div>
<?php if (Auth::check() && ((!empty($album) && $album->member_id == $u->id) || (!empty($member) && $member->id == $u->id))): ?>
				<div class="btn-group btn_album_image_edit" id="btn_album_image_edit_<?php echo $album_image->id ?>">
					<button data-toggle="dropdown" class="btn btn-mini dropdown-toggle"><i class="icon-edit"></i></button>
					<ul class="dropdown-menu pull-right">
						<li><?php echo Html::anchor('album/image/edit/'.$album_image->id, '<i class="icon-pencil"></i> 編集'); ?></li>
						<li><a href="#" class="link_album_image_set_cover" id="link_album_image_set_cover_<?php echo $album_image->id; ?>"><i class="icon-book"></i> カバーに指定</a></li>
						<li><a href="#" onclick="delete_item('album/image/api/delete.json', <?php echo $album_image->id; ?>, '#main_item');return false;"><i class="icon-trash"></i> 削除</a></li>
					</ul>
				</div><!-- /btn-group -->
<?php endif; ?>
			</div>
		</div>

<?php if ($album_image_comment): ?>
		<div class="list_album_image_comment">
<?php echo render('_parts/comment/list', array(
	'parent' => (!empty($album)) ? $album : $album_image->album,
	'comments' => $album_image_comment,
	'is_all_records' => $is_all_records,
	'trim_width' => Config::get('album.articles.comment.trim_width'),
)); ?>
<?php if (!$is_all_records): ?>
			<div class="listMoreBox"><a href="<?php echo Uri::create(sprintf('album/image/%d?all_comment=1#comments', $album_image->id)); ?>">もっと見る</a></div>
<?php endif; ?>
		</div>
<?php endif; ?>
	</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<?php if ($is_next): ?>
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
echo Html::anchor($uri, '');
?>
</nav>
<?php endif; ?>

<?php if ($is_api_request): ?></body></html><?php endif; ?>
