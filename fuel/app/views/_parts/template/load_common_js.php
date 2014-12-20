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
<?php echo Asset::js('bootstrap.min.js');?>
<?php echo Asset::js('apprise-1.5.min.js');?>
<?php echo Asset::js('jquery.autogrow-textarea.js');?>
<?php echo Asset::js('jquery.jgrowl.min.js');?>
<?php echo Asset::js('moment.min.js');?>
<?php echo Asset::js('moment.lang_ja.js');?>
<?php echo Asset::js('livestamp.min.js');?>
<?php echo Asset::js('js-url/js-url.min.js');?>
<?php echo Asset::js('util.js');?>
