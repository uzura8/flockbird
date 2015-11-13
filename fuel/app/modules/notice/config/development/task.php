<?php

return array(
	'noticeSendMail' => array(
		'loopMax' => 3,
		'sleepTime' => 3,
		'runningFlag' => array(
			'enabledPriod' => '1 minute',// if runningFlag is on over enabledPriod, runningFlag forced to be off.
		),
		'limit' => array(
			'model' => array(
				'delete' => array(
					'withSendMail' => 3,
				),
			),
		),
	),
);
