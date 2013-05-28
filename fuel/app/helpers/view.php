<?php

function include_partial($template, $data = array(), $module = null)
{
	
	$templabe_path = APPPATH;
	if ($module) $templabe_path .= 'modules/'.$module;
	$templabe_path .= sprintf('/views/%s.php', $template);
	if (!file_exists($templabe_path)) throw new Exception('template file not exists.');;

	foreach ($data as $key => $value)
	{
		$$key = $value;
	}

	include $templabe_path;
	return;
}

function view_convert_list($val)
{
	if (!is_array($val)) return $val;

	$list = array();
	foreach ($val as $row) $list[] = sprintf('<li>%s</li>', $row);

	return sprintf('<ul>%s</ul>', implode(PHP_EOL, $list));
}
