<?php echo Asset::js('site/modules/timeline/common/load_timeline.js');?>
<?php echo render('_parts/handlebars_template/post_comment', array('size' => empty($size) ? 'S' : strtoupper($size))); ?>
