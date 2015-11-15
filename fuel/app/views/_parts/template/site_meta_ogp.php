<meta property="og:title" content="<?php echo strim($common['title'], conf('view_params_default.ogp.trimWidth.title'), null, false, true); ?>">
<meta property="og:type" content="<?php if (Uri::check_current_is_base_path()): ?>website<?php else: ?>article<?php endif; ?>">
<meta property="og:description" content="<?php echo !empty($common['description']) ?
	strim($common['description'], conf('view_params_default.ogp.trimWidth.body'), null, false, true) : FBD_HEADER_DESCRIPTION_DEFAULT; ?>">
<meta property="og:url" content="<?php echo Uri::current(); ?>">
<meta property="og:image" content="<?php echo Uri::create_url('assets/img/ico/apple-touch-icon-144-precomposed.png', null, 'url'); ?>">
<meta property="og:site_name" content="<?php echo FBD_SITE_NAME; ?>">
<meta property="og:email" content="<?php echo FBD_ADMIN_MAIL; ?>">
<?php if (is_enabled_share('facebook', 'share')): ?>
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
<?php endif; ?>

