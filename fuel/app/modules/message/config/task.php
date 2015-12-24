<?php
return array(
	'messageSendMail' => array(
		'runningFlag' => array(
			'enabledPriod' => 60 * 30,// if runningFlag is on over enabledPriod, runningFlag forced to be off.
		),
		'loopMax' => 30,
		'limit' => 20,
	),
);
