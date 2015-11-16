<?php
if (empty($uri)) $uri = Uri::string();
$full_url = Uri::create($uri);
if (empty($disableds)) $disableds = array();

$text_for_link  = !empty($text) ? strim($text, conf('view_params_default.share.trimWidth.link'), null, false, true) : '';
if (!$text_for_link) $text_for_link = FBD_SITE_NAME;
?>
<?php if (!in_array('facebook', $disableds) && is_enabled_share('facebook', 'share')): ?>
<?php echo btn('service.facebook.do_share', 'http://www.facebook.com/share.php?u='.$full_url, null, true, 'xs', 'primary',
			array('class' => 'mr10', 'onClick' => "window.open(this.href, 'FBwindow', 'width=650, height=450, menubar=no, toolbar=no, scrollbars=yes'); return false;"),
			null, null, null, false); ?>
<?php endif; ?>

<?php if (!in_array('twitter', $disableds) && is_enabled_share('twitter')): ?>
<?php echo btn('service.twitter.do_share', sprintf('http://twitter.com/share?url=%s&amp;text=%s', $full_url, urlencode($text_for_link)), null, true, 'xs', 'info',
			array('class' => 'mr10', 'target' => '_blank'), null, null, null, false); ?>
<?php endif; ?>

<?php if (!in_array('line', $disableds) && is_enabled_share('line')): ?>
<?php echo btn('service.line.do_share', sprintf('http://line.me/R/msg/text/?%s %s', urlencode($text_for_link), $full_url), null, true, 'xs', 'success',
			array('class' => 'mr10 fb-sp-view', 'target' => '_blank'), null, null, null, false); ?>
<?php endif; ?>

<?php if (!in_array('google', $disableds) && is_enabled_share('google')): ?>
<g:plusone data-href="<?php echo $full_url; ?>" expandTo="bottom"></g:plusone>
<?php endif; ?>

