<?php echo Asset::js('jquery.masonry.min.js');?>
<?php echo Asset::js('jquery.infinitescroll.min.js');?>
<script type="text/javascript">
$(function(){
	load_masonry_item('#main_container', '.main_item');
});
</script>

<?php if (Config::get('album.display_setting.detail.display_slide_image')): ?>
<?php echo Asset::js('bootstrap-carousel.js');?>
<script type="text/javascript">
$('.carousel').carousel({
	interval: false
})
</script>
<?php endif; ?>
