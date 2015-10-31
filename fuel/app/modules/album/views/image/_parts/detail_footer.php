<?php if (conf('display_setting.image.detail.displayGallery.isEnabled', 'album')): ?>
<?php echo render('_parts/garraly/footer_script', array('slide_file_names' => $slide_file_names)); ?>
<?php endif; ?>

<?php if (is_enabled_map('image/detail', 'album')): ?>
<?php echo render('_parts/map/load_common_js', array('is_load_template' => true)); ?>
<?php endif; ?>

<?php echo render('_parts/comment/handlebars_template'); ?>

