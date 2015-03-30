<?php
$jquery_version = conf('jqueryVersion.latest');
if (conf('legacyBrowserSupport.isEnabled') && \MyAgent\Agent::check_legacy_ie(conf('legacyBrowserSupport.legacyIECriteriaVersion')))
{
	$jquery_version = conf('jqueryVersion.legacy');
}
?>
<?php echo Asset::js('handlebars-v1.3.0.js');?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $jquery_version ; ?>/jquery.min.js"></script>
<script>
if (typeof jQuery == 'undefined') document.write('<script src="<?php echo Uri::base_path('assets/js/jquery-'.$jquery_version.'.min.js'); ?>"><\/script>')
</script>
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

