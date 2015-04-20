<?php if (Auth::check()): ?>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
<?php	$i = 1; ?>
<?php foreach (Config::get('navigation.admin.secure_global_head') as $name => $value): ?>
<?php 	if (is_array($value)): ?>
<?php 		if (false == \Admin\Site_Util::check_exists_accessible_uri($value)) continue; ?>
					<li class="dropdown" id="menu<?php echo $i; ?>">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#menu<?php echo $i; ?>"><?php echo $name; ?> <b class="caret"></b></a>
						<ul class="dropdown-menu">
<?php 		foreach ($value as $item_name => $item_path): ?>
							<li<?php if (check_current_uri(isset($item_path['href']) ? $item_path['href'] : $item_path)): ?> class="active"<?php endif; ?>><?php echo navigation_link($item_name, $item_path, true); ?></li>
<?php 		endforeach; ?>
						</ul>
						</li>
<?php 	elseif (\Admin\Site_Util::check_exists_accessible_uri($value)): ?>
					<li<?php if (check_current_uri($item_path)): ?> class="active"<?php endif; ?>><?php echo navigation_link($name, $value, true); ?></li>
<?php 	endif; ?>
<?php		$i++; ?>
<?php endforeach; ?>
				</ul>
			</div><!--/.nav-collapse -->
<?php else: ?>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
<?php	$i = 1; ?>
<?php foreach (Config::get('navigation.admin.insecure_global_head') as $name => $value): ?>
<?php		if (is_array($value)): ?>
<?php 		if (false == \Admin\Site_Util::check_exists_accessible_uri($value)) continue; ?>
					<li class="dropdown" id="menu<?php echo $i; ?>">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#menu<?php echo $i; ?>"><?php echo $name; ?> <b class="caret"></b></a>
						<ul class="dropdown-menu">
<?php 		foreach ($value as $item_name => $item_path): ?>
							<li><?php echo navigation_link($item_name, $item_path, true); ?></li>
<?php 		endforeach; ?>
						</ul>
						</li>
<?php 	elseif (\Admin\Site_Util::check_exists_accessible_uri($value)): ?>
					<li<?php if (check_current_uri($value)): ?> class="active"<?php endif; ?>><?php echo navigation_link($name, $value, true); ?></li>
<?php 	endif; ?>
<?php		$i++; ?>
<?php endforeach; ?>
				</ul>
			</div><!--/.nav-collapse -->
<?php endif; ?>
