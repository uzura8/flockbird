<?php echo form_open(false, false, array('class' => '')); ?>
	<?php echo render('filetmp/upload', array('files' => $files)); ?>
	<?php echo btn('form.upload', null, null, true, null, 'primary', array('id' => 'form_button'), null, 'button', 'form_button'); ?>
<?php echo form_close(); ?>
