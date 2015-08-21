<?php echo Html::doctype('html5'); ?>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width<?php if (IS_SP): ?>, initial-scale=1.0, maximum-scale=1.0, user-scalable=no<?php endif; ?>">
<meta name="description" content="<?php echo $header_description ? $header_description : FBD_HEADER_DESCRIPTION_DEFAULT; ?>">
<meta name="keywords" content="<?php echo site_header_keywords($header_keywords); ?>">
<title><?php echo $header_title ? $header_title : $title; ?></title>
<meta name="robots" content="<?php if (is_prod_env()): ?>index,follow<?php else: ?>noindex,nofollow<?php endif; ?>">
<?php if (GOOGLE_SITE_VERIFICATION): ?>  <meta name="google-site-verification" content="<?php echo GOOGLE_SITE_VERIFICATION; ?>"><?php endif; ?>
<?php echo render('_parts/template/site_meta_ogp', array('description' => $header_description)); ?>
<?php echo render('_parts/template/load_common_css'); ?>
<?php echo render('_parts/template/load_site_css'); ?>
<?php echo render('_parts/template/load_common_favicon'); ?>
<?php if (isset($post_header)): ?>
<?php echo $post_header; ?>
<?php endif; ?>
</head>
<?php
$current_page_id = site_get_current_page_id();
$body_attrs = array('id' => $current_page_id);
if (conf('library.angularJs.isEnabled') && $use_angularjs) $body_attrs['ng-app'] = Inflector::camelize($current_page_id);
?>
<body <?php echo Util_Array::conv_array2attr_string($body_attrs); ?>>

<?php echo render('_parts/template/navbar'); ?>

<div class="container" id="main_container"<?php if (!empty($main_container_attrs)): ?> <?php echo Util_Array::conv_array2attr_string($main_container_attrs); ?><?php endif; ?>>
<?php if (isset($top_content)): ?>
	<div id="top_content">
<?php echo $top_content; ?>
	</div><!-- #top_content -->
<?php endif; ?>
	<div class="row row-offcanvas row-offcanvas-right">
		<div class="col-sm-<?php if ($layout == 'wide'): ?>12<?php else: ?>9<?php endif; ?>">
<?php if (!empty($breadcrumbs)): ?>
<div class="hidden-xs">
<?php echo render('_parts/template/breadcrumbs', array('list' => $breadcrumbs)); ?>
</div>
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

<?php if (!empty($breadcrumbs)): ?>
<div class="visible-xs">
<?php echo render('_parts/template/breadcrumbs', array('list' => $breadcrumbs)); ?>
</div>
<?php endif; ?>

		</div><!--/span-->

<?php if ($layout == 'normal'): ?>
		<div class="col-sm-3" id="sidebar" role="navigation">
<?php if (Auth::check()): ?>
			<?php echo render('_parts/template/profile_img_box'); ?>
			<?php echo render('_parts/nav_list', array('header' => term('member.view', 'common.menu'), 'list' => Config::get('navigation.site.secure_side'))); ?>
<?php endif; ?>
<?php if (conf('viewParams.site.sideMenu.categoryList', 'news') && $news_categories = \News\Site_Util::get_category_uris(conf('viewParams.site.sideMenu.categoryList.withAllLink', 'news'))): ?>
			<?php echo render('_parts/nav_list', array('header' => term('news.category.view'), 'list' => $news_categories)); ?>
<?php endif; ?>
			<?php echo render('_parts/nav_list', array('header' => term('site.view', 'common.menu'), 'list' => Config::get('navigation.site.global_side'))); ?>

<?php if (isset($subside_contents)): ?>
<?php echo $subside_contents; ?>
<?php endif; ?>

		</div><!--/span-->
<?php endif; ?>
	</div><!--/row-->

<?php echo render('_parts/template/footer'); ?>

</div><!--/.container-->

<?php echo render('_parts/template/load_common_js', array('use_angularjs' => $use_angularjs)); ?>
<?php echo render('_parts/template/common_footer_script'); ?>
<?php echo render('_parts/template/load_site_js'); ?>
<?php echo render('_parts/template/site_footer_script'); ?>
<?php if (isset($post_footer)): ?><?php echo $post_footer; ?><?php endif; ?>
<?php echo site_htmltag_include_js_module();?>
<?php echo site_htmltag_include_js_action();?>
<?php echo render('_parts/template/site_footer_optional_script'); ?>

</body>
</html>
