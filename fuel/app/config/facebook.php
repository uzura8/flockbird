<?php
return array(
	'init' => array(
		'appId'  => PRJ_FACEBOOK_APP_ID,
		'secret' => PRJ_FACEBOOK_APP_SECRET,
	),
	'login' => array(
		'redirect_uri' => PRJ_BASE_URL.'facebook/callback/',
		'scope' => array('publish_stream',),
	),
	'logout' => array(
		'next' => PRJ_BASE_URL.'member',
	),
);

/* End of file facebook.php */
