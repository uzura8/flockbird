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

	public function action_img($size = null, $file_cate = null, $split_num = null, $filename = null)
	{
		$config = array(
			'file_cate' => $file_cate,
			'split_num' => $split_num,
			'size'      => $size,
			'filename'  => $filename,
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
