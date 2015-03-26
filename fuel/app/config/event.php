<?php

return array(
	'fuelphp' => array(
		'controller_started' => function()
		{
			if (Fuel::$env == Fuel::DEVELOPMENT)
			{
				$configs = \Config::get('less.less_source_files');
				foreach ($configs as $config)
				{
					//compile less
					Asset::less($config);
				}
			}
		},
	),
);
