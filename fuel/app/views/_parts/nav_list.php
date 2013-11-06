<ul class="nav nav-list">
	<li class="nav-header"><?php echo $header; ?></li>
<?php foreach ($list as $name => $path): ?>
	<li<?php if (Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo Html::anchor($path, $name); ?></li>
<?php endforeach; ?>
</ul>
