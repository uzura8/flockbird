<?php

class Controller_Media extends Controller
{
	private static $exts = array();

	public function before()
	{
		parent::before();

		static::$exts['image'] = Config::get('site.upload.types.img.accept_format');
	}

	public function action_img($size = null, $file_cate = null, $split_num = null, $filename = null)
	{
		Input::server('REQUEST_URI');
		$filename = Site_Util::get_uri_last_real_segment($filename, Site_Upload::get_accept_format());
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
