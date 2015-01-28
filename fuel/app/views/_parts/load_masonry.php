<?php echo Asset::js('jquery.masonry.min.js');?>
<?php echo Asset::js('jquery.infinitescroll.min.js');?>
<script>
$(function(){
	load_masonry_item('#image_list', '.image_item'<?php if (!empty($is_not_load_more)): ?>, false<?php endif; ?>);
});
</script>
