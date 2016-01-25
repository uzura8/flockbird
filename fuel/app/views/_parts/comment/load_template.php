<?php echo render('_parts/comment/handlebars_template'); ?>
<?php 	if (Auth::check()): ?>
<?php echo render('_parts/handlebars_template/post_comment', array('size' => empty($size) ? 'S' : strtoupper($size))); ?>
<?php 	endif; ?>

