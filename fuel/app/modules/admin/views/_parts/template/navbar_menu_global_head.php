<?php if (Auth::check()): ?>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
<?php	$i = 1; ?>
<?php foreach (Config::get('navigation.admin.secure_global_head') as $name => $value): ?>
<?php		if (is_array($value)): ?>
					<li class="dropdown" id="menu<?php echo $i; ?>">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#menu<?php echo $i; ?>"><?php echo $name; ?> <b class="caret"></b></a>
						<ul class="dropdown-menu">
<?php 		foreach ($value as $item_name => $item_path): ?>
							<li><?php echo anchor($item_path, $item_name, true); ?></li>
<?php 		endforeach; ?>
						</ul>
						</li>
<?php 	else: ?>
					<li<?php if (isset($path) && Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo anchor($value, $name, true); ?></li>
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
					<li class="dropdown" id="menu<?php echo $i; ?>">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#menu<?php echo $i; ?>"><?php echo $name; ?> <b class="caret"></b></a>
						<ul class="dropdown-menu">
<?php 		foreach ($value as $item_name => $item_path): ?>
							<li><?php echo anchor($item_path, $item_name, true); ?></li>
<?php 		endforeach; ?>
						</ul>
						</li>
<?php 	else: ?>
					<li<?php if (isset($path) && Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo anchor($value, $name, true); ?></li>
<?php 	endif; ?>
<?php		$i++; ?>
<?php endforeach; ?>
				</ul>
			</div><!--/.nav-collapse -->
<?php endif; ?>
