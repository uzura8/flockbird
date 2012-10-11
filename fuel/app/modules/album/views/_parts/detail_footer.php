<?php echo Asset::js('bootstrap-carousel.js');?>
<?php echo Asset::js('jquery.masonry.min.js');?>
<script type="text/javascript">
$('.carousel').carousel({
	interval: false
})

$('#ai_container').masonry({
	itemSelector : '.ai_item'
});

function set_cover(album_image_id) {
	$.post(
		baseUrl + 'album/set_cover_ajax/' + album_image_id,
		{
			'<?php echo Config::get('security.csrf_token_key'); ?>': '<?php echo Util_security::get_csrf(); ?>',
		},
		function(data) {
			$.jGrowl('カバー写真を設定しました。');
		}
	);
}
</script>
