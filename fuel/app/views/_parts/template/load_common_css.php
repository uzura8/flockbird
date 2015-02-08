<?php
$theme_name = conf('cssTheme');
$bootstrap_css_file = ($theme_name && $theme_name != 'default') ? sprintf('bootstrap.min.%s.css', $theme_name) : 'bootstrap.min.css';
?>
<?php echo Asset::css($bootstrap_css_file);?>
<?php echo Asset::css('bootstrap.custom.css');?>
<?php echo Asset::css('apprise.min.css');?>
<?php echo Asset::css('jquery.jgrowl.min.css');?>
<?php echo asset::css('font-awesome.min.css');?>
<?php echo Asset::css('base.css');?>
<?php if (conf('legacyBrowserSupport')): ?>
<!--[if lt IE 9]>
	<?php echo Asset::js('html5shiv.js');?>
	<?php echo Asset::js('respond.min.js');?>
<![endif]-->
<?php endif; ?>
