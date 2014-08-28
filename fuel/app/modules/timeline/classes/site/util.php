<?php
namespace Timeline;

class Site_Util
{
	public static function get_accept_timeline_foreign_tables()
	{
		return array(
			'member',
			'note',
			'album_image',
			'album',
			'file',
		);
	}

	public static function get_type4key($type_key = null)
	{
		if (!$type_key) $type_key = 'normal';
		if (!$type = \Config::get('timeline.types.'.$type_key))
		{
			throw new \InvalidArgumentException('first parameter is invalid.');
		}

		return $type;
	}

	public static function get_key4type($target_type = null)
	{
		$types = \Config::get('timeeline.types');
		foreach ($types as $key => $type)
		{
			if ($type == $target_type) return $key;
		}

		throw new \InvalidArgumentException('first parameter is invalid.');
	}

	public static function check_type($target_type, $type_key)
	{
		return $target_type == self::get_type4key($type_key);
	}

	public static function get_timeline_save_values($type_key = null)
	{
		$type = self::get_type4key($type_key);
		$foreign_table = null;
		$child_foreign_table = null;
		switch ($type_key)
		{
			case 'member_register':
				$foreign_table = 'member';
				break;
			case 'member_name':
				$foreign_table = 'member';
				break;
			case 'profile_image':
				$foreign_table = 'file';
				break;
			case 'note':
				$foreign_table = 'note';
				break;
			case 'album':
				$foreign_table = 'album';
				$child_foreign_table = 'album_image';
				break;
			case 'album_image':
				$foreign_table = 'album';
				$child_foreign_table = 'album_image';
				break;
			case 'album_image_profile':
				$foreign_table = 'album_image';
				break;
			case 'album_image_timeline':
				$foreign_table = 'album';
				$child_foreign_table = 'album_image';
				break;
		}

		return array($type, $foreign_table, $child_foreign_table);
	}

	public static function get_timeline_body($type, $body = null, $foreign_table_obj = null, array $optional_info = null)
	{
		$is_safe = false;
		switch ($type)
		{
			case \Config::get('timeline.types.normal'):// 通常 timeline 投稿(つぶやき)
			case \Config::get('timeline.types.member_name'):// ニックネーム変更
				break;
			case \Config::get('timeline.types.member_register'):// SNS への参加
				$body = PRJ_SITE_NAME.' に参加しました。';
				break;
			case \Config::get('timeline.types.profile_image'):// profile 写真投稿
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				$body = term('profile', 'site.picture').'を設定しました。';
				break;
			case \Config::get('timeline.types.note'):// note 投稿
				$body = term('note').'を投稿しました。';
				break;
			case \Config::get('timeline.types.album'):// album 作成
				$body = term('album').'を作成しました。';
				break;
			case \Config::get('timeline.types.album_image'):// album_image 投稿
				$is_safe = true;
				$body = $foreign_table_obj ? render('timeline::_parts/body_for_add_album_image', array(
					'album_id' => $foreign_table_obj->id,
					'name' => $foreign_table_obj->name,
					'count' => isset($optional_info['count']) ? $optional_info['count'] : 0,
				)) : null;
				break;
			case \Config::get('timeline.types.album_image_timeline'):
				if (!strlen($body))
				{
					$body = sprintf('%sに%sを投稿しました。', term('timeline'), term('site.picture'));
					if (!empty($optional_info['count']))
					{
						$body = sprintf('%sに%sを %d 枚投稿しました。', term('timeline'), term('site.picture'), $optional_info['count']);
					}
				}
				break;
		}

		return array($body, $is_safe);
	}

	public static function get_timeline_content(Model_Timeline $timeline, \Orm\Model $foreign_table_obj = null, $optional_info = array(), $is_detail = false)
	{
		list($content, $is_safe_content) = self::get_timeline_body(
			$timeline->type,
			$timeline->body,
			$foreign_table_obj,
			$optional_info
		);
		if ($is_detail) return nl2br($content);

		if (strlen($content) && !$is_safe_content)
		{
			if ($truncate_lines = \Config::get('timeline.articles.truncate_lines.body', false))
			{
				$content = truncate_lines($content, $truncate_lines, 'timeline/'.$timeline->id);
			}
			elseif ($trim_width = \Config::get('timeline.articles.trim_width.body', false))
			{
				$content = strim($content, $trim_width);
			}
		}

		return $content;
	}

