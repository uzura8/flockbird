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
<?php echo render_module('_parts/template/navbar_user_button', isset($path) ? array('path' => $path) : array(), IS_ADMIN ? 'admin' : ''); ?>
		</div>
		<div class="collapse navbar-collapse">
<?php echo render_module('_parts/template/navbar_menu_global_head', isset($path) ? array('path' => $path) : array(), IS_ADMIN ? 'admin' : ''); ?>
		</div><!-- /.nav-collapse -->
	</div><!-- /.container -->
</div><!-- /.navbar -->
