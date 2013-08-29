<?php echo Asset::js('jquery.masonry.min.js');?>
<?php echo Asset::js('jquery.infinitescroll.min.js');?>
<script type="text/javascript">
$(function(){
	load_masonry_item('#main_container', '.main_item');
});
</script>
