<?php echo render('_parts/load_masonry'); ?>
<?php if (Config::get('album.display_setting.detail.display_slide_image')): ?>
<script>
$('.carousel').carousel({
	interval: false
})
</script>
<?php endif; ?>

<?php if (FBD_FACEBOOK_APP_ID && !empty($is_mypage) && conf('service.facebook.shareDialog.album.isEnabled')): ?>
<?php echo render('_parts/facebook/load_share_dialog_js', array('auto_popup' => conf('service.facebook.shareDialog.album.autoPopupAfterCreated'))); ?>
<?php endif; ?>

