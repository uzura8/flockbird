<?php echo Html::doctype('html5'); ?>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width<?php if (IS_SP): ?>, initial-scale=1.0, maximum-scale=1.0, user-scalable=no<?php endif; ?>">
	<meta name="author" content="">
  <title><?php echo (!empty($header_title)) ? $header_title : $title; ?></title>
  <meta name="robots" content="noindex,nofollow">

  <?php echo render('_parts/template/load_common_css'); ?>
  <?php echo Asset::css('admin.css');?>
  <?php echo render('_parts/template/load_common_favicon'); ?>

<?php if (isset($post_header)): ?>
<?php echo $post_header; ?>
<?php endif; ?>
</head>
<body id="<?php echo site_get_current_page_id(); ?>">

<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container-fluid">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			<a class="brand" href="<?php echo Uri::create('/'); ?>"><?php echo PRJ_SITE_NAME; ?></a>
<?php if (Auth::check()): ?>
			<div class="btn-group pull-right">
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
					<i class="icon-user"></i> <?php echo site_get_screen_name($u); ?>
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu">
<?php foreach (Config::get('navigation.admin.secure_user_dropdown') as $name => $path): ?>
					<li<?php if (Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo Html::anchor($path, $name); ?></li>
<?php endforeach; ?>
				</ul>
			</div>
			<div class="nav-collapse">
				<ul class="nav">
<?php	$i = 1; ?>
<?php foreach (Config::get('navigation.admin.secure_global_head') as $name => $value): ?>
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
			<div class="btn-group pull-right">
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
					<i class="icon-user"></i> <?php echo site_get_screen_name($u); ?>
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu">
<?php foreach (Config::get('navigation.admin.insecure_user_dropdown') as $name => $path): ?>
					<li<?php if (Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo Html::anchor($path, $name); ?></li>
<?php endforeach; ?>
				</ul>
			</div>
			<div class="nav-collapse">
				<ul class="nav">
<?php	$i = 1; ?>
<?php foreach (Config::get('navigation.admin.insecure_global_head') as $name => $value): ?>
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
<?php endif; ?>
		</div>
	</div>

</div>

<div class="container-fluid">
	<div class="row-fluid">
		<div class="span9">

<?php if (isset($breadcrumbs)): ?>
			<ul class="breadcrumb">
	<?php foreach ($breadcrumbs as $name => $path): ?>
				<li><?php echo ($path) ? Html::anchor($path, $name).'<span class="divider">/</span>' : sprintf('<li class="active">%s</li>', $name);?></li>
	<?php endforeach; ?>
			</ul>
<?php endif; ?>

			<?php if ($message = Session::get_flash('message')): ?>
				<div class="alert alert-success">
					<a class="close" data-dismiss="alert">x</a>
					<?php echo $message; ?>
				</div>
			<?php endif; ?>
			<?php if ($error = Session::get_flash('error')): ?>
				<div class="alert alert-error">
					<a class="close" data-dismiss="alert">x</a>
					<?php echo view_convert_list($error); ?>
				</div>
			<?php endif; ?>

			<!-- title -->
<?php if (isset($title) || isset($subtitle)): ?>
			<div class="page-header">
<?php if (isset($title)): ?><h2><?php echo $title; ?></h2><?php endif; ?>
<?php if (isset($subtitle)): ?>
			<div id="subtitle"><?php echo $subtitle; ?></div>
<?php endif; ?>
			</div>
<?php endif; ?>

<?php if (isset($sub_column)): ?>
			<div class="row-fluid">
				<div class="span10" id="main_column">
<?php echo $content; ?>
				</div><!--/span-->
				<div class="span2">
<?php echo $sub_column; ?>
				</div><!--/span-->
			</div><!--/row-->
<?php else: ?>
			<div id="main">
<?php echo $content; ?>
			</div>
<?php endif; ?>
		</div><!--/span-->

		<div class="span3">
<?php if (Auth::check()): ?>
			<div class="well sidebar-nav">

				<ul class="nav nav-list">
					<li class="nav-header">Member</li>
<?php foreach (Config::get('navigation.admin.secure_side') as $name => $path): ?>
					<li<?php if (Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo Html::anchor($path, $name); ?></li>
<?php endforeach; ?>
				</ul>
			</div><!--/.well -->
<?php endif; ?>

			<div class="well sidebar-nav">
				<ul class="nav nav-list">
					<li class="nav-header">Site</li>
<?php foreach (Config::get('navigation.admin.global_side') as $name => $path): ?>
					<li<?php if (Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo Html::anchor($path, $name); ?></li>
<?php endforeach; ?>
				</ul>
			</div><!--/.well -->

<?php if (isset($subside_contents)): ?>
<?php echo $subside_contents; ?>
<?php endif; ?>

		</div><!--/span-->
	</div><!--/row-->
	<hr>
	<footer>
		<p>Copyright : <?php echo date('Y'); ?> <?php echo PRJ_SITE_NAME; ?></p>
	</footer>
</div><!--/.fluid-container-->

<?php echo render('_parts/template/load_common_js'); ?>
<?php echo render('_parts/template/common_footer_script'); ?>
<?php if (isset($post_footer)): ?>
<?php echo $post_footer; ?>
<?php endif; ?>
</body>
</html>
