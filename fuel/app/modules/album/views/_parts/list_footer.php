<?php echo Asset::js('jquery.masonry.min.js');?>
<script type="text/javascript">
$(function(){
	load_masonry_item(
		'#main_container',
		'.main_item',
		'<?php echo \Config::get('album.term.album'); ?>がありません。',
		'<?php echo \Uri::create('assets/img/site/loading_l.gif'); ?>'
	);
});
</script>
