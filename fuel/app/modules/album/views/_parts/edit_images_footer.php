<?php echo render('_parts/datetimepicker_footer', array('attr' => '#shot_at', 'max_date' => 'now')); ?>

<?php if (is_enabled_map('edit_images', 'album')): ?>
<?php echo render('_parts/map/load_common_js', array('is_load_template' => false)); ?>
<?php endif; ?>
