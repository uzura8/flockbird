<?php echo Asset::css('site.css');?>
<?php
$module_assets_css = conf('assets.css.modules');
foreach ($module_assets_css as $module => $files)
{
	if ($module == 'admin') continue;
	foreach ($files as $file) echo Asset::css(sprintf('modules/%s/%s', $module, $file));
}
?>
