<?php
Asset::js(array(
	'summernote.min.js',
	'lang/summernote-ja-JP.js',
), null, 'js_summernote', false, true);
echo Asset::render('js_summernote', false, 'js');
?>