	public static function get_quote_article($type, $foreign_table_obj, $is_detail = false)
	{
		$accept_types = array(
			\Config::get('timeline.types.note'),
			\Config::get('timeline.types.album'),
			\Config::get('timeline.types.album_image'),
		);
		if (!in_array($type, $accept_types)) return null;

		$title = array(
			'value' => '',
			'truncate_count' => conf('view_params_default.list.trim_width.title')
		);
		$body = array('value' => $foreign_table_obj->body);
		if (!$is_detail)
		{
			$body['truncate_count'] = \Config::get('timeline.articles.truncate_lines.body');
			$body['truncate_type']  = 'line';
		}
		$read_more_uri  = '';

		switch ($type)
		{
			case \Config::get('timeline.types.note'):
				$title['value'] = $foreign_table_obj->title;
				$read_more_uri = 'note/'.$foreign_table_obj->id;
				break;
			case \Config::get('timeline.types.album'):
				$title['value'] = $foreign_table_obj->name;
				$read_more_uri = 'album/'.$foreign_table_obj->id;
				break;
			case \Config::get('timeline.types.album_image'):
				$read_more_uri = 'timeline/'.$foreign_table_obj->id;
				break;
		}

		return render('_parts/quote_article', array('title' => $title, 'body' => $body, 'read_more_uri' => $read_more_uri));
	}

	public static function get_detail_uri($timeline_id, $type, $foreign_table_obj)
	{
		switch ($type)
		{
			case \Config::get('timeline.types.note'):
				return 'note/'.$foreign_table_obj->id;
				break;
			case \Config::get('timeline.types.album'):
				return 'album/'.$foreign_table_obj->id;
				break;
				break;
		}

		return 'timeline/'.$timeline_id;
	}

	public static function get_type_for_save_comment_to_foreign_table()
	{
		return array(
			\Config::get('timeline.types.note'),
			\Config::get('timeline.types.album_image_profile'),
		);
	}

	public static function check_type_for_post_foreign_table_comment($type)
	{
		$types_for_save_comment_to_foreign_table = self::get_type_for_save_comment_to_foreign_table();
		if (in_array($type, $types_for_save_comment_to_foreign_table)) return true;

		return false;
	}

	public static function get_comment_parent_id($type, $timeline_id = 0, $foreign_id = 0)
	{
		return self::check_type_for_post_foreign_table_comment($type) ? $foreign_id : $timeline_id;
	}

	public static function get_comment_api_uri($action, $type, $timeline_id = 0, $foreign_id = 0)
	{
		$pre_path = 'timeline/';
		$id = $timeline_id;
		switch ($type)
		{
			case \Config::get('timeline.types.note'):// note 投稿
				$pre_path = 'note/';
				$id = $foreign_id;
				if ($action == 'get') $common_path = 'comment/api/list/'.$foreign_id.'.html';
				break;
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				$pre_path = 'album/image/';
				$id = $foreign_id;
				if ($action == 'get') $common_path = 'comment/api/list/'.$foreign_id.'.html';
				break;
		}

		switch ($action)
		{
			case 'create':
				$common_path = $id ? 'comment/api/create/'.$id.'.json' : 'comment/api/create.json';
				break;
			case 'delete':
				$common_path = $id ? 'comment/api/delete/'.$id.'.json' : 'comment/api/delete.json';
				break;
			case 'get':
			default :
				$common_path = $id ? 'comment/api/list/'.$id.'.html' : 'comment/api/list.json';
				break;
		}

		return $pre_path.$common_path;
	}

	public static function get_like_api_uri($type, $timeline_id = 0, $foreign_id = 0)
	{
		switch ($type)
		{
			case \Config::get('timeline.types.note'):// note 投稿
				return \Note\Site_Util::get_like_api_uri($foreign_id);
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				return \Album\Site_Util::get_like_api_uri4album_image($foreign_id);
		}

		return sprintf('timeline/like/api/update/%d.json', $timeline_id);
	}

	public static function get_liked_member_api_uri($timeline_id)
	{
		return sprintf('timeline/like/api/member/%d.html', $timeline_id);
	}

	public static function get_liked_member_api_uri4foreign_table($type, $timeline_id = 0, $foreign_id = 0)
	{
		switch ($type)
		{
			case \Config::get('timeline.types.note'):// note 投稿
				return \Note\Site_Util::get_liked_member_api_uri($foreign_id);
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				return \Album\Site_Util::get_liked_member_api_uri4album_image($foreign_id);
		}

		return self::get_liked_member_api_uri($timeline_id);
	}

