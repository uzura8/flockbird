<?php echo Html::doctype('html5'); ?>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo (!$header_description) ? $header_description : PRJ_HEADER_DESCRIPTION_DEFAULT; ?>">
  <meta name="keywords" content="<?php echo site_header_keywords($header_keywords); ?>">
	<meta name="author" content="">
  <title><?php echo (!empty($header_title)) ? $header_title : $title; ?></title>

  <meta name="robots" content="noindex,nofollow">

  <?php echo Asset::css('bootstrap.min.css');?>
  <?php echo Asset::css('base.css');?>
  <?php echo Asset::css('bootstrap-responsive.min.css');?>
  <?php echo Asset::css('jquery.jgrowl.css');?>
  <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
  <link href="<?php echo Uri::create('assets/js/jquery.alerts/jquery.alerts.css'); ?>" rel="stylesheet" type="text/css" media="screen">
  <?php echo Asset::css('admin.css');?>

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="<?php echo Uri::create('assets/img/ico/favicon.ico'); ?>">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo Uri::create('assets/img/ico/apple-touch-icon-144-precomposed.png'); ?>">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo Uri::create('assets/img/ico/apple-touch-icon-114-precomposed.png'); ?>">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo Uri::create('assets/img/ico/apple-touch-icon-72-precomposed.png'); ?>">
    <link rel="apple-touch-icon-precomposed" href="<?php echo Uri::create('assets/img/ico/apple-touch-icon-57-precomposed.png'); ?>">

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

<?php echo Asset::js('jquery-1.7.2.min.js');?>
<?php echo Asset::js('bootstrap.min.js');?>
<?php echo Asset::js('jquery.alerts/jquery.alerts.js');?>
<?php echo Asset::js('jquery.autogrow-textarea.js');?>
<?php echo Asset::js('jquery.jgrowl_minimized.js');?>
<script type="text/javascript" charset="utf-8">
$('textarea.autogrow').autogrow();
</script>
<?php if (isset($post_footer)): ?>
<?php echo $post_footer; ?>
<?php endif; ?>
<!--
  <script src="../assets/js/bootstrap-transition.js"></script>
  <script src="../assets/js/bootstrap-alert.js"></script>
  <script src="../assets/js/bootstrap-modal.js"></script>
  <script src="../assets/js/bootstrap-dropdown.js"></script>
  <script src="../assets/js/bootstrap-scrollspy.js"></script>
  <script src="../assets/js/bootstrap-tab.js"></script>
  <script src="../assets/js/bootstrap-tooltip.js"></script>
  <script src="../assets/js/bootstrap-popover.js"></script>
  <script src="../assets/js/bootstrap-button.js"></script>
  <script src="../assets/js/bootstrap-collapse.js"></script>
  <script src="../assets/js/bootstrap-carousel.js"></script>
  <script src="../assets/js/bootstrap-typeahead.js"></script>
-->
</body>
</html>