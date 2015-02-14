<?php if ($locations): ?>
<script src="//maps.google.com/maps/api/js?sensor=true"></script>
<script src="//raw.githubusercontent.com/HPNeo/gmaps/master/gmaps.js"></script>
<script>
window.onload = function(){
	var map = new GMaps({
		div: "#map",
		lat: <?php echo $locations[0]; ?>,
		lng: <?php echo $locations[1]; ?>,
		zoom: 15,
	});
	map.addMarker({
		lat: <?php echo $locations[0]; ?>,
		lng: <?php echo $locations[1]; ?>,
		title: '<?php echo $album_image->name; ?>',
		//click: function(e) {
		//	alert('You clicked in this marker');
		//}
		infoWindow: {
			content: '<?php echo img($album_image->get_image(), 'M', 'album/image/'.$album_image->id, false, $album_image->name ?: ''); ?>'
		}
	});
};
</script>
<?php endif; ?>
<?php echo render('_parts/comment/handlebars_template'); ?>
