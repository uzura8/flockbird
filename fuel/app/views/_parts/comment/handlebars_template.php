<?php $view_name = '_parts/comment/handlebars_template'; ?>
<?php if (!in_array($view_name, $renderd_views)): ?>
<script type="text/x-handlebars-template" id="comment-template">
<?php echo render('_parts/handlebars_template/comment'); ?>
</script>
<?php endif; ?>
<?php
$renderd_views[] = $view_name;
View::set_global('renderd_views', $renderd_views);
?>

