<?php if (FBD_FACEBOOK_APP_ID && conf('service.facebook.shareDialog.detail', 'note')): ?>
<?php echo render('_parts/facebook/load_js'); ?>
<?php endif; ?>
<?php echo render('_parts/comment/handlebars_template'); ?>

