<?php
if ($callbacks)
{
	if (!is_array($callbacks)) $callbacks = (array)$callbacks;
	foreach ($callbacks as $callback)
	{
		if (is_callable($callback)) $body = call_user_func($callback, $body);
	}
}

$is_truncated = false;
if (!$options['is_detail'])
{
	if ($options['line'])
	{
		list($body, $is_truncated4line) = Util_string::truncate4line($body, $options['line'], '', $options['is_rtrim'], $options['encoding']);
	}
	if ($options['width'])
	{
		list($body, $is_truncated4count) = Util_string::truncate($body, $options['width'], '', true);
	}
	$is_truncated = $is_truncated4line || $is_truncated4count;
}

if ($is_truncated && $options['trimmarker'])
{
	if (!Str::ends_with($body, "\n")) $body .= ' ';
	$body .= $options['trimmarker'];
}
?>
<div><?php echo $body; ?></div>
<?php if ($is_truncated && $options['read_more_uri']): ?>
<div class="bodyMore"><?php echo Html::anchor($options['read_more_uri'], 'もっとみる'); ?></div>
<?php endif; ?>
