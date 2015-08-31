<?php echo render('_parts/load_masonry'); ?>
<?php if (Config::get('album.display_setting.detail.display_slide_image')): ?>
<script>
$('.carousel').carousel({
	interval: false
})
</script>
<?php endif; ?>

<?php if (FBD_FACEBOOK_APP_ID && conf('service.facebook.shareDialog.album.isEnabled') && conf('service.facebook.shareDialog.album.autoPopupAfterCreated')): ?>
<?php echo render('_parts/facebook/load_js_auto_popup'); ?>
<?php endif; ?>
