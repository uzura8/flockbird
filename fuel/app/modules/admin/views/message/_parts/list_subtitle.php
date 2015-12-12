<?php echo btn('form.create', 'note/create', 'edit'); ?>
<?php
$uri = 'admin/message/create';
$method = 'GET';
if (check_acl($uri, $method)) echo btn('form.create', $uri, 'edit');
?>

