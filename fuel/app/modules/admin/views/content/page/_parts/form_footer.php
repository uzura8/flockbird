<?php if (\Content\Site_Util::check_editor_enabled('html_editor')): ?>
<?php 	echo render('_parts/form/summernote/footer'); ?>
<?php 	echo render('_parts/form/summernote/moderator_setting'); ?>
<?php endif; ?>
<?php if (\Content\Site_Util::check_editor_enabled('markdown')): ?>
<?php 	echo render('_parts/form/markdown/footer', array('textarea_selector' => '#form_body')); ?>
<?php endif; ?>
<?php echo Asset::js('site/modules/admin/common/editor_form.js');?>
<?php echo Asset::js('site/modules/admin/content/common/form.js');?>
