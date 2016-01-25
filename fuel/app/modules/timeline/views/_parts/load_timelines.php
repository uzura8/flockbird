<?php echo render('_parts/comment/load_template'); ?>
<?php echo render('timeline::_parts/handlebars_template/list/dropdown_menu', array('size' => empty($size) ? 'S' : strtoupper($size))); ?>
<?php echo Asset::js('site/modules/timeline/common/load_timeline.js');?>

