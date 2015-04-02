<?php echo render('_parts/comment/handlebars_template'); ?>
<script>
function get_upload_uri_base_path() {return '<?php echo FBD_URI_PATH.Site_Upload::get_uploaded_file_path('', '600x600', 'img', false, true); ?>';}
</script>
<?php if (Auth::check()): ?>
<?php echo render('_parts/handlebars_template/post_comment', array('size' => empty($size) ? 'S' : strtoupper($size))); ?>
<?php endif; ?>
