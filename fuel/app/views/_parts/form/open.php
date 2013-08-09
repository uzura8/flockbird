<?php if ($wiht_outside_box): ?>
<div class="well">
<?php endif; ?>
	<?php echo render('_parts/form_description', array('exists_required_fields' => $exists_required_fields)); ?>
	<?php echo Form::open($atter, $hidden); ?>
<?php if (strlen($title)): ?>
		<h4><?php echo $title; ?></h4>
<?php endif; ?>
