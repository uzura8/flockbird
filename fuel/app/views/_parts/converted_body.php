<?php
if ($callbacks)
{
	foreach ($callbacks as $callback)
	{
		if (is_callable($callback)) $body = call_user_func($callback, $body);
	}
}
?>
<div>
	<?php echo $body; ?>
</div>
<?php if ($is_truncated && $read_more_uri): ?>
<div class="bodyMore"><?php echo Html::anchor($read_more_uri, 'もっとみる'); ?></div>
<?php endif; ?>
