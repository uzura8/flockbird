<?php echo Asset::js('handlebars-v1.3.0.js');?>
<?php
$jquery_version = conf('library.jqueryVersion.latest');
if (conf('legacyBrowserSupport.isEnabled') && \MyAgent\Agent::check_legacy_ie(conf('legacyBrowserSupport.legacyIECriteriaVersion')))
{
	$jquery_version = conf('library.jqueryVersion.legacy');
}
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $jquery_version ; ?>/jquery.min.js"></script>
<script>
if (typeof jQuery == 'undefined') document.write('<script src="<?php echo Uri::base_path('assets/js/jquery-'.$jquery_version.'.min.js'); ?>"><\/script>')
</script>

<?php if (conf('library.angularJs.isEnabled') && $use_angularjs): ?>
<?php
$angularjs_version = conf('library.angularJs.versions.latest');
//if (conf('legacyBrowserSupport.isEnabled') && \MyAgent\Agent::check_legacy_ie(conf('legacyBrowserSupport.legacyIECriteriaVersion')))
//{
//	$jquery_version = conf('library.angularJs.versions.legacy');
//}
?>
<script src="//ajax.googleapis.com/ajax/libs/angularjs/<?php echo $angularjs_version ; ?>/angular.min.js"></script>
<script>
if (typeof angular == 'undefined') document.write('<script src="<?php echo Uri::base_path('assets/js/angularjs/'.$angularjs_version.'/angular.min.js'); ?>"><\/script>')
</script>
<?php endif; ?>

<?php
Asset::js(array(
	'bootstrap.js',
	'apprise-1.5.full.js',
	'jquery.autogrow-textarea.js',
	'jquery.jgrowl.js',
	'moment.js',
	'moment.lang_ja.js',
	'livestamp.js',
	'js-url/js-url.js',
	'util.js',
), null, 'js_common', false, true);
echo Asset::render('js_common', false, 'js');
?>

<script>var gapi;</script>
<?php if (is_enabled_share('google')): ?>
<script src="//apis.google.com/js/platform.js" async defer>
	{lang: "ja"}
</script>
<?php endif; ?>

