<?php if (FBD_FACEBOOK_APP_ID && !empty($is_mypage) && conf('service.facebook.shareDialog.note.isEnabled')): ?>
<?php echo render('_parts/facebook/load_share_dialog_js', array('auto_popup' => conf('service.facebook.shareDialog.note.autoPopupAfterCreated'))); ?>
<?php endif; ?>
<?php echo render('_parts/comment/handlebars_template'); ?>

