<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_API_KEY; ?>"></script>
<?php echo Asset::js('gmaps.js', null, null, false, false, true);?>
<?php echo Asset::js('site/common/map.js');?>
<?php if (!empty($is_load_template)): ?>
<?php echo render('_parts/map/handlebars_template/marker_image'); ?>
<?php endif; ?>
