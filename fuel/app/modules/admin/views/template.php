<?php echo Html::doctype('html5'); ?>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width<?php if (IS_SP): ?>, initial-scale=1.0, maximum-scale=1.0, user-scalable=no<?php endif; ?>">
<title><?php echo $header_title ? $header_title : $title; ?></title>
<meta name="robots" content="noindex,nofollow">

<?php echo Asset::css('bootstrap.custom.admin.css');?>
<?php echo render('_parts/template/load_common_css'); ?>
<?php echo Asset::css('admin.css');?>
<?php echo render('_parts/template/load_common_favicon'); ?>

<?php if (isset($post_header)): ?>
<?php echo $post_header; ?>
<?php endif; ?>
</head>
<body id="<?php echo site_get_current_page_id(); ?>">

<?php echo render('_parts/template/navbar'); ?>

<div class="container">
	<div class="row row-offcanvas row-offcanvas-right">
		<div class="col-sm-<?php if ($layout == 'wide'): ?>12<?php else: ?>9<?php endif; ?>">

<?php if (!empty($breadcrumbs) && !IS_SP): ?>
<?php echo render('_parts/template/breadcrumbs', array('list' => $breadcrumbs)); ?>
<?php endif; ?>

<?php echo render('_parts/template/global_alerts'); ?>

<?php
$title = isset($title) ? $title : null;
$subtitle = isset($subtitle) ? $subtitle : null;
$header_info = isset($header_info) ? $header_info : null;
echo render('_parts/template/page_header', array('title' => $title, 'subtitle' => $subtitle, 'header_info' => $header_info));
?>

<?php
$content = isset($content) ? $content : null;
$sub_column = isset($sub_column) ? $sub_column : null;
echo render('_parts/template/main_content', array('content' => $content, 'sub_column' => $sub_column));
?>

<?php if (!empty($breadcrumbs) && IS_SP): ?>
<?php echo render('_parts/template/breadcrumbs', array('list' => $breadcrumbs)); ?>
<?php endif; ?>

		</div><!--/span-->

<?php if ($layout == 'normal'): ?>
		<div class="col-sm-3" id="sidebar" role="navigation">
<?php if (Auth::check()): ?>
				<?php echo render('_parts/nav_list', array('header' => 'admin menu', 'list' => Config::get('navigation.admin.secure_side_admin'))); ?>
				<?php echo render('_parts/nav_list', array('header' => 'user menu', 'list' => Config::get('navigation.admin.secure_side_user'))); ?>
<?php else: ?>
				<?php echo render('_parts/nav_list', array('header' => 'menu', 'list' => Config::get('navigation.admin.insecure_side'))); ?>
<?php endif; ?>
				<?php echo render('_parts/nav_list', array('header' => 'site menu', 'list' => Config::get('navigation.admin.global_side'))); ?>

<?php if (isset($subside_contents)): ?>
<?php echo $subside_contents; ?>
<?php endif; ?>
		</div><!--/span-->
<?php endif; ?>

	</div><!--/row-->

<?php echo render('_parts/template/footer'); ?>

</div><!--/.fluid-container-->

<?php echo render('_parts/template/load_common_js'); ?>
<?php echo render('_parts/template/common_footer_script'); ?>
<?php echo render('_parts/template/load_site_js'); ?>
<?php if (isset($post_footer)): ?><?php echo $post_footer; ?><?php endif; ?>
<?php echo site_htmltag_include_js_module();?>
<?php echo site_htmltag_include_js_action();?>

</body>
</html>
