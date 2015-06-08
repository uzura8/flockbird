<?php echo render('_parts/datetimepicker_footer', array('attr' => '#published_at_time')); ?>
<?php if (Config::get('news.image.isEnabled') || Config::get('news.file.isEnabled')): ?>
<?php 	echo render('filetmp/_parts/upload_footer'); ?>
<?php endif; ?>
<?php if (\News\Site_Util::check_editor_enabled('html_editor')): ?>
<?php 	echo render('_parts/form/summernote/footer'); ?>
<?php 	echo render('_parts/form/summernote/moderator_setting'); ?>
<?php endif; ?>
<?php if (\News\Site_Util::check_editor_enabled('markdown')): ?>
<?php 	echo render('_parts/form/markdown/footer', array('textarea_selector' => '#form_body')); ?>
<?php endif; ?>
<?php if (Config::get('news.form.tags.isEnabled')): ?>
<?php echo Asset::js('select2/select2.js');?>
<script>
$("#form_tags").select2({
  tags: true,
  tokenSeparators: [',', ' ']
})
</script>
<?php endif; ?>
<script>
var isInsertImage = true;
</script>
<?php echo Asset::js('site/modules/admin/common/editor_form.js');?>
<?php echo Asset::js('site/modules/admin/news/common/form.js');?>

