<?php

return array(
	'fuelphp' => array(
		'controller_started' => function()
		{
			if (Fuel::$env == Fuel::DEVELOPMENT)
			{
				//compile less
				Asset::less('bootstrap.custom.less');
			}
		},
	),
);
