<?php

return array(
	'noticeSendMail' => array(
		'runningFlag' => array(
			'enabledPriod' => '3 hours',// if runningFlag is on over enabledPriod, runningFlag forced to be off.
		),
		'loopMax' => 30,
		'sleepTime' => 5,
		'limit' => array(
			'model' => array(
				'delete' => array(
					'withSendMail' => 20,
				),
			),
		),
	),
);
