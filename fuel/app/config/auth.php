<?php

return array(
	'driver' => 'UzuraAuth',
	'verify_multiple_logins' => false,// true にすると remember_me でエラーが発生(v1.6 時点)
	'salt' => FBD_ENCRYPTION_KEY,
	'iterations' => 10000,
);
