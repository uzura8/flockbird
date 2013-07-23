<?php

class Controller_Media extends Controller
{
	private static $exts = array(
		'image' => array(
			'gif' => 'image/gif',
			'jpg' => 'image/jpeg',
			'jpeg'=> 'image/jpeg',
			'png' => 'image/png',
		),
	);

	public function action_img($identifier = null, $group_number = null, $size = null, $filename = null)
	{
		$filename = Util_string::get_exploded_last(Input::server('REQUEST_URI'), '/');
		if (!is_numeric($group_number)) $group_number = null;
		if ($group_number === null && $size === null && preg_match('/([0-9]+x[0-9]+)_noimage.gif/', $filename, $matches))
		{
			$size = $matches[1];
		}

		if (!Site_Upload::check_uploaded_file_exists($filename))
		{
			return new Response(null, 404);
		}

		$config = array(
			'identifier'   => $identifier,
			'group_number' => $group_number,
			'size'         => $size,
			'filename'     => $filename,
		);
		$img = new Site_image($config);
		$ext = $img->get_extension();

		return Response::forge(
			$img->get_image(),
			200,
			array('Content-Type' => static::$exts['image'][$ext])
		);
	}
}
