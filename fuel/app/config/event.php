<?php

return array(
	'fuelphp' => array(
		'controller_started' => function()
		{
			if (Fuel::$env == Fuel::DEVELOPMENT)
			{
				//compile less
				Asset::less('bootstrap.custom.less');
				Asset::less('base.less');
				Asset::less('base_pc.less');
				Asset::less('base_mobile.less');
				Asset::less('site.less');
				Asset::less('admin.less');
				Asset::less('bootstrap.custom.admin.less');
			}
		},
	),
);