	public static function check_type_to_get_access_from($type)
	{
		$types_to_get_access_from = array(
			\Config::get('timeline.types.album'),
			\Config::get('timeline.types.album_image'),
			\Config::get('timeline.types.album_image_timeline'),
		);

		return in_array($type, $types_to_get_access_from);
	}

	public static function get_timeline_images($type, $foreign_id, $timeline_id = null, $access_from = null)
	{
		// defaults
		$images = array();
		$images['file_cate']    = 'ai';
		$images['size']         = 'N_M';
		$images['column_count'] = 3;

		switch ($type)
		{
			case \Config::get('timeline.types.album_image_profile'):
				$images['list']   = array();
				$images['list'][] = \Album\Model_AlbumImage::check_authority($foreign_id);
				$images['additional_table'] = 'profile';
				$images['size']             = 'P_LL';
				$images['column_count']     = 2;
				break;

			case \Config::get('timeline.types.profile_image'):
				$images['list']   = array();
				$images['list'][] = \Model_File::find($foreign_id);
				$images['file_cate']        = 'm';
				$images['size']             = 'LL';
				$images['column_count']     = 2;
				break;

			case \Config::get('timeline.types.note'):
				list($images['list'], $images['count_all']) = \Note\Model_NoteAlbumImage::get_album_image4note_id(
					$foreign_id,
					\Config::get('timeline.articles.thumbnail.limit.default'),
					array('id' => 'asc'),
					true
				);
				$images['additional_table'] = 'note';
				$images['parent_page_uri']  = 'note/'.$foreign_id;
				break;

			case \Config::get('timeline.types.album'):
			case \Config::get('timeline.types.album_image'):
				list($images['list'], $images['count']) = \Site_Model::get_list_and_count('album_image', array(
					'where' => \Site_Model::get_where_public_flag4access_from(
						$access_from,
						array(array('id', 'in', Model_TimelineChildData::get_foreign_ids4timeline_id($timeline_id)))
					),
					'limit'    => \Config::get('timeline.articles.thumbnail.limit.default'),
					'order_by' => array('created_at' => 'asc'),
				), 'Album');
				$images['count_all'] = \Site_Model::get_count('album_image', array(
					'where' => \Site_Model::get_where_public_flag4access_from(
						$access_from,
						array(array('album_id', $foreign_id))
					),
				), 'Album');
				$images['parent_page_uri']  = 'album/'.$foreign_id;
				break;

			case \Config::get('timeline.types.album_image_timeline'):
				list($images['list'], $images['count']) = \Site_Model::get_list_and_count('album_image', array(
					'where' => \Site_Model::get_where_public_flag4access_from(
						$access_from,
						array(array('id', 'in', Model_TimelineChildData::get_foreign_ids4timeline_id($timeline_id)))
					),
					'limit'    => \Config::get('timeline.articles.thumbnail.limit.album_image_timeline'),
					'order_by' => array('created_at' => 'asc'),
				), 'Album');
				$images['count_all'] = $images['count'];
				$images['parent_page_uri']  = 'timeline/'.$timeline_id;
				break;

			default :
				break;
		}

		return $images;
	}

	public static function get_timeline_object($type_key, $foreign_id)
	{
		if (in_array($type_key, array('album', 'album_image', 'member_name')))
		{
			$table = $type_key;
			if ($type_key == 'album_image') $table = 'album';
			if ($type_key == 'member_name') $table = 'member';
			$since_datetime = date('Y-m-d H:i:s', strtotime('- '.\Config::get('timeline.periode_to_update.'.$type_key)));
			if ($timeline = Model_Timeline::get4latest_foreign_data($table, $foreign_id, $since_datetime))
			{
				return $timeline;
			}
		}

		return Model_Timeline::forge();
	}

	public static function check_is_editable($type)
	{
		$editable_types = array(
			\Config::get('timeline.types.normal'),
			\Config::get('timeline.types.note'),
			\Config::get('timeline.types.album'),
			\Config::get('timeline.types.album_image_timeline'),
		);

		return in_array($type, $editable_types);
	}

