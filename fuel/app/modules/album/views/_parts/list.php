<?php $is_api_request = Site_Util::check_is_api_request(); ?>
<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php if (!$is_api_request): ?><?php echo \Config::get('term.album'); ?>がありません。<?php endif; ?>
<?php else: ?>
<div class="row-fluid">
<div id="main_container">
<?php foreach ($list as $album): ?>
	<div class="main_item" id="main_item_<?php echo $album->id ?>"<?php if (!Agent::is_smartphone()): ?> onmouseover="$('#btn_album_edit_<?php echo $album->id ?>').show();" onmouseout="$('#btn_album_edit_<?php echo $album->id ?>').hide();"<?php endif; ?>>
		<?php echo img(\Album\Site_Util::get_album_cover_filename($album->cover_album_image_id, $album->id), img_size('ai', 'M'), 'album/'.$album->id); ?>
		<h5><?php echo Html::anchor('album/'.$album->id, strim($album->name, \Config::get('album.articles.trim_width.name'))); ?></h5>

<?php if (!empty($is_member_page)): ?>
		<div class="date_box">
			<small><?php echo site_get_time($album->created_at) ?></small>
<?php $is_mycontents = Auth::check() && $u->id == $album->member_id; ?>
<?php echo render('_parts/public_flag_selecter', array(
	'model'          => 'album',
	'id'             => $album->id,
	'public_flag'    => $album->public_flag,
	'is_mycontents'  => $is_mycontents,
	'view_icon_only' => true,
	'have_children_public_flag' => true,
	'child_model'    => 'album_image',
)); ?>
		</div>
<?php else: ?>
<?php echo render('_parts/member_contents_box', array(
	'member'      => $album->member,
	'id'          => $album->id,
	'public_flag' => $album->public_flag,
	'public_flag_view_icon_only' => true,
	'have_children_public_flag'  => true,
	'model'       => 'album',
	'date'        => array('datetime' => $album->created_at),
	'child_model' => 'album_image',
)); ?>
<?php endif; ?>

		<div class="article">
			<div class="body"><?php echo nl2br(strim($album->body, \Config::get('album.articles.trim_width.body'))) ?></div>
			<small>
<?php if ($album_image_count = (int)\Album\Model_AlbumImage::get_count4album_id($album->id)): ?>
				<?php echo Html::anchor('album/slide/'.$album->id.'#slidetop', '<i class="icon-picture"></i> '.$album_image_count.' 枚'); ?>
<?php else: ?>
				<i class="icon-picture"></i> 0 枚
<?php endif; ?>
			</small>
<?php if (Auth::check() && $album->member_id == $u->id): ?>
				<div class="btn-group btn_album_edit" id="btn_album_edit_<?php echo $album->id ?>">
<?php if (\Config::get('album.display_setting.member.display_delete_link')): ?>
					<button data-toggle="dropdown" class="btn btn-mini dropdown-toggle"><i class="icon-edit"></i><span class="caret"/></button>
					<ul class="dropdown-menu">
						<li><?php echo Html::anchor('album/edit/'.$album->id, '<i class="icon-pencil"></i> 編集'); ?></li>
						<li><a href="#" onclick="delete_item('album/api/delete.json', <?php echo $album->id; ?>, '#main_item');return false;"><i class="icon-trash"></i> 削除</a></li>
					</ul>
<?php else: ?>
					<?php echo Html::anchor('album/edit/'.$album->id, '<i class="icon-edit mrlr10"></i>', array('class' => 'btn btn-mini')); ?>
<?php endif; ?>
				</div><!-- /btn-group -->
<?php endif; ?>
		</div>
	</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<nav id="page-nav">
<?php
$uri = sprintf('album/api/list.html?page=%d', $page + 1);
if (!empty($member)) $uri .= '&member_id='.$member->id;
if (!empty($is_member_page)) $uri .= '&is_member_page=1';
echo Html::anchor($uri, '');
?>
</nav>

<?php if ($is_api_request): ?></body></html><?php endif; ?>
