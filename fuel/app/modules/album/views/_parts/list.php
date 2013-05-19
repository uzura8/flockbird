<?php if ($list): ?>
<div class="row-fluid">
<div id="a_container" class="span12">
<?php foreach ($list as $id => $album): ?>
	<div class="a_item">
<?php

?>
		<?php echo img(\Album\Site_util::get_album_cover_filename($album->cover_album_image_id, $album->id), img_size('ai', 'M'), 'album/detail/'.$id); ?>
		<h5><?php echo Html::anchor('album/detail/'.$id, $album->name); ?></h5>

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
<?php if (\Album\Model_AlbumImage::get_count4album_id($album->id)): ?>
				<?php echo Html::anchor('album/slide/'.$album->id.'#slidetop', '<i class="icon-picture"></i> '.\Album\Model_AlbumImage::get_count4album_id($album->id).' 枚'); ?>
<?php else: ?>
				<i class="icon-picture"></i> <?php echo \Album\Model_AlbumImage::get_count4album_id($album->id); ?> 枚
<?php endif; ?>
			</small>
		</div>
	</div>
<?php endforeach; ?>
</div>
</div>
<?php else: ?>
<?php echo \Config::get('album.term.album'); ?>がありません。
<?php endif; ?>
