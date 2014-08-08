<?php

return array(
	'driver'			=> 'file',

	// cookie path  (optional, default = '/')
	//'cookie_path'		=> PRJ_URI_PATH.'admin/',

	// specific configuration settings for file based sessions
	'file'				=> array(
		'cookie_name'		=> 'auth_token_admin',				// name of the session cookie for file based sessions
		'path'				=>	APPPATH.'/tmp',					// path where the session files should be stored
		'gc_probability'	=>	5						// probability % (between 0 and 100) for garbage collection
						),
);
