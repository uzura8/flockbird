<?php
if (isset($label) && empty($label['name'])) unset($label);
if (isset($label) && empty($label['attr'])) $label['attr'] = 'label-default';
?>
.<h1<?php if (!empty($label)): ?> class="has_label"<?php endif; ?>><?php echo $name; ?><?php if (!empty($label)): ?>	<?php echo render('_parts/label', array('name' => $label['name'], 'attr' => $label['attr'])); ?><?php endif; ?></h1>
