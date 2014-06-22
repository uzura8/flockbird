<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
if (typeof jQuery == 'undefined') document.write('<script src="<?php echo Uri::base_path('assets/js/jquery-2.1.1.min.js'); ?>"><\/script>')
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
<?php echo Asset::js('site.js');?>
<script>
function get_uid() {return <?php echo Auth::check() ? $u->id : 0; ?>;}
function check_is_admin() {return <?php echo IS_ADMIN ? 'true' : 'false'; ?>;}
</script>
