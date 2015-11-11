<div class="article_body">
<?php echo convert_body($album->body, array('is_truncate' => false)); ?>
</div>

<?php if (Config::get('album.display_setting.detail.display_slide_image')): ?>
<?php 	if ($list): ?>
<div id="myCarousel" class="carousel carousel-flex slide" data-ride="carousel">
	<!-- Wrapper for slides -->
	<div class="carousel-inner">
<?php $i = 0; ?>
<?php 		foreach ($list as $album_image): ?>
		<div class="item<?php if (!$i): ?> active<?php endif; ?>">
			<?php echo img($album_image->get_image(), img_size('ai', 'L'), 'album/image/'.$album_image->id); ?>
<?php if (!empty($album_image->name)): ?>
			<div class="carousel-caption">
				<p><?php echo $album_image->name; ?></p>
			</div>
<?php endif; ?>
		</div>
<?php $i++; ?>
<?php 		endforeach; ?>
	</div>
	<!-- Controls -->
	<a class="left carousel-control" href="#myCarousel" data-slide="prev">
		<span class="glyphicon glyphicon-chevron-left"></span>
	</a>
	<a class="right carousel-control" href="#myCarousel" data-slide="next">
		<span class="glyphicon glyphicon-chevron-right"></span>
	</a>
</div>
<?php 	endif; ?>
<?php endif; ?>

<?php if (!empty($val)): ?>
<div class="well">
<h4><?php echo term('album_image', 'form.upload'); ?></h4>
<?php echo render('_parts/form/upload_form', array('form_attrs' => array('action' => 'album/upload_image/'.$id), 'with_public_flag' => true, 'val' => $val)); ?>
</div><!-- well -->
<?php endif; ?>

<div id="btn_menu">
<?php if ($list): ?>
<?php echo btn(sprintf('%sを見る', term('site.picture')), 'album/slide/'.$album->id, 'mr', true, null, null, null, 'picture', null, null, false); ?>
<?php endif; ?>
<?php if (Auth::check() && $album->member_id == $u->id): ?>
<?php if (!$disabled_to_update): ?>
<?php echo btn(sprintf('%sを%s', term('site.picture'), term('form.add')), 'album/upload/'.$album->id, 'mr', true, null, null, null, 'upload', null, null, false); ?>
<?php endif; ?>
<?php echo btn(term('site.picture', 'site.management'), 'album/edit_images/'.$album->id, null, true, null, null, null, 'th-list', null, null, false); ?>
<?php endif; ?>

<?php // Facebook feed ?>
<?php 	if (FBD_FACEBOOK_APP_ID && conf('service.facebook.shareDialog.album.isEnabled')): ?>
<?php echo render('_parts/facebook/share_btn', array(
	'images' => $list,
	'link_uri' => 'album/'.$album->id,
	'name' => $album->name,
	'description' => $album->body,
)); ?>
<?php 	endif; ?>

<!-- share button -->
<?php if (conf('site.common.shareButton.isEnabled', 'page')): ?>
<?php echo render('_parts/services/share', array('disableds' => array('facebook'))); ?>
<?php endif; ?>

</div>

<?php echo render('image/_parts/list', array(
	'id' => $id,
	'album' => $album,
	'list' => $list,
	'page' => $page,
	'next_page' => $next_page,
	'is_member_page' => $is_member_page,
	'liked_album_image_ids' => isset($liked_album_image_ids) ? $liked_album_image_ids : array(),
)); ?>
