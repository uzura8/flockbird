<?php echo render('_parts/datetimepicker_footer', array('attr' => '#shot_at_time', 'max_date' => 'now')); ?>

<?php if (is_enabled_map('image/edit', 'album')): ?>
<?php echo render('_parts/map/load_common_js', array('is_load_template' => false)); ?>
<?php endif; ?>
