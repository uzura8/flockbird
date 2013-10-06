<div><?php echo $is_convert_nl2br ? nl2br($body) : $body; ?></div>
<?php if ($is_truncated): ?>
<div class="bodyMore"><?php echo Html::anchor($read_more_uri, 'もっとみる'); ?></div>
<?php endif; ?>
