<div class="article_body">
<?php if ($news->format == 1): ?>
<?php 	echo $html_body ?>
<?php elseif ($news->format == 2): ?>
<?php 	echo Markdown::parse($html_body); ?>
<?php else: ?>
<?php 	echo nl2br($news->body) ?>
<?php endif; ?>
</div>

<?php if (!conf('image.isInsertBody', 'news')): ?>
<?php echo render('_parts/thumbnails', array('is_display_name' => true, 'images' => array('list' => $images, 'file_cate' => 'nw', 'size' => 'M', 'column_count' => 3))); ?>
<?php endif; ?>
<?php echo render('_parts/file_links', array('title' => term('site.file'), 'list' => $files, 'file_cate' => 'nw', 'split_criterion_id' => $news->id)); ?>
<?php echo render('_parts/links', array('title' => term('site.link'), 'list' => $news->news_link)); ?>
