<?php

class Controller_Media extends Controller
{
	private static $exts = array();

	public function before()
	{
		parent::before();
	}

	public function action_img($size = null, $file_cate = null, $split_num = null, $file_name = null)
	{
		$file_name = sprintf('%s.%s', $file_name, Input::extension());
		$config = array(
			'file_cate' => $file_cate,
			'split_num' => $split_num,
			'size'      => $size,
			'file_name'  => $file_name,
		);
		$file = new Site_FileMaker($config);
		$ext = $file->get_extension();
		$accept_exts = conf('upload.types.img.accept_format');

		return Response::forge(
			$file->get_data(),
			200,
			array('Content-Type' => $accept_exts[$ext])
		);
	}

	public function action_img_tmp($size = null, $file_cate = null, $split_num = null, $file_name = null)
	{
		$file_name = sprintf('%s.%s', $file_name, Input::extension());
		$config = array(
			'is_tmp'    => true,
			'file_cate' => $file_cate,
			'split_num' => $split_num,
			'size'      => $size,
			'file_name'  => $file_name,
		);
		$file = new Site_FileMaker($config);
		$ext = $file->get_extension();
		$accept_exts = conf('upload.types.img.accept_format');

		return Response::forge(
			$file->get_data(),
			200,
			array('Content-Type' => $accept_exts[$ext])
		);
	}

	public function action_file($size = null, $file_cate = null, $split_num = null, $file_name = null)
	{
		$file_name = sprintf('%s.%s', $file_name, Input::extension());
		$config = array(
			'type' => 'file',
			'file_cate' => $file_cate,
			'split_num' => $split_num,
			'size'      => $size,
			'file_name'  => $file_name,
		);
		$file = new Site_FileMaker($config);
		if (!$data = $file->get_data()) throw new HttpNotFoundException();
		$ext = $file->get_extension();
		$accept_exts = conf('upload.types.file.accept_format');

		return Response::forge(
			$data,
			200,
			array('Content-Type' => $accept_exts[$ext])
		);
	}
}
