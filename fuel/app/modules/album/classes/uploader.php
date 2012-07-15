<?php
namespace Album;

class Uploader
{
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

	public function upload($album_id, $sizes = array())
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
				$this->save_images($file['saved_to'], $file['saved_as'], $album_id, $sizes);
			}
			catch(Exception $e)
			{
				$this->error = $e->getMessage();
			}
		}

		return empty($this->error);
	}

	private function save_images($original_file_dir, $original_file_name, $album_id, $sizes)
	{
		// 各サイズの icon を作成
		if (!self::make_thumbnails($original_file_dir, $original_file_name, $sizes))
		{
			throw new Exception('Resize error.');
		}
/*
		$member = $this->current_user;
		// 古い icon の削除
		if (!self::remove_old_images($member->image))
		{
			throw new Exception('Remove old image error.');
		}
*/
		// filename の保存
		$album_image = Model_AlbumImage::forge(array(
			'album_id' => (int)$album_id,
			'image' => $original_file_name,
		));

		return $album_image->save();
	}

	private static function make_thumbnails($original_file_dir, $original_file_name, $sizes)
	{
		$original_file = $original_file_dir.$original_file_name;
		try
		{
			foreach ($sizes as $key => $config)
			{
				if ($key == 'original') continue;
				\Util_file::resize($original_file, $config['path'].'/'.$original_file_name, $config['width'], $config['height']);
			}
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
	}

	private static function remove_old_images($old_file_name, $sizes)
	{
		if (!$old_file_name) return true;

		try
		{
			foreach ($sizes as $key => $config)
			{
				//if ($key == 'original') continue;
				\Util_file::remove($config['path'].'/'.$old_file_name);
			}
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
	}
}
