<?php $is_api_request = Site_Util::check_is_api_request(); ?>
<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php if (!$is_api_request): ?><?php echo \Config::get('term.album'); ?>がありません。<?php endif; ?>
<?php else: ?>
<div class="row">
<div id="main_container">
<?php foreach ($list as $album): ?>
	<div class="main_item" id="main_item_<?php echo $album->id; ?>">
		<div class="imgBox" id="imgBox_<?php echo $album->id ?>"<?php if (!IS_SP): ?> onmouseover="$('#btn_album_edit_<?php echo $album->id ?>').show();" onmouseout="$('#btn_album_edit_<?php echo $album->id ?>').hide();"<?php endif; ?>>
			<?php echo img(\Album\Site_Util::get_album_cover_filename($album->cover_album_image_id, $album->id), img_size('ai', 'M'), 'album/'.$album->id); ?>
			<h5><?php echo Html::anchor('album/'.$album->id, strim($album->name, \Config::get('album.articles.trim_width.name'))); ?></h5>
<?php $disable_to_update = \Album\Site_Util::check_album_disabled_to_update($album->foreign_table); ?>
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
	'disabled_to_update' => $disable_to_update,
	'have_children_public_flag' => true,
	'child_model'    => 'album_image',
)); ?>
			</div><!-- date_box -->
<?php else: ?>
<?php echo render('_parts/member_contents_box', array(
	'member'      => $album->member,
	'id'          => $album->id,
	'public_flag' => $album->public_flag,
	'public_flag_view_icon_only' => true,
	'public_flag_disabled_to_update' => $disable_to_update,
	'have_children_public_flag'  => true,
	'model'       => 'album',
	'date'        => array('datetime' => $album->created_at),
	'child_model' => 'album_image',
)); ?>
<?php endif; ?>
<?php $album_image_count = (int)\Album\Model_AlbumImage::get_count4album_id($album->id); ?>
			<div class="article">
				<div class="body"><?php echo nl2br(strim($album->body, \Config::get('album.articles.trim_width.body'))) ?></div>
				<small><?php echo render('_parts/image_count_link', array('count' => $album_image_count, 'uri' => 'album/slide/'.$album->id.'#slidetop')); ?></small>
<?php if (!$disable_to_update && Auth::check() && $album->member_id == $u->id): ?>
					<div class="btn_album_edit btn-group" data-toggle="dropdown" id="btn_album_edit_<?php echo $album->id ?>">
<?php if (\Config::get('album.display_setting.member.display_delete_link')): ?>
						<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span class="glyphicon glyphicon-edit"></span><span class="caret"></span></button>
						<ul class="dropdown-menu">
							<li><?php echo Html::anchor('album/edit/'.$album->id, '<i class="icon-pencil"></i> 編集'); ?></li>
							<li><a href="#" onclick="delete_item('album/api/delete.json', <?php echo $album->id; ?>, '#main_item');return false;"><i class="icon-trash"></i> 削除</a></li>
						</ul>
<?php else: ?>
					<?php echo Html::anchor('album/edit/'.$album->id, '<i class="ls-icon-edit mrlr10"></i>', array('class' => 'btn btn-default btn-xs')); ?>
<?php endif; ?>
					</div><!-- /btn-group -->
<?php endif; ?>
			</div><!-- article -->
		</div><!-- img_box -->
	</div><!-- main_item -->
<?php endforeach; ?>
</div><!-- main_container. -->
</div><!-- row -->
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
