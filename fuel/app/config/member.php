<?php

return array(
	'group' => array(
		'options' => array(
			'admin' => 100,
			//'moderator' => 50,
			'specified_user' => 40,
			//'paid_user' => 20,
			'user' => 1,
			//'guest' => 0,
		),
		'defaultValue' => 1,
	),
	'status' => array(
		'options' => array(
			'normal' => 0,
		),
	),
	'profile' => array(
		'useCacheTable' => array(
			'isEnabled' => true,
		),
	),
	'address' => array(
		'isEnabled' => true,
		'type' => array(
			'options' => array(
				'optional' => 0,
				'main' => 1,
			),
		),
	),
);
