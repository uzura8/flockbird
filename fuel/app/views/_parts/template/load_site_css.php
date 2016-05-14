<?php echo Asset::css('site.css', array(), null, false, false, false, true);?>
<?php
Asset::css('site.css', null, 'css_site', false, true, false, true);
echo Asset::render('css_site', false, 'css');
?>

