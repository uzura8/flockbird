<script src="//maps.google.com/maps/api/js?sensor=true"></script>
<script src="//raw.githubusercontent.com/HPNeo/gmaps/master/gmaps.min.js"></script>
<?php echo Asset::js('site/common/map.js');?>
<?php if (!empty($is_load_template)): ?>
<?php echo render('_parts/map/handlebars_template/marker_image'); ?>
<?php endif; ?>
