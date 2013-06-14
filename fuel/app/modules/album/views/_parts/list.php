<?php $is_api_request = Site_util::check_is_api_request(); ?>
<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><?php endif; ?>
<?php if (!$albums): ?>
<?php if (!$is_api_request): ?><?php echo \Config::get('album.term.album'); ?>がありません。<?php endif; ?>
<?php else: ?>
<div class="row-fluid">
<div id="main_container" class="span12">
<?php $i = 0; ?>
<?php foreach ($albums as $album): ?>
	<div class="main_item">
		<?php echo img(\Album\Site_util::get_album_cover_filename($album->cover_album_image_id, $album->id), img_size('ai', 'M'), 'album/'.$album->id); ?>
		<h5><?php echo Html::anchor('album/'.$album->id, $album->name); ?></h5>

		<div class="member_img_box_s">
			<?php echo img((!empty($album->member))? $album->member->get_image() : 'm', '30x30', (empty($album->member))? null : 'member/'.$album->member_id); ?>
			<div class="content">
				<div class="main">
					<b class="fullname"><?php echo (empty($album->member))? Config::get('site.term.left_member') : Html::anchor('member/'.$album->member_id, $album->member->name); ?></b>
				</div>
				<small><?php echo site_get_time($album->created_at, 'Y年n月j日'); ?></small>
			</div>
		</div>
		<div class="article">
			<div class="body"><?php echo nl2br(mb_strimwidth($album->body, 0, \Config::get('album.article_list.trim_width'), '...')) ?></div>
			<small>
<?php if ($album_image_count = \Album\Model_AlbumImage::get_count4album_id($album->id)): ?>
				<?php echo Html::anchor('album/slide/'.$album->id.'#slidetop', '<i class="icon-picture"></i> '.$album_image_count.' 枚'); ?>
<?php else: ?>
				<i class="icon-picture"></i> 0 枚
<?php endif; ?>
			</small>
		</div>
	</div>
<?php $i++; ?>
<?php endforeach; ?>
</div>
</div>

<?php if ($is_next): ?>
<nav id="page-nav">
<?php
$uri = sprintf('album/api/list.html?page=%d&amp;nocache=%s', $page + 1, time());
if ($member_id) $uri .= '&amp;member_id='.$member_id;
?>
	<a href="<?php echo Uri::create($uri); ?>"></a>
</nav>
<?php endif; ?>
<?php endif; ?>
