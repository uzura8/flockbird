<?php
return array(
	'messageSendMail' => array(
		'runningFlag' => array(
			'enabledPriod' => 60 * 1,// if runningFlag is on over enabledPriod, runningFlag forced to be off.
		),
		'loopMax' => 3,
		'limit' => 2,
	),
);
