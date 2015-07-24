<?php if (FBD_FACEBOOK_APP_ID && conf('service.facebook.shareDialog.note.isEnabled') && conf('service.facebook.shareDialog.note.autoPopupAfterCreated')): ?>
<?php echo render('_parts/facebook/load_js_auto_popup'); ?>
<?php endif; ?>
<?php echo render('_parts/comment/handlebars_template'); ?>

