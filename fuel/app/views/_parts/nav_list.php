<?php if (!empty($list)): ?>
<ul class="nav nav-pills nav-stacked well">
<?php 	if (!empty($header)): ?>
	<li class="nav-header disabled"><a><?php echo $header; ?></a></li>
<?php 	endif; ?>
<?php 	foreach ($list as $name => $path): ?>
<?php 		if (IS_ADMIN && !Site_Util::check_ext_uri($path, IS_ADMIN) && Auth::check() && !\Admin\Site_Util::check_exists_accessible_uri($path)) continue; ?>
	<li<?php if (Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo anchor_icon($path, $name, null, null, false, null, IS_ADMIN); ?></li>
<?php 	endforeach; ?>
</ul>
<?php endif; ?>
