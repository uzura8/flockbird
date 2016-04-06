<?php echo Asset::js('site/modules/message/common/load_message.js');?>
<?php if (conf('uploadImages.isEnabled', 'message')): ?>
<?php echo render('filetmp/_parts/upload_footer', array('thumbnail_size' => 'S')); ?>
<?php endif; ?>

