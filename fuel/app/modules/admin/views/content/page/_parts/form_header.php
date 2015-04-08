<?php if (\Content\Site_Util::check_editor_enabled('html_editor')): ?>
<?php 	echo Asset::css('summernote.css');?>
<?php endif; ?>
<?php if (\Content\Site_Util::check_editor_enabled('markdown')): ?>
<?php 	echo Asset::css('bootstrap-markdown.min.css'); ?>
<?php endif; ?>

