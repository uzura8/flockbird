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

<?php echo render('_parts/template/navbar'); ?>

<div class="container-fluid">
	<div class="row-fluid">
		<div class="span9">
<?php if (!empty($breadcrumbs) && !IS_SP): ?>
<?php echo render('_parts/template/breadcrumbs', array('list' => $breadcrumbs)); ?>
<?php endif; ?>

<?php echo render('_parts/template/global_alerts'); ?>

<?php if (!empty($title) || !empty($subtitle)): ?>
			<div class="page-header">
<?php if (isset($header_info)): ?>
				<?php echo $header_info; ?>
<?php endif; ?>
<?php if ($title): ?>
				<?php echo $title; ?>
<?php endif; ?>
<?php if (isset($subtitle)): ?>
				<div id="subtitle"><?php echo $subtitle; ?></div>
<?php endif; ?>
			</div><!-- page-header -->
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

<?php if (!empty($breadcrumbs) && IS_SP): ?>
<?php echo render('_parts/template/breadcrumbs', array('list' => $breadcrumbs)); ?>
<?php endif; ?>

		</div><!--/span-->

		<div class="span3">
<?php if (Auth::check()): ?>
			<div class="well sidebar-nav">
				<?php echo render('_parts/template/profile_img_box'); ?>
				<?php echo render('_parts/nav_list', array('header' => 'Member', 'list' => Config::get('navigation.site.secure_side'))); ?>
			</div><!--/.well -->
<?php endif; ?>

			<div class="well sidebar-nav">
				<?php echo render('_parts/nav_list', array('header' => 'Site', 'list' => Config::get('navigation.site.global_side'))); ?>
			</div><!--/.well -->

<?php if (isset($subside_contents)): ?>
<?php echo $subside_contents; ?>
<?php endif; ?>

		</div><!--/span-->
	</div><!--/row-->

<?php echo render('_parts/template/footer'); ?>

</div><!--/.fluid-container-->

<?php echo render('_parts/template/load_common_js'); ?>
<?php echo render('_parts/template/common_footer_script'); ?>
<script>
function get_uid() {return <?php echo Auth::check() ? $u->id : 0; ?>;}
</script>
<?php echo Asset::js('util.js');?>
<?php echo Asset::js('site.js');?>
<?php if (!Auth::check()): ?>
<?php $destination = Session::get_flash('destination') ?: urlencode(Input::server('REQUEST_URI'));?>
<script>
	var inputs = new Array('#form_email', '#form_password');
	load_popover('#insecure_user_menu', '#insecure_user_popover', '<?php echo Uri::create('auth/api/login').'?destination='.$destination; ?>', inputs);
</script>
<?php endif; ?>
<?php if (isset($post_footer)): ?><?php echo $post_footer; ?><?php endif; ?>
<?php echo site_htmltag_include_js_module();?>
<?php echo site_htmltag_include_js_action();?>
</body>
</html>
