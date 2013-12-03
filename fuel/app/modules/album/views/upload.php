<?php echo form_open(false, false, array('class' => '')); ?>
	<?php echo render('filetmp/upload', array('files' => $files)); ?>
	<?php echo Form::button('form_button', \Config::get('term.album_image').'を投稿する', array(
		'class' => 'btn btn-default btn-primary',
		'id'    => 'form_button',
		'type'  => 'button',
	)); ?>
<?php echo form_close(); ?>
