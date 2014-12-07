<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if (!$list): ?>
<?php if (!IS_API): ?><?php echo term('album'); ?>がありません。<?php endif; ?>
<?php else: ?>
<div class="row">
<div id="main_container">
<?php foreach ($list as $album): ?>
<?php
if (empty($before_album_member_id) || $album->member_id != $before_album_member_id)
{
	$access_from = \Site_Member::get_access_from_member_relation($album->member_id, \Auth::check() ? $u->id : 0);
}
$before_album_member_id = $album->member_id;
?>
	<div class="js-hide-btn main_item" id="main_item_<?php echo $album->id; ?>" data-hidden_btn="btn_dropdown_<?php echo $album->id; ?>">
		<div class="imgBox" id="imgBox_<?php echo $album->id ?>">
			<div class="content"><?php echo img(
				\Album\Model_AlbumImage::get_album_cover_filename($album->cover_album_image_id, $album->id, $access_from),
				'M',
				'album/'.$album->id
			); ?></div>
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
<?php
$album_image_count = \Album\Model_AlbumImage::get_list_count(array(
	'where' => \Site_Model::get_where_public_flag4access_from(
		$access_from,
		array(array('album_id', $album->id))
	),
));
?>
			<div class="article">
				<div class="body"><?php echo nl2br(strim($album->body, \Config::get('album.articles.trim_width.body'))) ?></div>
				<small><?php echo render('_parts/image_count_link', array('count' => $album_image_count, 'uri' => 'album/slide/'.$album->id.'#slidetop')); ?></small>
			</div><!-- article -->
<?php
$dropdown_btn_group_attr = array(
	'id' => 'btn_dropdown_'.$album->id,
	'class' => array('dropdown', 'boxBtn'),
);
$get_uri = sprintf('album/api/menu/%d.html', $album->id);
$dropdown_btn_attr = array(
	'class' => 'js-dropdown_content_menu',
	'data-uri' => sprintf('album/api/menu/%d.html', $album->id),
	'data-member_id' => $album->member_id,
	'data-loaded' => 0,
);
$menus = array(array('icon_term' => 'site.show_detail', 'href' => 'album/'.$album->id));
echo btn_dropdown('noterm.dropdown', $menus, false, 'xs', null, true, $dropdown_btn_group_attr, $dropdown_btn_attr, false);
?>
		</div><!-- img_box -->
	</div><!-- main_item -->
<?php endforeach; ?>
</div><!-- main_container. -->
</div><!-- row -->
<?php endif; ?>

<?php if (!empty($next_page)): ?>
<nav id="page-nav">
<?php
$uri = sprintf('album/api/list.html?page=%d', $next_page);
if (!empty($member)) $uri .= '&member_id='.$member->id;
if (!empty($is_member_page)) $uri .= '&is_member_page=1';
echo Html::anchor($uri, '');
?>
</nav>
<?php endif; ?>

<?php if (IS_API): ?></body></html><?php endif; ?>
