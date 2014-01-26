<div class="navbar navbar-fixed-top navbar-inverse" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo Uri::create('/'); ?>"><?php echo PRJ_SITE_NAME; ?></a>
<?php if (Auth::check()): ?>
			<div class="pull-right navbar-btn btn-group">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					<?php echo img($u->get_image(), '20x20xc', '', false, '', true); ?><span class="hidden-xs-inline"> <?php echo site_get_screen_name($u); ?></span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu">
<?php foreach (Config::get('navigation.site.secure_user_dropdown') as $name => $path): ?>
					<li<?php if (Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo Html::anchor($path, $name); ?></li>
<?php endforeach; ?>
				</ul>
			</div>
<?php 	else: ?>
			<button href="#" type="button" id="insecure_user_menu" class="btn btn-default pull-right navbar-btn" data-content="<div id='insecure_user_popover'></div>" data-placement="bottom">
				<i class="glyphicon glyphicon-user"></i><span class="hidden-xs-inline"> <?php echo site_get_screen_name($u); ?></span>
				<span class="caret"></span>
			</button>
<?php 	endif; ?>
		</div>
		<div class="collapse navbar-collapse">
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
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
<?php	$i = 1; ?>
<?php foreach (Config::get('navigation.site.insecure_global_head') as $name => $value): ?>
<?php		if (is_array($value)): ?>
					<li class="dropdown" id="menu<?php echo $i; ?>">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#menu<?php echo $i; ?>"><?php echo $name; ?> <span class="caret"></span></a>
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
		</div><!-- /.nav-collapse -->
	</div><!-- /.container -->
</div><!-- /.navbar -->
