<?php

$config = array(
	'default' => array(
		'statusFlags' => array(
			'unexecuted' => 0,
			'successed' => 1,
			'failed' => 2,
			'skipped' => 3,
		),
		'runningFlag' => array(
			'enabledPriod' => 60 * 60 * 1,// if runningFlag is on over enabledPriod, runningFlag forced to be off.
		),
		'loopMax' => 50,
		'sleepTime' => 5,
		'limit' => array(
			'default' => 50,
			'sendMail' => 20,
			'model' => array(
				'update' => 40,
				'delete' => array(
					'normal' => 30,
					'file' => 10,
				),
			),
		),
	),
);

return Site_Config::merge_module_configs($config, 'task');
