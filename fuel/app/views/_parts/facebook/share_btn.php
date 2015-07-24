<?php
if (empty($caption)) $caption = FBD_SITE_DESCRIPTION;
if (empty($img_size_key)) $img_size_key = 'thumbnail';
?>
<script src="<?php echo conf('service.facebook.shareDialog.jsUrl'); ?>"></script>
<?php
$img_uri_info = '';
if (!empty($images))
{
	$image = Util_Array::get_last($images);
	$img_uri_info = img_uri($image->get_image(), $img_size_key);
}
echo btn('service.facebook.do_share', '#', 'js-facebook_feed', true, 'xs', 'primary',
			array('class' => 'ml10', 'onClick' => 'postFacebookFeed(); return false;'), null, null, null, false);
?>
<div id="fb-root"></div>
<script> 
if (typeof FB !== 'undefined') FB.init({appId: "<?php echo FBD_FACEBOOK_APP_ID; ?>", status: true, cookie: true});
function postFacebookFeed() {
	var obj = {
		method: 'feed',
		link: '<?php echo Uri::create($link_uri); ?>',
		name: '<?php echo strim($name, conf('service.facebook.shareDialog.name.trimWidth')); ?>',
<?php 	if ($img_uri_info): ?>
		picture: '<?php echo Uri::create($img_uri_info); ?>',
<?php 	endif; ?>
		caption: '<?php echo $caption; ?>',
		description: '<?php echo strim($description, conf('service.facebook.shareDialog.caption.trimWidth'), null, false, true); ?>'
	};
	function callback(response) {
		if (response && response.post_id) {
			showMessage('<?php echo sprintf('%sに%sしました。', term('service.facebook.view'), term('form.post')); ?>');
		}
	}
	if (typeof FB !== 'undefined') FB.ui(obj, callback);
}
</script>

