<?php echo render('_parts/datetimepicker_footer', array('attr' => '#published_at_time')); ?>
<?php if (Config::get('news.image.isEnabled') || Config::get('news.file.isEnabled')): ?>
<?php echo render('filetmp/_parts/upload_footer'); ?>
<?php endif; ?>
<?php echo Asset::js('site/modules/admin/news/common/form.js');?>
<?php if (Config::get('news.form.isEnabledWysiwygEditor')): ?>
<?php echo Asset::js('summernote.min.js');?>
<?php echo Asset::js('lang/summernote-ja-JP.js');?>
<script type="text/javascript">
$(document).ready(function() {
	$('#form_body').summernote({
<?php if (\Auth::member(50)): ?>
		toolbar: [
			['font', ['bold', 'italic', 'underline', 'clear']],
			['insert', ['link']],
			['view', ['fullscreen']]
		],
<?php endif; ?>
		lang: 'ja-JP',
		height: 300,
		minHeight: 150
	});
});
</script>
<?php endif; ?>
