<?php
$bootstrap_css_file = 'bootstrap.min.css';
$bootstrap_css_theme_file = '';
$theme_name = conf('bootstrapCssTheme.name');
if (!IS_ADMIN && $theme_name && $theme_name != 'default')
{
	$bootstrap_css_file = sprintf('bootstrap.%s.min.css', $theme_name);
	if (conf('bootstrapCssTheme.withThemeCss')) $bootstrap_css_theme_file = sprintf('bootstrap-theme.%s.min.css', $theme_name);
}
?>
<?php echo Asset::css($bootstrap_css_file);?>
<?php if ($bootstrap_css_theme_file) echo Asset::css($bootstrap_css_theme_file);?>
<?php echo Asset::css('bootstrap-custom.flockbird.css');?>
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
