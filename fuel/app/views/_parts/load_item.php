<?php echo Asset::js('jquery.infinitescroll.js', null, null, false, false, true);?>
<script>
$(function(){
	loadItem('#article_list', '.article');
});
</script>
