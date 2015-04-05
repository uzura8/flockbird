<?php if (Auth::check()): ?>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
<?php	$i = 1; ?>
<?php foreach (Config::get('navigation.site.secure_global_head') as $name => $value): ?>
<?php		if (is_array($value)): ?>
					<li class="dropdown" id="menu<?php echo $i; ?>">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#menu<?php echo $i; ?>"><?php echo $name; ?> <b class="caret"></b></a>
						<ul class="dropdown-menu">
<?php 		foreach ($value as $item_name => $item_path): ?>
							<li<?php if (check_current_uri($item_path)): ?> class="active"<?php endif; ?>><?php echo navigation_link($item_name, $item_path); ?></li>
<?php 		endforeach; ?>
						</ul>
						</li>
<?php 	else: ?>
					<li<?php if (check_current_uri($value)): ?> class="active"<?php endif; ?>><?php echo navigation_link($name, $value); ?></li>
<?php 	endif; ?>
<?php		$i++; ?>
<?php endforeach; ?>
				</ul>
			</div><!--/.nav-collapse -->
<?php else: ?>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
<?php	$i = 1; ?>
<?php foreach (Config::get('navigation.site.insecure_global_head') as $name => $value): ?>
<?php		if (is_array($value)): ?>
					<li class="dropdown" id="menu<?php echo $i; ?>">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#menu<?php echo $i; ?>"><?php echo $name; ?> <span class="caret"></span></a>
						<ul class="dropdown-menu">
<?php 		foreach ($value as $item_name => $item_path): ?>
							<li<?php if (check_current_uri($item_path)): ?> class="active"<?php endif; ?>><?php echo navigation_link($item_name, $item_path); ?></li>
<?php 		endforeach; ?>
						</ul>
						</li>
<?php 	else: ?>
					<li<?php if (check_current_uri($value)): ?> class="active"<?php endif; ?>><?php echo navigation_link($name, $value); ?></li>
<?php 	endif; ?>
<?php		$i++; ?>
<?php endforeach; ?>
				</ul>
			</div><!--/.nav-collapse -->
<?php endif; ?>
