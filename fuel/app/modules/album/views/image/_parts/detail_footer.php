<?php if (is_enabled_map('image/detail', 'album')): ?>
<script src="//maps.google.com/maps/api/js?sensor=true"></script>
<script src="//raw.githubusercontent.com/HPNeo/gmaps/master/gmaps.js"></script>
<?php echo Asset::js('site/common/map.js');?>
<?php echo render('_parts/map/handlebars_template/marker_image'); ?>
<?php endif; ?>

<?php echo render('_parts/comment/handlebars_template'); ?>

