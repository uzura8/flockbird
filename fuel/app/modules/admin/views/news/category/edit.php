<?php
$col_sm_size = 12;
$label_size = 3;
?>
<div class="well">
<?php echo form_open(true); ?>
	<?php echo form_input($val, 'name', isset($news) ? $news->name : '', $col_sm_size, $label_size); ?>
	<?php echo form_input($val, 'label', isset($news) ? $news->label : '', $col_sm_size, $label_size); ?>
	<?php echo form_button('form.do_edit', 'submit', 'submit', array('class' => 'btn btn-default btn-primary'), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
