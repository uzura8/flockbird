<?php
return array(
	'note' => array(
		'isEnabled'  => 1,
	),
	'batch' => array(
		'limit' => array(
			'delete' => array(
				'note' => 50,
			),
		),
	),
	'service' => array(
		'facebook' => array(
			'shareDialog' => array(
				'note' => array(
					'isEnabled' => true,
					'autoPopupAfterCreated' => true,
				),
			),
		),
	),
);
