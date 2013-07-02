<?php $is_api_request = Site_Util::check_is_api_request(); ?>
<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php if (!$is_api_request): ?><?php echo \Config::get('album.term.album'); ?>がありません。<?php endif; ?>
<?php else: ?>
<div class="row-fluid">
<div id="main_container" class="span12">
<?php foreach ($list as $album): ?>
	<div class="main_item" id="main_item_<?php echo $album->id ?>"<?php if (!Agent::is_smartphone()): ?> onmouseover="$('#btn_album_edit_<?php echo $album->id ?>').show();" onmouseout="$('#btn_album_edit_<?php echo $album->id ?>').hide();"<?php endif; ?>>
		<?php echo img(\Album\Site_Util::get_album_cover_filename($album->cover_album_image_id, $album->id), img_size('ai', 'M'), 'album/'.$album->id); ?>
		<h5><?php echo Html::anchor('album/'.$album->id, $album->name); ?></h5>

		<?php echo render('_parts/member_contents_box', array('member' => $album->member, 'date' => array('datetime' => $album->created_at))); ?>
		<div class="article">
			<div class="body"><?php echo nl2br(strim($album->body, \Config::get('album.article_list.trim_width'))) ?></div>
			<small>
<?php if ($album_image_count = \Album\Model_AlbumImage::get_count4album_id($album->id)): ?>
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

<?php if (!$is_api_request && $is_next): ?>
<nav id="page-nav">
<?php
$uri = sprintf('album/api/list.html?hoge=%d', $page + 1);
if (!empty($member)) $uri .= '&member_id='.$member->id;
echo Html::anchor($uri, '');
?>
</nav>
<?php endif; ?>
<?php if ($is_api_request): ?></body></html><?php endif; ?>
