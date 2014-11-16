<?php if (IS_API): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>
<?php if ($list): ?>
<div class="row">
<div id="main_container">
<?php foreach ($list as $image): ?>
	<div class="main_item js-hide-btn" id="main_item_<?php echo $image->id; ?>" data-hidden_btn="btn_album_image_edit_<?php echo $image->id; ?>">
		<div class="imgBox" id="imgBox_<?php echo $image->id ?>"<?php if (!IS_SP): ?> onmouseover="$('#btn_album_image_edit_<?php echo $image->id ?>').show();" onmouseout="$('#btn_album_image_edit_<?php echo $image->id ?>').hide();"<?php endif; ?>>
			<div class="content"><?php echo img($image->get_image(), 'M', 'admin/content/image/'.$image->id); ?></div>
<?php if (!empty($is_simple_view) && $image->name): ?>
			<div class="description">
				<small><?php echo strim($image->name, Config::get('admin.articles.images.trim_width.name')); ?></small>
			</div>
<?php else: ?>
			<h5><?php echo Html::anchor('admin/content/image/'.$image->id, strim($image->name ?: $image->file_name, Config::get('admin.articles.images.trim_width.name'))); ?></h5>
<?php endif; ?>

<?php if (empty($is_simple_view)): ?>
			<div class="article">
			</div><!-- article -->
<?php endif; ?>
<?php
$menus = array();
$menus[] = array(
	'icon_term' => 'site.show_detail',
	'href' => 'admin/content/image/'.$image->id,
);
$menus[] = array('icon_term' => 'form.do_delete', 'attr' => array(
	'class' => 'js-ajax-delete',
	'data-parent' => 'main_item_'.$image->id,
	'data-uri' => 'admin/content/image/api/delete/'.$image->id.'.json',
));
echo btn_dropdown('form.edit', $menus, false, 'xs', null, true, array('class' => 'btn_album_image_edit', 'id' => 'btn_album_image_edit_'.$image->id));
?>
		</div><!-- imgBox -->

	</div><!-- main_item -->
<?php endforeach; ?>
</div><!-- main_container -->
</div><!-- row -->
<?php endif; ?>

<?php if (empty($is_simple_view) && !empty($next_page)): ?>
<nav id="page-nav">
<?php
$uri = sprintf('admin/content/image/api/list.html?page=%d', $next_page);
echo Html::anchor($uri, '');
?>
</nav>
<?php endif; ?>

<?php if (IS_API): ?></body></html><?php endif; ?>
