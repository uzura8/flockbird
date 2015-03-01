<?php if (is_enabled_map('image/detail', 'album')): ?>
<?php echo render('_parts/map/load_common_js', array('is_load_template' => true)); ?>
<?php endif; ?>

<?php echo render('_parts/comment/handlebars_template'); ?>

