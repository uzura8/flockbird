<?php echo Asset::js('jquery.masonry.js', null, null, false, false, true);?>
<?php echo Asset::js('jquery.infinitescroll.js', null, null, false, false, true);?>
<script>
$(function(){
	load_masonry_item('#image_list', '.image_item'<?php if (!empty($is_not_load_more)): ?>, false<?php endif; ?>);
});
</script>
