<p class="article_body">
<?php if (Config::get('news.form.isEnabledWysiwygEditor')): ?>
<?php echo $html_body ?>
<?php else: ?>
<?php echo nl2br($news->body) ?>
<?php endif; ?>
</p>

<?php echo render('_parts/thumbnails', array('is_display_name' => true, 'images' => array('list' => $images, 'file_cate' => 'nw', 'size' => 'M', 'column_count' => 3))); ?>
<?php echo render('_parts/file_links', array('title' => term('site.file'), 'list' => $files, 'file_cate' => 'nw', 'split_criterion_id' => $news->id)); ?>
<?php echo render('_parts/links', array('title' => term('site.link'), 'list' => $news->news_link)); ?>
