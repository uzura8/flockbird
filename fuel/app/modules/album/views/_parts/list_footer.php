<?php echo Asset::js('jquery.masonry.min.js');?>
<script type="text/javascript">
$(function(){
	load_masonry_item(
		'#main_container',
		'.main_item',
		get_term('album_image') + 'がありません。'
	);
});
</script>
