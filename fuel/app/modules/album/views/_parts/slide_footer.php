<script>
function get_upload_uri_base_path() {return '<?php echo PRJ_URI_PATH.Site_Upload::get_uploaded_file_uri_path('', '', '600x600'); ?>';}
function get_comment_limit_default() {return <?php echo conf('view_params_default.list.comment.limit'); ?>;}
</script>
<?php echo render('_parts/handlebars_template/post_comment', array('size' => empty($size) ? 'S' : strtoupper($size))); ?>
