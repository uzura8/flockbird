<?php

return array(
	'max_articles_per_day' => 50,
	'category' => array(
		'isEnabled' => true,
	),
	'image' => array(
		'isEnabled' => true,
		'isInsertBody' => true,
		'isModalUpload' => false,// modal(TODO: implement to file upload)
	),
	'file' => array(
		'isEnabled' => true,
	),
	'link' => array(
		'isEnabled' => true,
	),
	'tags' => array(
		'isEnabled' => true,
	),
	'form' => array(
		'formats' => array(
			'options' => array(
				//'0' => 'text',
				'1' => 'html_editor',// enabled to wysiwyg editor(summernote)
				'2' => 'markdown',//    enabled to markdown editor
			),
			'default' => '1',
		),
		'isSecure' => array(
			'isEnabled' => false,
		),
	),
	'viewParams' => array(
		'site' => array(
			'list' => array(
				'limit' => 5,
				'limit_max' => 100,
				'trim_width' => array(
					'title' => 88,
					'body'  => 200,
				),
				'truncate_lines' => array(
					'body'  => 5,
				),
			),
		),
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
	'display_setting' => array(
	),
);
