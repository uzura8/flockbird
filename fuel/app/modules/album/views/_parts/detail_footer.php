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
	var baseUrl = '<?php echo Uri::base(false); ?>';
	$.ajax({
		url : baseUrl + 'album/image/api/set_cover.json',
		dataType : 'text',
		data : {'id': album_image_id, '<?php echo Config::get('security.csrf_token_key'); ?>': '<?php echo Util_security::get_csrf(); ?>'},
		type : 'POST',
		success: function(status_after){
			$.jGrowl('カバー写真を設定しました。');
		},
		error: function(){
			$.jGrowl('カバー写真の設定に失敗しました。');
		}
	});
}
</script>
