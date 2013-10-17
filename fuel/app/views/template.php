<?php echo Html::doctype('html5'); ?>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width<?php if (IS_SP): ?>, initial-scale=1.0, maximum-scale=1.0, user-scalable=no<?php endif; ?>">
<meta name="description" content="<?php echo $header_description ? $header_description : PRJ_HEADER_DESCRIPTION_DEFAULT; ?>">
<meta name="keywords" content="<?php echo site_header_keywords($header_keywords); ?>">
<title><?php echo $header_title ? $header_title : $title; ?></title>
<meta name="robots" content="<?php if (PRJ_ENVIRONMENT == 'PRODUCTION'): ?>index,follow<?php else: ?>noindex,nofollow<?php endif; ?>">
<?php if (GOOGLE_SITE_VERIFICATION): ?>  <meta name="google-site-verification" content="<?php echo GOOGLE_SITE_VERIFICATION; ?>" /><?php endif; ?>
<?php echo render('_parts/template/load_common_css'); ?>
<?php if (IS_SP): ?><?php echo Asset::css('base_mobile.css');?><?php else: ?><?php echo Asset::css('base_pc.css');?><?php endif; ?>
<?php echo Asset::css('site.css');?>
<?php echo render('_parts/template/load_common_favicon'); ?>
<?php if (isset($post_header)): ?>
<?php echo $post_header; ?>
<?php endif; ?>
</head>
<body id="<?php echo site_get_current_page_id(); ?>">
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

<div class="container-fluid">
	<div class="row-fluid">
		<div class="span9">
<?php if (!empty($breadcrumbs)): ?>
			<ul class="breadcrumb">
<?php foreach ($breadcrumbs as $path => $name): ?>
<?php if ($path): ?>
				<li><?php echo Html::anchor($path, strim($name, 30)); ?> <span class="divider">/</span></li>
<?php else: ?>
				<li class="active"><?php echo strim($name, 30); ?></li>
<?php endif; ?>
<?php endforeach; ?>
			</ul>
<?php endif; ?>
<?php
$message = '';
if (Session::get_flash('message')) $message = Session::get_flash('message');
if (Input::get('msg')) $message = e(Input::get('msg'));
?>
<?php if ($message): ?>
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
<?php if (isset($header_info)): ?>
				<?php echo $header_info; ?>
<?php endif; ?>
<?php if (isset($title)): ?>
				<?php echo $title; ?>
<?php endif; ?>
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
<?php if (isset($content)) echo $content; ?>
			</div>
<?php endif; ?>
		</div><!--/span-->

		<div class="span3">
<?php if (Auth::check()): ?>
			<div class="well sidebar-nav">
				<div class="profile_img_box">
					<a class="account-summary account-summary-small" data-nav="profile" href="<?php echo Uri::create('member/profile'); ?>">
					<div class="content">
					<div class="account-group js-mini-current-user" data-screen-name="<?php echo site_get_screen_name($u); ?>">
					<?php echo img($u->get_image(), '50x50xc', '', false, site_get_screen_name($u), true); ?>
					<div class="main"><b class="fullname"><?php echo site_get_screen_name($u); ?></b></div>
					<small class="metadata">プロフィールを見る</small>
					</div>
					</div>
					</a>
				</div>

				<ul class="nav nav-list">
					<li class="nav-header">Member</li>
<?php foreach (Config::get('navigation.site.secure_side') as $name => $path): ?>
					<li<?php if (Uri::string().'/' == $path): ?><?php echo ' class="active"'; ?><?php endif; ?>><?php echo Html::anchor($path, $name); ?></li>
<?php endforeach; ?>
				</ul>
			</div><!--/.well -->
<?php endif; ?>

			<div class="well sidebar-nav">
				<ul class="nav nav-list">
					<li class="nav-header">Site</li>
<?php foreach (Config::get('navigation.site.global_side') as $name => $path): ?>
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
<script type="text/javascript" charset="utf-8">
function get_uid() {return <?php echo Auth::check() ? $u->id : 0; ?>;}
</script>
<?php echo Asset::js('util.js');?>
<?php echo Asset::js('site.js');?>
<?php if (!Auth::check()): ?>
<?php $destination = Session::get_flash('destination') ?: urlencode(Input::server('REQUEST_URI'));?>
<script type="text/javascript" charset="utf-8">
	var inputs = new Array('#form_email', '#form_password');
	load_popover('#insecure_user_menu', '#insecure_user_popover', '<?php echo Uri::create('auth/api/login').'?destination='.$destination; ?>', inputs);
</script>
<?php endif; ?>
<?php if (isset($post_footer)): ?><?php echo $post_footer; ?><?php endif; ?>
<?php echo site_htmltag_include_js_module();?>
<?php echo site_htmltag_include_js_action();?>
</body>
</html>
