<?php
$config = array(
	'\Model_OauthProvider' => array(
		array('id' => 1, 'name' => 'Facebook'),
		array('id' => 2, 'name' => 'Twitter'),
		array('id' => 3, 'name' => 'Google'),
	),
);

return Site_Config::merge_module_configs($config, 'db_fixture');
