<div class="well">
<?php echo form_open(false, false, array('class' => '')); ?>
	<?php echo render('filetmp/upload', array('files' => $files)); ?>
	<?php echo btn('form.upload', null, null, true, null, 'primary', array('id' => 'form_button', 'class' => 'submit_btn'), null, 'button', 'form_button', false); ?>
<?php echo form_close(); ?>
</div>
