<?php
$uri = 'admin/message/create/site_info_all';
$method = 'GET';
$label = sprintf('%s %sに%s', icon('form.send'), term('member.view', 'member.all'), term('form.do_send'));
if (check_acl($uri, $method)) echo btn('form.send', $uri, 'edit', true, null, 'warning', null, null, null, null, false, false, $label);
?>

