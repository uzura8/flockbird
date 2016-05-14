<?php

return array(
	'source_dir' => APPPATH.'assets/less/',
	'output_dir' => DOCROOT.'assets/css/cache/',
	'compiler' => '\\Less_Compiler_Lessphp',
	'source_files' => array(
		'bootstrap.custom.less',
		'base.less',
		'base_pc.less',
		'base_mobile.less',
		'site.less',
		'bootstrap.custom.admin.less',
		'admin.less',
	),
);
