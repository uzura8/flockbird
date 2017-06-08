<?php
if (! isset($dl_attr)) $dl_attr = array();
if (! empty($is_horizontal))
{
	if (! isset($dl_attr['class'])) $dl_attr['class'] = '';
	if ($dl_attr['class']) $dl_attr['class'] .= ' ';
	$dl_attr['class'] .= 'dl-horizontal';
}
?>
<?php if ($list): ?>
<dl<?php if (! empty($dl_attr)): ?> <?php echo Util_Array::conv_array2attr_string($dl_attr); ?><?php endif; ?>>
<?php foreach ($list as $key => $value): ?>
  <dt><?php echo $key; ?></dt>
		<dd><?php echo $value; ?></dd>
<?php endforeach; ?>
</dl>
<?php endif; ?>
