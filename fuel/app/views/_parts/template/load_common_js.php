<?php echo Asset::js('handlebars-v4.0.5.js');?>
<?php
$jquery_version = conf('library.jqueryVersion.latest');
if (conf('legacyBrowserSupport.isEnabled') && \MyAgent\Agent::check_legacy_ie(conf('legacyBrowserSupport.legacyIECriteriaVersion')))
{
	$jquery_version = conf('library.jqueryVersion.legacy');
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/<?php echo $jquery_version ; ?>/jquery.min.js"></script>
<script>
if (typeof jQuery == 'undefined') document.write('<script src="<?php echo Uri::base_path('assets/js/jquery-'.$jquery_version.'.min.js'); ?>"><\/script>')
</script>

<?php if (conf('library.vue.isEnabled')): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/<?php echo conf('library.vue.version') ; ?>/vue.min.js"></script>
<script>
if (typeof Vue == 'undefined') document.write('<script src="<?php echo Uri::base_path('assets/js/vue.min.js'); ?>"><\/script>')
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.0.4/theme-chalk/index.css">
<?php 	if (conf('library.vue.element.isEnabled')): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/<?php echo conf('library.vue.element.version') ; ?>/index.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/<?php echo conf('library.vue.element.version') ; ?>/locale/ja.js"></script>
<script>
ELEMENT.locale(ELEMENT.lang.ja)
<?php 	endif; ?>
</script>
<?php endif; ?>

<?php if (conf('library.axios.isEnabled')): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/<?php echo conf('library.axios.version') ; ?>/axios.min.js"></script>
<script>
if (typeof axios == 'undefined') document.write('<script src="<?php echo Uri::base_path('assets/js/axios.min.js'); ?>"><\/script>')
</script>
<?php endif; ?>

<?php
$js_files = array(
	'bootstrap.js',
	'apprise-1.5.full.js',
	'jquery.autogrow-textarea.js',
	'jquery.jgrowl.js',
	'moment.js',
	'livestamp.js',
	'js-url/js-url.js',
	'util.js',
);
$moment_local_file = Site_Util::get_moment_js_locale_file();
Arr::insert_after_value($js_files, $moment_local_file, 'moment.js');
Asset::js($js_files, null, 'js_common', false, true);
echo Asset::render('js_common', false, 'js');
?>

<script>var gapi;</script>
<?php if (is_enabled_share('google')): ?>
<script src="https://apis.google.com/js/platform.js" async defer>
	{lang: "ja"}
</script>
<?php endif; ?>

