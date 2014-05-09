<ul class="nav nav-pills nav-stacked well">
	<li class="nav-header disabled"><a><?php echo $header; ?></a></li>
<?php foreach ($list as $name => $path): ?>
	<li<?php if (Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo anchor($path, $name, IS_ADMIN); ?></li>
<?php endforeach; ?>
</ul>
