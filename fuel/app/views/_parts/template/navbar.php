<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
			<a class="brand" href="<?php echo Uri::create('/'); ?>"><?php echo PRJ_SITE_NAME; ?></a>
<?php if (Auth::check()): ?>
			<div class="btn-group pull-right">
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
					<?php echo img($u->get_image(), '20x20xc', '', false, '', true); ?> <?php echo site_get_screen_name($u); ?>
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu">
<?php foreach (Config::get('navigation.site.secure_user_dropdown') as $name => $path): ?>
					<li<?php if (Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo Html::anchor($path, $name); ?></li>
<?php endforeach; ?>
				</ul>
			</div>
			<div class="nav-collapse">
				<ul class="nav">
<?php	$i = 1; ?>
<?php foreach (Config::get('navigation.site.secure_global_head') as $name => $value): ?>
<?php		if (is_array($value)): ?>
					<li class="dropdown" id="menu<?php echo $i; ?>">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#menu<?php echo $i; ?>"><?php echo $name; ?> <b class="caret"></b></a>
						<ul class="dropdown-menu">
<?php 		foreach ($value as $item_name => $item_path): ?>
							<li><?php echo Html::anchor($item_path, $item_name); ?></li>
<?php 		endforeach; ?>
						</ul>
						</li>
<?php 	else: ?>
					<li<?php if (Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo Html::anchor($value, $name); ?></li>
<?php 	endif; ?>
<?php		$i++; ?>
<?php endforeach; ?>
				</ul>
			</div><!--/.nav-collapse -->
<?php else: ?>
			<a href="#" id="insecure_user_menu" class="btn pull-right" data-content="<div id='insecure_user_popover'></div>" data-placement="bottom">
				<i class="icon-user"></i> <?php echo site_get_screen_name($u); ?>
			</a>
			<div class="nav-collapse">
				<ul class="nav">
<?php	$i = 1; ?>
<?php foreach (Config::get('navigation.site.insecure_global_head') as $name => $value): ?>
<?php		if (is_array($value)): ?>
					<li class="dropdown" id="menu<?php echo $i; ?>">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#menu<?php echo $i; ?>"><?php echo $name; ?> <b class="caret"></b></a>
						<ul class="dropdown-menu">
<?php 		foreach ($value as $item_name => $item_path): ?>
							<li><?php echo Html::anchor($item_path, $item_name); ?></li>
<?php 		endforeach; ?>
						</ul>
						</li>
<?php 	else: ?>
					<li<?php if (isset($path) && Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo Html::anchor($value, $name); ?></li>
<?php 	endif; ?>
<?php		$i++; ?>
<?php endforeach; ?>
				</ul>
			</div><!--/.nav-collapse -->
<?php endif; ?>
		</div>
	</div>
</div>
