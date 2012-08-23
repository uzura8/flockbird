<?php

class Site_uploader
{
	public $base_dir = '';
	public $model    = '';
	public $file_column = 'image';
	public $updates = array();
	public $error = '';

	public function __construct($options = array())
	{
		\Upload::process($options);
	}

	public function validate()
	{
		$result = true;
		if (count(\Upload::get_files()) != 1) $result = false;
		if (!\Upload::is_valid()) $result = false;

		if (!$result)
		{
			$errors = \Upload::get_errors();
			if (!empty($errors[0]['errors'][0]['message'])) $this->error = $result[0]['errors'][0]['message'];
		}

		return $result;
	}

	public function upload($content_id, $sizes = array(), $obj = '')
	{
		if ($this->validate())
		{
			\Upload::save(0);
			$file = \Upload::get_files(0);
			/**
			 * ここで$fileを使ってアップロード後の処理
			 * $fileの中にいろんな情報が入っている
			 **/

			try
			{
				// 各サイズの thumbnail を作成
				if (!$this->make_thumbnails($file['saved_to'], $file['saved_as'], $content_id, $sizes))
				{
					throw new Exception('Resize error.');
				}

				// 古い icon の削除
				$column = $this->file_column;
				if (!empty($obj) && !$this->remove_old_images($content_id, $obj->$column, $sizes))
				{
					throw new Exception('Remove old image error.');
				}

				// filename の保存
				if (empty($obj))
				{
					$obj = new $this->model();
				}
				$column = $this->file_column;
				$obj->$column = $file['saved_as'];
				foreach ($this->updates as $key => $value) $obj->$key = $value;
				$obj->save();
			}
			catch(Exception $e)
			{
				$this->error = $e->getMessage();
			}
		}

		return empty($this->error);
	}

	private function make_thumbnails($original_file_dir, $original_file_name, $content_id, $sizes)
	{
		$original_file = $original_file_dir.$original_file_name;
		try
		{
			foreach ($sizes as $size)
			{
				if ($size == 'raw') continue;

				$dir = sprintf('%s/%s', $this->base_dir, $size);
				if (!file_exists($dir) && $target_path = Util_file::check_exists_file_path($dir))
				{
					Util_file::make_dir_recursive($dir);
					Util_file::chmod_recursive($target_path, 0777);
				}

				$path = sprintf('%s/%s', $dir, $original_file_name);
				list($width, $height) = explode('x', $size);
				Util_file::resize($original_file, $path, $width, $height);
			}
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
	}

	private function remove_old_images($content_id, $old_file_name, $sizes)
	{
		if (!$old_file_name) return true;

		try
		{
			foreach ($sizes as $size)
			{
				$file = sprintf('%s/%s/%s', $this->base_dir, $size, $old_file_name);
				if (!file_exists($file)) continue;

				Util_file::remove($file);
			}
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
	}

	private static function remove_image($file)
	{
		if (!file_exists($file)) return;
		if (!$return = unlink($file))
		{
			throw new Exception('Remove image error.');
		}

		return $return;
	}
}
