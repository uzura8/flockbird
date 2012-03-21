<?php

function include_partial($template, $data = array())
{
	$templabe_path = sprintf('%s/views/%s.php', APPPATH, $template);
	if (!file_exists($templabe_path)) throw new Exception('template file not exists.');;

	foreach ($data as $key => $value)
	{
		$$key = $value;
	}

	include $templabe_path;
	return;
}
