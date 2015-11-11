<?php
if (empty($uri)) $uri = Uri::string();
$full_url = Uri::create($uri);
if (empty($disableds)) $disableds = array();
?>
<?php if (!in_array('facebook', $disableds) && is_enabled_share('facebook', 'share')): ?>
<?php echo btn('service.facebook.do_share', 'http://www.facebook.com/share.php?u='.$full_url, null, true, 'xs', 'primary',
			array('class' => 'mr10', 'onClick' => "window.open(this.href, 'FBwindow', 'width=650, height=450, menubar=no, toolbar=no, scrollbars=yes'); return false;"),
			null, null, null, false); ?>
<?php endif; ?>

<?php if (!in_array('twitter', $disableds) && is_enabled_share('twitter')): ?>
<?php echo btn('service.twitter.do_share', sprintf('http://twitter.com/share?url=%s&text=%s', $full_url, FBD_SITE_NAME), null, true, 'xs', 'info',
			array('class' => 'mr10', 'target' => '_blank'), null, null, null, false); ?>
<?php endif; ?>

<?php if (!in_array('line', $disableds) && is_enabled_share('line')): ?>
<?php echo btn('service.line.do_share', sprintf('http://line.me/R/msg/text/?%s %s', FBD_SITE_NAME, $full_url), null, true, 'xs', 'success',
			array('class' => 'mr10 fb-sp-view', 'target' => '_blank'), null, null, null, false); ?>
<?php endif; ?>

<?php if (!in_array('google', $disableds) && is_enabled_share('google')): ?>
<g:plusone data-href="<?php echo $full_url; ?>" expandTo="bottom"></g:plusone>
<?php endif; ?>

