<?php if (Auth::check()): ?>
		<div class="navbar-btn pull-right">
			<div class="btn-group pull-right">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					<?php echo member_image($u, 'SS', '', false, false); ?><span class="hidden-xs-inline"> <?php echo strim(Auth::get_screen_name(), 25, null, false); ?></span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu">
<?php foreach (Config::get('navigation.site.secure_user_dropdown') as $name => $path): ?>
					<li<?php if (check_current_uri($path)): ?> class="active"<?php endif; ?>><?php echo anchor_icon($path, $name); ?></li>
<?php endforeach; ?>
				</ul>
			</div>
<?php 	if (!IS_ADMIN): ?>
<?php echo render('_parts/template/navbar_user_button_notice'); ?>
<?php 	endif; ?>
		</div>

<?php 	else: ?>

<?php
$button_attrs = array(
	'type' => 'button',
	'id' => 'insecure_user_menu',
	'class' => 'btn btn-default pull-right navbar-btn',
);
?>
<?php 	switch (conf('auth.headerLoginForm.type')): ?>
<?php 		case 'popover': ?>
<?php
$button_attrs['data-content']   = "<div id='insecure_user_popover'></div>";
$button_attrs['data-placement'] = 'bottom';
?>
<?php 		break; ?>
<?php 		case 'modal': ?>
<?php
$button_attrs['class'] .= ' js-modal';
$button_attrs['data-target'] = '#insecure_user_modal';
?>
<?php 		break; ?>
<?php 		default: ?>
<?php
$button_attrs['class'] .= ' js-simpleLink';
$button_attrs['href']   = 'auth/login';
?>
<?php 		break; ?>
<?php 	endswitch; ?>
		<button <?php echo Util_Array::conv_array2attr_string($button_attrs); ?>>
			<i class="glyphicon glyphicon-user"></i><span class="hidden-xs-inline"> <?php echo Auth::get_screen_name(); ?></span>
			<span class="caret"></span>
		</button>

<?php endif; ?>
