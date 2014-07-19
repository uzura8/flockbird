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
<script>
$(function(){
	$(window).on('beforeunload', function() {
		if (checkInput()) return '投稿が完了していません。このまま移動しますか？';
	});
	$("button[type=submit]").click(function() {
		$(window).off('beforeunload');
	});
});
function checkInput() {
	if ($('#form_title').val().length > 0) return true;
	if ($('.note-editable').size() > 0 && $('.note-editable').html().replace(/^<br>\s*/, '')) return true;
	if ($('.image_tmp').size() > 0 && $('.image_tmp').length) return true;
	if ($('.file_tmp').size() > 0 && $('.file_tmp').length) return true;
	if ($('.link_uri').size() > 0 && $('.link_uri').length) return true;
	if ($('.link_label').size() > 0 && $('.link_label').length) return true;
	if ($('#form_published_at_time').val()) return true;
	return false;
}
</script>



