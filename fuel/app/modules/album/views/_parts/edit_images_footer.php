<?php echo render('_parts/datetimepicker_footer', array('attr' => '#shot_at', 'max_date' => 'now')); ?>
<?php if (is_enabled_map('edit_images', 'album')): ?>
<script src="//maps.google.com/maps/api/js?sensor=true"></script>
<script src="//raw.githubusercontent.com/HPNeo/gmaps/master/gmaps.js"></script>
<?php echo Asset::js('site/common/map.js');?>
<?php echo render('_parts/map/handlebars_template/marker_image'); ?>
<?php endif; ?>
