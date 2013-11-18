<ul class="breadcrumb">
<?php foreach ($list as $path => $name): ?>
<?php if ($path): ?>
	<li><?php echo Html::anchor($path, strim($name, 30)); ?></li>
<?php else: ?>
	<li class="active"><?php echo strim($name, 30); ?></li>
<?php endif; ?>
<?php endforeach; ?>
</ul>
