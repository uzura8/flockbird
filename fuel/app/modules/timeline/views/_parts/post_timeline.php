<?php echo Asset::js('site/modules/timeline/common/post_timeline.js');?>
<?php echo render('filetmp/_parts/upload_footer', array('thumbnail_size' => 'S')); ?>
<?php if (FBD_FACEBOOK_APP_ID && conf('service.facebook.shareDialog.myhome.autoPopupAfterCreated')): ?>
<?php echo render('_parts/facebook/load_share_dialog_js'); ?>
<?php endif; ?>

