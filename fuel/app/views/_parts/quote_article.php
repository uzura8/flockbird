<?php if (isset($title['value']) && strlen($title['value'])): ?>
<?php $label = strim($title['value'], $title['truncate_count']); ?>
<h4><?php if (!empty($read_more_uri)): ?><?php echo Html::anchor($read_more_uri, $label); ?><?php else: ?><?php echo $label; ?><?php endif; ?></h4>
<?php endif; ?>
<?php if (isset($body['value']) && strlen($body['value'])): ?>
<?php
$body_value = $body['value'];
if (empty($read_more_uri)) $read_more_uri = '';
if (!empty($body['truncate_count']))
{
	$truncate_options = array('width' => $body['truncate_count']);
	if ($body['truncate_type'] == 'line')
	{
		$truncate_options = array(
			'line' => $body['truncate_count'],
			'read_more_uri' => $read_more_uri,
		);
	}
	$body_value = convert_body($body['value'], $truncate_options, array('url2link', 'mention2link', 'nl2br'));
}
?>
<div class="quote">
<?php echo $body_value; ?>
</div>
<?php endif; ?>
