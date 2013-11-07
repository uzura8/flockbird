<?php

return array(
	// if no session type is requested, use the default
	'driver'			=> 'file',

	// special configuration settings for cookie based sessions
	'cookie'			=> array(
		'cookie_name'		=> 'fuelcid',				// name of the session cookie for cookie based sessions
						),

	// specific configuration settings for file based sessions
	'file'				=> array(
		'cookie_name'		=> 'fuelfid',				// name of the session cookie for file based sessions
		'path'				=>	APPPATH.'/tmp',					// path where the session files should be stored
		'gc_probability'	=>	5						// probability % (between 0 and 100) for garbage collection
						),
);


