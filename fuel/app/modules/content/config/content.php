<?php

return array(
	'page' => array(
		'form' => array(
			'formats' => array(
				'options' => array(
					//'0' => 'text',
					'1' => 'html_editor',// enabled to wysiwyg editor(summernote)
					'2' => 'markdown',//    enabled to markdown editor
				),
				'default' => '1',
			),
		),
	),
	'viewParams' => array(
		'admin' => array(
			'list' => array(
				'limit' => 20,
				'limit_max' => 50,
				'trim_width' => array(
					'title' => 50,
					'body'  => 100,
				),
				'truncate_lines' => array(
					'body'  => 2,
				),
			),
		),
	),
);