	public static function get_delete_api_info(Model_Timeline $timeline)
	{
		$path = '';
		$id = '';
		switch ($timeline->type)
		{
			case \Config::get('timeline.types.normal'):
			case \Config::get('timeline.types.album_image_timeline'):
				$id  = $timeline->id;
				$path = 'timeline/api/delete/';
				break;
			case \Config::get('timeline.types.note'):
				$id  = $timeline->foreign_id;
				$path = 'note/api/delete/';
				break;
			case \Config::get('timeline.types.album'):
				$id  = $timeline->foreign_id;
				$path = 'album/api/delete/';
				break;
			default :
				break;
		}
		if (!$path || !$id) return '';

		return sprintf('%s%s.json', $path, $id);
	}

	public static function get_public_flag_info(Model_Timeline $timeline)
	{
		$info = array(
			'model' => 'timeline',
			'public_flag_target_id' => $timeline->id,
			'have_children_public_flag' => false,
			'child_model' => null,
			'disabled_to_update' => false,
		);
		switch ($timeline->type)
		{
			case \Config::get('timeline.types.normal'):
			case \Config::get('timeline.types.album_image_timeline'):
				break;
			case \Config::get('timeline.types.note'):
				$info['model'] = 'note';
				$info['public_flag_target_id'] = $timeline->foreign_id;
				break;
			case \Config::get('timeline.types.album'):
				$info['model'] = 'album';
				$info['public_flag_target_id'] = $timeline->foreign_id;
				$info['have_children_public_flag'] = true;
				$info['child_model'] = 'album_image';
				break;
			case \Config::get('timeline.types.member_register'):
			case \Config::get('timeline.types.member_name'):
			case \Config::get('timeline.types.profile_image'):
			case \Config::get('timeline.types.album_image_profile'):
			case \Config::get('timeline.types.album_image'):
				$info['disabled_to_update'] = array('message' => '変更できません。');
				break;
			default :
				break;
		}

		return $info;
	}

	public static function get_article_main_view($timeline_id, $access_from_member_relation = null, $is_detail = false)
	{
		if ($is_detail || !\Config::get('timeline.articles.cache.is_use'))
		{
			return self::get_article_main_content($timeline_id, $access_from_member_relation, $is_detail);
		}

		$cache_key = self::get_cache_key($timeline_id, $access_from_member_relation);
		$cache_expir = \Config::get('timeline.articles.cache.expir');
		try
		{
			$content = \Cache::get($cache_key, $cache_expir);
		}
		catch (\CacheNotFoundException $e)
		{
			$content = self::get_article_main_content($timeline_id, $access_from_member_relation);
			\Cache::set($cache_key, $content, $cache_expir);
		}

		return $content;
	}

	public static function get_article_main_content($timeline_id, $access_from_member_relation = null, $is_detail = false)
	{
		$timeline = Model_Timeline::find($timeline_id, array('related' => array('member')));

		return render('timeline::_parts/article_main', array(
			'timeline' => $timeline,
			'access_from_member_relation' => $access_from_member_relation,
			'is_detail' => $is_detail,
		));
	}

	public static function get_cache_key($timeline_id, $access_from_member_relation = null)
	{
		$cache_key = \Config::get('timeline.articles.cache.prefix').$timeline_id;
		if ($access_from_member_relation) $cache_key .= '_'.$access_from_member_relation;

		return $cache_key;
	}

	public static function delete_cache($timeline_id, $type = null)
	{
		$cache_keys = array(self::get_cache_key($timeline_id));
		if (!$type || self::check_type_to_get_access_from($type))
		{
			$relations = array('self', 'member', 'others', 'friend');
			foreach ($relations as $relation)
			{
				$cache_keys[] = self::get_cache_key($timeline_id, $relation);
			}
		}
		foreach ($cache_keys as $cache_key)
		{
			\Cache::delete($cache_key);
		}
	}

	public static function get_member_relation_member_ids($member_id_from, $timeline_viewType = null)
	{
		$follow_member_ids = null;
		$friend_member_ids = null;
		$timeline_viewType = Site_Model::validate_timeline_viewType($timeline_viewType);
		switch ($timeline_viewType)
		{
			case 1:
				$follow_member_ids = \Model_MemberRelation::get_member_ids($member_id_from, 'is_follow');
				break;
			case 2:
				$friend_member_ids = \Model_MemberRelation::get_member_ids($member_id_from, 'is_friend');
				break;
			case 3:
				$follow_member_ids = \Model_MemberRelation::get_member_ids($member_id_from, 'is_follow');
				$friend_member_ids = \Model_MemberRelation::get_member_ids($member_id_from, 'is_friend');
				break;
			case 0:
			default :
				break;
		}

		return array($follow_member_ids, $friend_member_ids);
	}
}
