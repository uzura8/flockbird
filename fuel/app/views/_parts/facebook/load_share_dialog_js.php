<?php if (FBD_FACEBOOK_APP_ID): ?>
<script src="<?php echo conf('service.facebook.shareDialog.jsUrl'); ?>"></script>
<script>
if (typeof FB !== 'undefined') FB.init({appId: get_config('app_id_facebook'), status: true, cookie: true});
</script>

<?php 	if (!empty($auto_popup)): ?>
<?php
if (empty($param_name)) $param_name = 'created';
if (empty($facebook_option_selectoer)) $facebook_option_selectoer = '.js-facebook_feed';
?>
<script>
$(function(){
	var isCreated = url('?<?php echo $param_name; ?>'),
		fbFeedOptions = $('<?php echo $facebook_option_selectoer; ?>').data('options');
	if (isCreated && fbFeedOptions) {
		popupFacebookShareDialog(fbFeedOptions);
	}
})
</script>
<?php 	endif; ?>
<?php endif; ?>

