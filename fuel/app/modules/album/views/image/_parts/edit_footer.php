<?php echo render('_parts/datetimepicker_footer', array('attr' => '#shot_at', 'max_date' => 'now')); ?>

<?php if (is_enabled_map('image/edit', 'album')): ?>
<?php echo render('_parts/map/load_common_js', array('is_load_template' => false)); ?>
<?php endif; ?>
