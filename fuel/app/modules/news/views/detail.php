<p class="article_body"><?php echo nl2br($news->body) ?></p>

<?php echo render('_parts/thumbnails', array('is_display_name' => true, 'images' => array('list' => $images, 'file_cate' => 'nw', 'size' => 'M', 'column_count' => 3))); ?>