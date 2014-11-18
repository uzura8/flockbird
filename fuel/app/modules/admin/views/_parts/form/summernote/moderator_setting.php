<script type="text/javascript">
$(document).ready(function() {
	$('#form_body').summernote({
<?php if (\Auth::member(50)): ?>
		toolbar: [
			['font', ['bold', 'italic', 'underline', 'clear']],
			['insert', ['link', 'picture']],
			['view', ['fullscreen']]
		],
<?php endif; ?>
		lang: 'ja-JP',
		height: 300,
		minHeight: 150
	});
});
</script>
