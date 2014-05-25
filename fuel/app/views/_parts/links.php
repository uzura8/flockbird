<?php if ($list): ?>
<?php 	if (!empty($title)): ?>
<h4><?php echo $title; ?></h4>
<?php 	endif; ?>
<ul>
<?php 	foreach ($list as $link): ?>
	<li><?php echo anchor($link->uri, $link->label ?: $link->uri); ?></li>
<?php 	endforeach; ?>
</ul>
<?php endif; ?>
