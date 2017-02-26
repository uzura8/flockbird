<?php
namespace Album;

class Site_Util
{
	public static function get_album_image_display_name(Model_AlbumImage $album_image, $default = '', $is_accept_hash_filename = false)
	{
		if (isset($album_image->name) && strlen($album_image->name)) return $album_image->name;
		if (!\Config::get('album.isDisplayOriginalFileName')) return $default;

		$file = \Model_File::get4name($album_image->file_name);
		if ($file && isset($file->original_filename) && strlen($file->original_filename))
		{
			return $file->original_filename;
		}

		if ($is_accept_hash_filename) return $album_image->file_name;

		return term('album_image');
	}

	public static function check_album_disabled_to_update($album_foreign_table, $is_bool = false, $action_type = 'edit')
	{
		if (!in_array($action_type, array('edit', 'delete'))) throw new InvalidArgumentException('Third parameter is invalid.');
		if (!$album_foreign_table) return false;
		if (!in_array($album_foreign_table, self::get_album_foreign_tables())) return false;
		if ($is_bool) return true;

		$label = t('common.delimitter.of', array(
			'subject' => t('public_flag.label'),
			'object' => static::get_album_type4table($album_foreign_table),
		));
		$lang_key = sprintf('message_disabled_to_%s_for', $action_type);

		return array('message' => __($lang_key, array('label' => $label)));
	}

	public static function get_album_foreign_tables()
	{
		return array('note', 'member', 'timeline');
	}

	public static function get_foreign_table_info($table_name)
	{
		$info = array('public_flag' => conf('public_flag.default'));
		$info['name'] = static::get_album_type4table($table_name);
		if ($table_name == 'member') $info['public_flag'] = conf('public_flag.maxRange');

		return $info;
	}

	public static function get_album_type4table($table_name, $is_throw_exception = true)
	{
		switch ($table_name)
		{
			case 'note':
				return t('common.delimitter.for', array('subject' => t('album.view'), 'object' => t('note.view')));
			case 'member':
				return t('common.delimitter.for', array('subject' => t('album.view'), 'object' => t('profile')));
			case 'timeline':
				return t('common.delimitter.for', array('subject' => t('album.view'), 'object' => t('timeline.view')));
		}
		if ($is_throw_exception) throw new InvalidArgumentException('First parameter is invalid.');

		return '';
	}

	public static function get_like_api_uri($album_image_id)
	{
		return sprintf('album/image/like/api/update/%d.json', $album_image_id);
	}

	public static function get_save_location_api_uri($album_image_id)
	{
		return sprintf('album/image/api/save_location/%d.json', $album_image_id);
	}

	public static function get_album_image_edit_menu(Model_AlbumImage $obj, $member_filename)
	{
		$menus = array();
		if ($obj->album->foreign_table == 'member')
		{
			if ($obj->file_name == $member_filename)
			{
				$menus[] = array('tag' => 'disabled', 'icon_term' => term(array('profile', 'site.image', 'site.set_already')));
			}
			else
			{
				$menus[] = array('icon_term' => 'form.set_profile_image', 'href' => '#', 'attr' => array(
					'class' => 'js-simplePost',
					'data-uri' => 'member/profile/image/set/'.$obj->id,
					'data-msg' => __('message_set_confirm_as', array('label' => term('profile', 'site.image'))),
				));
			}
		}
		else
		{
			if ($obj->album->cover_album_image_id == $obj->id)
			{
				$menus[] = array('tag' => 'disabled', 'icon_term' => 'form.set_cover_already');
			}
			else
			{
				$menus[] = array('icon_term' => 'form.set_cover', 'attr' => array(
					'class' => 'js-update_toggle',
					'data-uri' => \Site_Util::get_action_uri('album_image', $obj->id, 'set_cover', 'json'),
				));
			}
		}

		return $menus;
	}

	public static function get_top_slide_image_uris()
	{
		$cache_conf = conf('site.index.slide.recentAlbumImage.cache', 'page');
		if (!$cache_conf['isEnabled']) return static::get_top_slide_image_uris_raw();

		$cache_key   = $cache_conf['key'];
		$cache_expir = $cache_conf['expir'];
		try
		{
			$image_uris = \Cache::get($cache_key, $cache_expir);
		}
		catch (\CacheNotFoundException $e)
		{
			$image_uris = static::get_top_slide_image_uris_raw();
			\Cache::set($cache_key, $image_uris, $cache_expir);
		}

		return $image_uris;
	}

	public static function get_top_slide_image_uris_raw()
	{
		$configs = conf('site.index.slide.recentAlbumImage', 'page');
		$size_string = conf('upload.types.img.types.ai.sizes.'.$configs['sizeKey']);
		$limit_width = false;
		if ($sizes_for_resize = \Site_Upload::conv_size_str_to_array($size_string))
		{
			$limit_width = $sizes_for_resize['width'];
		}

		$limit = $configs['displayCount'] + $configs['displayCountAdditional'];
		$data = Site_Model::get_album_images($limit);
		if (empty($data['list'])) return $uris_default;

		$uris = array();
		$i = 0;
		foreach ($data['list'] as $album_image)
		{
			if ($i >= $configs['displayCount']) break;
			if (!static::check_album_imgage4top_page_slide($album_image, $limit_width)) continue;

			$uris[] = img_uri($album_image->file_name, $configs['sizeKey']);
			$i++;
		}
		$rest_count = $configs['displayCount'] - $i;
		if ($rest_count > 0)
		{
			$uris_default = conf('site.index.slide.images', 'page');
			if ($rest_count > count($uris_default)) $rest_count = count($uris_default);
			for ($i = 0; $i < $rest_count; $i++)
			{
				$uris[] = $uris_default[$i];
			}
		}

		return $uris;
	}

	protected static function check_album_imgage4top_page_slide(Model_AlbumImage $album_image, $limit_width = null)
	{
		if (!empty($album_image->album->foreign_table) && $album_image->album->foreign_table == 'member') return false;

		if ($limit_width)
		{
			if (!$file_path = \Site_Upload::get_file_path4file_name($album_image->file_name)) return false;
			$raw_size_obj = \Image::sizes($file_path);
			if ($raw_size_obj->width < $limit_width) return false;
		}

		return true;
	}
}
