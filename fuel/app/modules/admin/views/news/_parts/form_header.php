<?php echo render('_parts/datetimepicker_header'); ?>
<?php if (Config::get('news.image.isEnabled') || Config::get('news.file.isEnabled')): ?>
<?php echo render('filetmp/_parts/upload_header'); ?>
<?php endif; ?>
<?php if (Config::get('news.form.isEnabledWysiwygEditor')): ?>
<?php echo render('_parts/form/summernote/header'); ?>
<?php endif; ?>
