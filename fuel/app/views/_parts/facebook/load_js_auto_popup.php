<?php if (empty($param_name)) $param_name = 'created'; ?>
<script>
$(function(){
	 var isCreated = url('?<?php echo $param_name; ?>');
	if (isCreated && typeof FB !== 'undefined') {
		apprise('Facebook に投稿しますか？', {'confirm':true}, function(r) {
			if (r == true) postFacebookFeed();
		}); 
	}
});
</script>

