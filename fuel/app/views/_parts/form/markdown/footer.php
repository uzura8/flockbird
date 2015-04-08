<?php
Asset::js(array(
	'bootstrap-markdown/js/markdown.js',
	'bootstrap-markdown/js/to-markdown.js',
	'bootstrap-markdown/js/bootstrap-markdown.js',
), null, 'js_markdown', false, true);
echo Asset::render('js_markdown', false, 'js');
?>
<script>
$('<?php echo $textarea_selector; ?>').markdown({autofocus:false,savable:false})
</script>
