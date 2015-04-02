<?php if (Auth::check()): ?>
		<div class="navbar-btn pull-right">
<?php if (!IS_ADMIN): ?>
<?php echo render('_parts/template/navbar_user_button_notice'); ?>
<?php endif; ?>
			<div class="btn-group pull-right">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					<?php echo img($u->get_image(), 'SS', '', false, '', true); ?><span class="hidden-xs-inline"> <?php echo site_get_screen_name($u); ?></span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu">
<?php foreach (Config::get('navigation.site.secure_user_dropdown') as $name => $path): ?>
					<li<?php if (check_cuurent_uri($path)): ?> class="active"<?php endif; ?>><?php echo anchor_icon($path, $name); ?></li>
<?php endforeach; ?>
				</ul>
			</div>
		</div>
<?php 	else: ?>
		<button href="#" type="button" id="insecure_user_menu" class="btn btn-default pull-right navbar-btn" data-content="<div id='insecure_user_popover'></div>" data-placement="bottom">
			<i class="glyphicon glyphicon-user"></i><span class="hidden-xs-inline"> <?php echo site_get_screen_name($u); ?></span>
			<span class="caret"></span>
		</button>
<?php 	endif; ?>
