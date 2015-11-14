<?php

return array(
	'default' => array(
		'runningFlag' => array(
			'enabledPriod' => 60 * 1,// if runningFlag is on over enabledPriod, runningFlag forced to be off.
		),
		'statusFlags' => array(
			'unexecuted' => 0,
			'successed' => 1,
			'failed' => 2,
			'skipped' => 3,
		),
		'loopMax' => 3,
		'sleepTime' => 3,
		'limit' => array(
			'default' => 5,
			'sendMail' => 2,
			'model' => array(
				'update' => 2,
				'delete' => array(
					'normal' => 2,
					'file' => 2,
				),
			),
		),
	),
);
