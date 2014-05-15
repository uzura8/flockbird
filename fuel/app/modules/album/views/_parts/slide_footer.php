<script>
function get_upload_uri_base_path() {return '<?php echo Site_Upload::get_uploaded_file_uri_path('', '', '600x600'); ?>';}
function get_comment_limit_default() {return <?php echo conf('view_params_default.list.comment.limit'); ?>;}
</script>
