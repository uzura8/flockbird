<?php if (isset($title['value']) && strlen($title['value'])): ?>
<?php $label = strim($title['value'], $title['truncate_count']); ?>
<h4><?php if (!empty($read_more_uri)): ?><?php echo Html::anchor($read_more_uri, $label); ?><?php else: ?><?php echo $label; ?><?php endif; ?></h4>
<?php endif; ?>
<?php if (isset($body['value']) && strlen($body['value'])): ?>
<?php
$body_value = $body['value'];
if (empty($read_more_uri)) $read_more_uri = '';
if (isset($body['truncate_type']) && $body['truncate_type'] == 'line')
{
	if ($body['truncate_type'] == 'line')
	{
		$body_value = truncate_lines($body['value'], $body['truncate_count'], $read_more_uri);
	}
	else
	{
		$body_value = strim($body['value'], $body['truncate_count']);
	}
}
?>
<p><?php echo $body_value; ?></p>
<?php endif; ?>
