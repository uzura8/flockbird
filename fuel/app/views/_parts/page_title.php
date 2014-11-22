<?php
if (isset($label) && empty($label['name'])) unset($label);
if (isset($label) && empty($label['attr'])) $label['attr'] = 'label-default';
?>
<h1 class="h2<?php if (!empty($label)): ?> has_label<?php endif; ?>">
	<?php echo $name; ?>
<?php if (!empty($label)): ?>
 <?php echo label($label['name'],
	!empty($label['type'])  ? $label['type']  : 'default',
	!empty($label['attrs']) ? $label['attrs'] : array()
); ?>
<?php endif; ?>
</h1>
