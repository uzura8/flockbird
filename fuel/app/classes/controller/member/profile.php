<?php

class Controller_Member_profile extends Controller_Member
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();

		$this->auth_check();
	}

	/**
	 * Mmeber_profile index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{

		$member = $this->current_user;
		$this->template->title = $member->name.' さんのページ';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(
			Config::get('site.term.toppage') => '/',
			Config::get('site.term.myhome') => '/member/',
			Config::get('site.term.profile') => '',
		);

		$this->template->content = View::forge('member/profile/index');
	}

	/**
	 * Mmeber_profile setting_image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_setting_image()
	{
		$this->template->title = Config::get('site.term.profile').'写真設定';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(
			Config::get('site.term.toppage') => '/',
			Config::get('site.term.myhome') => '/member/',
			Config::get('site.term.profile') => '/member/profile/',
			Config::get('site.term.profile').'写真設定' => '',
		);

		$this->template->content = View::forge('member/profile/setting_image');
	}

	/**
	 * Mmeber_profile edit_image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_edit_image()
	{
		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();

			$config = array(
				'path'   => Config::get('site.image.member.original.path'),
				'prefix' => sprintf('m_%d_', $this->current_user->id),
			);
			Upload::process($config);

			$error = '';
			if (count(Upload::get_files()) != 1 || !Upload::is_valid())
			{
				$error = Upload::get_errors();
			}
			if (!$error)
			{
				Upload::save(0);
				$file = Upload::get_files(0);
				/**
				 * ここで$fileを使ってアップロード後の処理
				 * $fileの中にいろんな情報が入っている
				 **/

				try
				{
					$this->edit_images($file['saved_to'], $file['saved_as']);
				}
				catch(Exception $e)
				{
					$error = $e->getMessage();
				}
			}
			if ($error)
			{
				Session::set_flash('error', $error);
			}
			else
			{
				Session::set_flash('message', '写真を更新しました。');
			}
		}

		Response::redirect('member/profile/setting_image');
	}

	private function edit_images($original_file_dir, $original_file_name)
	{
		// 各サイズの icon を作成
		if (!self::make_icons($original_file_dir, $original_file_name))
		{
			throw new Exception('Resize error.');
		}

		$member = $this->current_user;
		// 古い icon の削除
		if (!self::remove_old_images($member->image))
		{
			throw new Exception('Remove old image error.');
		}

		// filename の保存
		$member->image = $original_file_name;
		$member->save();
	}

	private static function make_icons($original_file_dir, $original_file_name)
	{
		$original_file = $original_file_dir.$original_file_name;
		try
		{
			$config = Config::get('site.image.member.x-small');
			self::resize($original_file, $config['path'].'/'.$original_file_name, $config['width'], $config['height']);

			$config = Config::get('site.image.member.small');
			self::resize($original_file, $config['path'].'/'.$original_file_name, $config['width'], $config['height']);

			$config = Config::get('site.image.member.medium');
			self::resize($original_file, $config['path'].'/'.$original_file_name, $config['width'], $config['height']);

			$config = Config::get('site.image.member.lerge');
			self::resize($original_file, $config['path'].'/'.$original_file_name, $config['width'], $config['height']);
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
	}

	private static function resize($original_file, $resized_file, $width, $height)
	{
		return Image::load($original_file)
				->crop_resize($width, $height)
				->save($resized_file);
	}

	private static function remove_old_images($old_file_name)
	{
		if (!$old_file_name) return true;

		try
		{
			$config = Config::get('site.image.member.x-small');
			self::remove_image($config['path'].'/'.$old_file_name);

			$config = Config::get('site.image.member.small');
			self::remove_image($config['path'].'/'.$old_file_name);

			$config = Config::get('site.image.member.medium');
			self::remove_image($config['path'].'/'.$old_file_name);

			$config = Config::get('site.image.member.lerge');
			self::remove_image($config['path'].'/'.$old_file_name);
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
