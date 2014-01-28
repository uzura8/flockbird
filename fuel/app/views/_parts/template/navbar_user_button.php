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
