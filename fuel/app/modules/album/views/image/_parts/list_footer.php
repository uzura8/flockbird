<?php echo render('_parts/comment/handlebars_template'); ?>
<?php
$data = array();
if(isset($is_not_load_more)) $data['is_not_load_more'] = $is_not_load_more;
?>
<?php echo render('_parts/load_masonry', $data); ?>
