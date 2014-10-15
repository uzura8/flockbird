<script type="text/x-handlebars-template" id="notices-template">
<?php echo render('_parts/handlebars_template/list', array('is_detail' => !empty($is_detail))); ?>
</script>
<?php echo Asset::js('site/modules/notice/common/util.js');?>
