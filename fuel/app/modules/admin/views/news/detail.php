<div class="article_body">
<?php echo convert_body_by_format($html_body, $news->format); ?>
</div>
<?php if (!conf('image.isInsertBody', 'news')): ?>
<?php echo render('_parts/thumbnails', array('is_display_name' => true, 'images' => array('list' => $images, 'file_cate' => 'nw', 'size' => 'M', 'column_count' => 3))); ?>
<?php endif; ?>
<?php echo render('_parts/file_links', array('title' => term('site.related', 'site.file'), 'list' => $files, 'file_cate' => 'nw', 'split_criterion_id' => $news->id)); ?>
<?php echo render('_parts/links', array('title' => term('site.related', 'site.link'), 'list' => $news->news_link)); ?>
<?php if (\Config::get('news.form.tags.isEnabled') && $tags): ?>
<?php echo render('_parts/tags', array('tags' => $tags)); ?>
<?php endif; ?>

