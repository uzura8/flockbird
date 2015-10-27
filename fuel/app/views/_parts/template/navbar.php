<div class="navbar navbar-fixed-top navbar-inverse" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
<?php if (IS_ADMIN): ?>
			<a class="navbar-brand" href="<?php echo Uri::create('admin'); ?>"><?php echo FBD_SITE_NAME.term('admin.view', 'page.view'); ?></a>
<?php else: ?>
<?php 	if (conf('navbar.largeLogo.isEnabled')): ?>
			<a class="navbar-brand hidden-lg hidden-md" href="<?php echo Uri::create('/'); ?>">
				<?php echo Html::img('assets/img/ico/logo.png', array('alt' => FBD_SITE_NAME)); ?>
			</a>
			<a class="navbar-brand-lg hidden-sm hidden-xs" href="<?php echo Uri::create('/'); ?>">
				<?php echo Html::img('assets/img/ico/logo_lg.png', array('alt' => FBD_SITE_NAME)); ?>
			</a>
<?php 	else: ?>
			<a class="navbar-brand" href="<?php echo Uri::create('/'); ?>">
				<?php echo Html::img('assets/img/ico/logo.png', array('alt' => FBD_SITE_NAME)); ?>
				<span class="hidden-xs hidden-sm"><?php echo FBD_SITE_NAME; ?></span>
			</a>
<?php 	endif; ?>
<?php endif; ?>
<?php if (conf('auth.isEnabled') || IS_ADMIN): ?>
<?php echo render_module('_parts/template/navbar_user_button', isset($path) ? array('path' => $path) : array(), IS_ADMIN ? 'admin' : ''); ?>
<?php endif; ?>
		</div>
		<div class="collapse navbar-collapse">
<?php echo render_module('_parts/template/navbar_menu_global_head', isset($path) ? array('path' => $path) : array(), IS_ADMIN ? 'admin' : ''); ?>
		</div><!-- /.nav-collapse -->
	</div><!-- /.container -->
</div><!-- /.navbar -->
