<?php
if (empty($input_name)) $input_name = 'name';

$btn_attr_default = array('id' => 'btn_create');
if ($input_name != 'name') $btn_attr_default['data-input_name'] = $input_name;

if (empty($btn_attr)) $btn_attr = array();
$btn_attr = array_merge($btn_attr_default, $btn_attr);
?>
<div class="well">
<?php echo Form::open(array('class' => 'form-inline')); ?>
<div class="form-group">
	<?php echo Form::input($input_name, '', array('id' => 'input_'.$input_name, 'class' => 'form-control input-xlarge')); ?>
</div>
<div class="form-group">
	<?php echo btn('do_add', null, null, true, null, null, $btn_attr, 'button'); ?>
</div>
<?php echo Form::close(); ?>
</div><!-- well -->
