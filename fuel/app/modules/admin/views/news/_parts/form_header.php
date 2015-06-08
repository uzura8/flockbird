<?php echo render('_parts/datetimepicker_header'); ?>
<?php if (Config::get('news.image.isEnabled') || Config::get('news.file.isEnabled')): ?>
<?php 	echo render('filetmp/_parts/upload_header'); ?>
<?php endif; ?>
<?php if (\News\Site_Util::check_editor_enabled('html_editor')): ?>
<?php 	echo Asset::css('summernote.css');?>
<?php endif; ?>
<?php if (\News\Site_Util::check_editor_enabled('markdown')): ?>
<?php 	echo Asset::css('bootstrap-markdown.min.css'); ?>
<?php endif; ?>
<?php if (Config::get('news.form.tags.isEnabled')): ?>
<?php echo Asset::css('select2.css', null, null, false, false, true); ?>
<?php endif; ?>

