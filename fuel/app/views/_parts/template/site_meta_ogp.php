<meta property="og:title" content="<?php echo $common['title']; ?>">
<meta property="og:type" content="<?php if (Uri::check_current_is_base_path()): ?>website<?php else: ?>article<?php endif; ?>">
<meta property="og:description" content="<?php echo $description ? $description : FBD_HEADER_DESCRIPTION_DEFAULT; ?>">
<meta property="og:url" content="<?php echo Uri::current(); ?>">
<meta property="og:image" content="<?php echo Uri::create_url('assets/img/ico/apple-touch-icon-144-precomposed.png', null, 'url'); ?>">
<meta property="og:site_name" content="<?php echo FBD_SITE_NAME; ?>">
<meta property="og:email" content="<?php echo FBD_ADMIN_MAIL; ?>">
