<?php if (conf('legacyBrowserSupport')): ?>
<!--[if lt IE 9]>
	<?php echo Asset::js('html5shiv.js');?>
	<?php echo Asset::js('respond.min.js');?>
<![endif]-->
<?php endif; ?>

<?php
Asset::css(array(
	'apprise.css',
	'jquery.jgrowl.css',
), null, 'css_common', false, true);
echo Asset::render('css_common', false, 'css');
?>
<?php echo Asset::css('font-awesome.css', null, null, false, false, true);?>
<?php echo Asset::css(IS_ADMIN ? 'bootstrap.custom.admin.css' : 'bootstrap.custom.css');?>
<?php echo Asset::css('base.css');?>
<?php if (IS_SP): ?><?php echo Asset::css('base_mobile.css');?><?php else: ?><?php echo Asset::css('base_pc.css');?><?php endif; ?>
