<?php
return array(
	'contact' => array(
		'view' => 'Contact Us',
		'fields' => array(
			'default' => array(
				'body' => 'Details',
			),
			'pre' => array(
				'category' => array(
					'label' => 'Inquiry about',
					'options' => array(
						'0' => 'Please select',
						'1' => 'How to use this site',
						'2' => 'Others',
					),
				),
			),
		),
	),
	'report' => array(
		'view' => 'Report',
		'fields' => array(
			'default' => array(
				'body' => 'Additional information',
			),
			'pre' => array(
				'category' => array(
					'label' => 'Report about',
					'options' => array(
						'0' => 'Please select',
						'1' => 'Posting unpleasant information',
						'2' => 'Posting personal information',
						'3' => 'Spam post',
						'4' => 'Other reasons',
					),
				),
			),
		),
	),
	'form' => array(
		'post_report' => 'Report',
	),
);

