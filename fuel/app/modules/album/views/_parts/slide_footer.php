<script type="text/javascript">
function get_upload_uri_base_path() {return '/<?php echo \Site_Upload::get_upload_uri_base_path('img', 'ai', $id); ?>/600x600/';}
function get_comment_limit_default() {return <?php echo \Config::get('album.articles.comment.limit'); ?>;}
</script>
