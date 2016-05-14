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
), null, 'css_common_vendor', false, true);
echo Asset::render('css_common_vendor', false, 'css');

echo Asset::css('font-awesome.css', null, null, false, false, true);

Asset::css(array(
	IS_ADMIN ? 'bootstrap.custom.admin.css' : 'bootstrap.custom.css',
	'base.css',
	IS_SP ? 'base_mobile.css' : 'base_pc.css',
), null, 'css_common', false, true, false, true);
echo Asset::render('css_common', false, 'css');
?>

