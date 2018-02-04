<div class="article_body">
<?php echo convert_body_by_format($html_body, $news->format); ?>
</div>

<?php if (!conf('image.isInsertBody', 'news')): ?>
<?php echo render('_parts/thumbnails', array('is_display_name' => true, 'images' => array('list' => $images, 'file_cate' => 'nw', 'size' => 'M', 'column_count' => 3))); ?>
<?php endif; ?>
<?php echo render('_parts/file_links', array('title' => t('site.file.view'), 'list' => $files, 'file_cate' => 'nw', 'split_criterion_id' => $news->id)); ?>
<?php echo render('_parts/links', array('title' => t('site.link'), 'list' => $news->news_link)); ?>

<?php if (Config::get('news.tags.isEnabled') && $tags): ?>
<?php echo render('_parts/tags', array('tags' => $tags)); ?>
<?php endif; ?>

<!-- share button -->
<?php $share_configs = conf(sprintf('%s.news.shareButton', is_admin() ? 'admin' : 'site'), 'page', array()); ?>
<?php if ($news->is_published && ! empty($share_configs['isEnabled'])): ?>
<div class="comment_info">
<?php
	$disableds = array();
	if (empty($share_configs['twitter']['isEnabled'])) $disableds[] = 'twitter';
	if (empty($share_configs['google']['isEnabled'])) $disableds[] = 'google';
	if (empty($share_configs['line']['isEnabled'])) $disableds[] = 'line';
	if (empty($share_configs['facebook']['share']['isEnabled'])) $disableds[] = 'facebook';
	echo render('_parts/services/share', array(
		'disableds' => $disableds,
		'uri' => 'news/detail/'.$news->slug,
		'text' => $news->title,
	));
?>
</div>
<?php endif; ?>
