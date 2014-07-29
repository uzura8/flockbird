<?php echo Asset::js('site/modules/timeline/common/load_timeline.js');?>
<?php if (Auth::check()): ?>
<?php echo render('_parts/handlebars_template/post_comment', array('size' => empty($size) ? 'S' : strtoupper($size))); ?>
<?php endif; ?>
<?php echo render('_parts/handlebars_template/list/dropdown_menu', array('size' => empty($size) ? 'S' : strtoupper($size))); ?>
