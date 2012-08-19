<?php
$cookie_name = Util_admin::check_is_admin_request()? 'fuelcid_admin' : 'fuelcid';

return array(
	// special configuration settings for cookie based sessions
	'cookie' => array(
		'cookie_name' => $cookie_name,				// name of the session cookie for cookie based sessions
	),
);
