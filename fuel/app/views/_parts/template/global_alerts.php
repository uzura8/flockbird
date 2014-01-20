<?php
$message = '';
if (Session::get_flash('message')) $message = Session::get_flash('message');
if (Input::get('msg')) $message = e(Input::get('msg'));
if ($message)
{
	echo alert($message, 'success');
}
if ($error = Session::get_flash('error'))
{
	$view_alerts = View::forge('_parts/alerts', array('type' => 'danger'));
	$view_alerts->set_safe('message', view_convert_list($error));
	echo $view_alerts->render();
}
?>
