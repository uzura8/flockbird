<?php

$config = array(
	'default' => array(
		'statusFlags' => array(
			'unexecuted' => 0,
			'successed' => 1,
			'failed' => 2,
			'skipped' => 3,
		),
		'loopMax' => 50,
		'sleepTime' => 5,
		'limit' => array(
			'model' => array(
				'delete' => array(
					'normal' => 50,
					'file' => 10,
					'withSendMail' => 20,
				),
			),
		),
	),
);

return Site_Config::merge_module_configs($config, 'task');
