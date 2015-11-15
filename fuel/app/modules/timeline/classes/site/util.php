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
			'thread',
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
		$types = conf('types', 'timeline');
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
			case 'thread':
				$foreign_table = 'thread';
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

	public static function get_namespace4foreign_table($foreign_table)
	{
		switch ($foreign_table)
		{
			case 'note':
				return 'Note';
			case 'album':
			case 'album_image':
				return 'Album';
			case 'member':
			case 'file':
				return '';
		}

		throw new \InvalidArgumentException('first parameter is invalid.');;
	}

	public static function get_timeline_content($timeline_id, $type, $body = null, $foreign_table_obj = null, array $optional_info = null, $is_detail = false, $is_strip_tags = false)
	{
		switch ($type)
		{
			case \Config::get('timeline.types.normal'):// 通常 timeline 投稿(つぶやき)
			case \Config::get('timeline.types.album_image_timeline'):
			case \Config::get('timeline.types.member_name'):
				$return_body = self::get_normal_timeline_body($body, $type, $timeline_id, isset($optional_info['count']) ? $optional_info['count'] : 0, $is_detail);
				return $is_strip_tags ? \Security::strip_tags($return_body) : $return_body;

			case \Config::get('timeline.types.member_register'):// SNS への参加
				return FBD_SITE_NAME.' に参加しました。';

			case \Config::get('timeline.types.profile_image'):// profile 写真投稿
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				return term('profile', 'site.picture').'を設定しました。';

			case \Config::get('timeline.types.note'):// note 投稿
				return term('note').'を投稿しました。';

			case \Config::get('timeline.types.thread'):// thread 投稿
				return term('thread').'を投稿しました。';

			case \Config::get('timeline.types.album'):// album 作成
				return term('album').'を作成しました。';

			case \Config::get('timeline.types.album_image'):// album_image 投稿
				$return_body = $foreign_table_obj ? render('timeline::_parts/body_for_add_album_image', array(
					'album_id' => $foreign_table_obj->id,
					'name' => $foreign_table_obj->name,
					'count' => isset($optional_info['count']) ? $optional_info['count'] : 0,
				)) : null;
				return $is_strip_tags ? \Security::strip_tags($return_body) : $return_body;

			//case \Config::get('timeline.types.member_name'):// ニックネーム変更
			//	break;
		}

		return null;
	}

	public static function get_timeline_ogp_title($type)
	{
		switch ($type)
		{
			case \Config::get('timeline.types.normal'):// 通常 timeline 投稿(つぶやき)
			case \Config::get('timeline.types.album_image_timeline'):
			case \Config::get('timeline.types.member_name'):
				return term('timeline', 'form.post').'|'.FBD_SITE_NAME;

			case \Config::get('timeline.types.member_register'):// SNS への参加
				return FBD_SITE_NAME.' に参加しました。';

			case \Config::get('timeline.types.profile_image'):// profile 写真投稿
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				return term('profile', 'site.picture').'を設定しました。'.'|'.FBD_SITE_NAME;

			case \Config::get('timeline.types.note'):// note 投稿
				return term('note').'を投稿しました。'.'|'.FBD_SITE_NAME;

			case \Config::get('timeline.types.thread'):// thread 投稿
				return term('thread').'を投稿しました。'.'|'.FBD_SITE_NAME;

			case \Config::get('timeline.types.album'):// album 作成
				return term('album').'を作成しました。'.'|'.FBD_SITE_NAME;

			case \Config::get('timeline.types.album_image'):// album_image 投稿
				return term('album_image').'を投稿しました。'.'|'.FBD_SITE_NAME;
		}

		return term('timeline', 'form.post').'|'.FBD_SITE_NAME;
	}

	public static function get_normal_timeline_body($body, $type, $timeline_id, $image_count = 0, $is_detail = false)
	{
		if (!strlen($body) && $type == \Config::get('timeline.types.album_image_timeline'))
		{
			if (!$image_count)
			{
				return sprintf('%sに%sを %d 枚投稿しました。', term('timeline'), term('site.picture'), $image_count);
			}

			return sprintf('%sに%sを投稿しました。', term('timeline'), term('site.picture'));
		}

		return convert_body($body, array(
			'is_truncate'    => !$is_detail,
			'truncate_width' => conf('articles.trim_width.body', 'timeline'),
			'truncate_line'  => conf('articles.truncate_lines.body', 'timeline'),
			'read_more_uri'  => 'timeline/'.$timeline_id,
			'mention2link'   => true,
		));
	}

	public static function get_quote_article($type, $foreign_table_obj, $is_detail = false)
	{
		$accept_types = array(
			\Config::get('timeline.types.note'),
			\Config::get('timeline.types.thread'),
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
			case \Config::get('timeline.types.thread'):
				$title['value'] = $foreign_table_obj->title;
				$read_more_uri = 'thread/'.$foreign_table_obj->id;
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

	public static function get_detail_uri($timeline_id, $type, $foreign_table_obj = null)
	{
		$default = 'timeline/'.$timeline_id;
		if (!$foreign_table_obj) return $default;

		switch ($type)
		{
			case \Config::get('timeline.types.note'):
				return 'note/'.$foreign_table_obj->id;
				break;
			case \Config::get('timeline.types.thread'):
				return 'thread/'.$foreign_table_obj->id;
				break;
			case \Config::get('timeline.types.album'):
				return 'album/'.$foreign_table_obj->id;
				break;
		}

		return $default;
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
			case \Config::get('timeline.types.thread'):// thread 投稿
				$pre_path = 'thread/';
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
				$common_path = $id ? 'comment/api/list/'.$id.'.json' : 'comment/api/list.json';
				break;
		}

		return $pre_path.$common_path;
	}

	public static function get_like_api_uri($type, $timeline_id = 0, $foreign_id = 0)
	{
		switch ($type)
		{
			case \Config::get('timeline.types.note'):// note 投稿
				return \Site_Util::get_api_uri_update_like('note', $foreign_id);
			case \Config::get('timeline.types.thread'):// thread 投稿
				return \Site_Util::get_api_uri_update_like('thread', $foreign_id);
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				return \Site_Util::get_api_uri_update_like('album/image', $foreign_id);
		}

		return sprintf('timeline/like/api/update/%d.json', $timeline_id);
	}

	public static function get_liked_member_api_uri4foreign_table($type, $timeline_id = 0, $foreign_id = 0)
	{
		switch ($type)
		{
			case \Config::get('timeline.types.note'):// note 投稿
				return \Site_Util::get_api_uri_get_liked_members('note', $foreign_id);
			case \Config::get('timeline.types.thread'):// thread 投稿
				return \Site_Util::get_api_uri_get_liked_members('thread', $foreign_id);
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				return \Site_Util::get_api_uri_get_liked_members('album/image', $foreign_id);
		}

		return \Site_Util::get_api_uri_get_liked_members('timeline', $timeline_id);
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

	public static function get_timeline_images($type, $foreign_id, $timeline_id = null, $access_from = null, $is_detail = false)
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
				if ($list = \Album\Model_AlbumImage::check_authority($foreign_id))
				{
					$images['list'][]       = $list;
					$images['column_count'] = 2;
				}
				break;

			case \Config::get('timeline.types.profile_image'):
				$images['list']   = array();
				if ($list = \Model_File::find($foreign_id))
				{
					$images['list'][]       = $list;
					$images['file_cate']    = 'm';
					$images['size']         = 'LL';
					$images['column_count'] = 2;
				}
				break;

			case \Config::get('timeline.types.note'):
				list($images['list'], $images['count_all']) = \Note\Model_NoteAlbumImage::get_album_image4note_id(
					$foreign_id,
					$is_detail ? 0 : \Config::get('timeline.articles.thumbnail.limit.default'),
					array('id' => 'asc'),
					true
				);
				$images['parent_page_uri'] = 'note/'.$foreign_id;
				break;

			case \Config::get('timeline.types.thread'):
				list($images['list'], $images['count_all']) = \Thread\Model_ThreadImage::get4thread_id($foreign_id, 3, true);
				$images['file_cate']    = 't';
				$images['size']         = 'M';
				$images['column_count'] = 3;
				$images['parent_page_uri'] = 'thread/'.$foreign_id;
				break;

			case \Config::get('timeline.types.album'):
			case \Config::get('timeline.types.album_image'):
				$images['list'] = array();
				$images['count'] = 0;
				if ($album_image_ids = Model_TimelineChildData::get_foreign_ids4timeline_id($timeline_id))
				{
					list($images['list'], $images['count']) = \Album\Model_AlbumImage::get_list_and_count(array(
						'where' => \Site_Model::get_where_public_flag4access_from(
							$access_from,
							array(array('id', 'in', $album_image_ids))
						),
						$is_detail ? 0 : 'limit' => \Config::get('timeline.articles.thumbnail.limit.default'),
						'order_by' => array('created_at' => 'asc'),
					));
				}
				$images['count_all'] = \Album\Model_AlbumImage::get_list_count(array(
					'where' => \Site_Model::get_where_public_flag4access_from(
						$access_from,
						array(array('album_id', $foreign_id))
					),
				));
				$images['parent_page_uri']  = 'album/'.$foreign_id;
				break;

			case \Config::get('timeline.types.album_image_timeline'):
				list($images['list'], $images['count']) = \Album\Model_AlbumImage::get_list_and_count(array(
					'where' => \Site_Model::get_where_public_flag4access_from(
						$access_from,
						array(array('id', 'in', Model_TimelineChildData::get_foreign_ids4timeline_id($timeline_id)))
					),
					$is_detail ? 0 : 'limit' => \Config::get('timeline.articles.thumbnail.limit.album_image_timeline'),
					'order_by' => array('created_at' => 'asc'),
				));
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

	public static function get_edit_action_uri(Model_Timeline $timeline)
	{
		$table = '';
		$id = 0;
		switch (static::get_key4type($timeline->type))
		{
			case 'note':
			case 'album':
			case 'thread':
				$table = $timeline->foreign_table;
				$id = $timeline->foreign_id;
				break;
			case 'member_name':
				return 'member/profile/edit';
			case 'profile_image':
			case 'album_image_profile':
				return 'member/profile/image';
			case 'normal':
			case 'album_image_timeline':
			case 'member_register':
			case 'album_image':
			default :
				return '';
		}
		if (!$table || !$id) return '';

		return \Site_Util::get_action_uri($table, $id, 'edit');
	}

	public static function get_delete_api_uri(Model_Timeline $timeline)
	{
		$table = '';
		$id = 0;
		switch (static::get_key4type($timeline->type))
		{
			case 'normal':
			case 'album_image_timeline':
				$table = 'timeline';
				$id = $timeline->id;
				break;

			case 'note':
			case 'album':
			case 'thread':
			case 'album_image_profile':
				$table = $timeline->foreign_table;
				$id = $timeline->foreign_id;
				break;
		}
		if (!$table || !$id) return '';

		return \Site_Util::get_action_uri($table, $id, 'delete', 'json');
	}

	public static function get_member_watch_content_api_uri(Model_Timeline $timeline)
	{
		list($foreign_table, $foreign_id_column) = self::get_member_watch_content_info4timeline_type($timeline->type);
		if (!$foreign_table || !$foreign_id_column) return sprintf('member/notice/api/update_watch_status/timeline/%s.json', $timeline->id);

		return sprintf('member/notice/api/update_watch_status/%s/%s.json', $foreign_table, $timeline->{$foreign_id_column});
	}

	public static function get_member_watch_content_info4timeline_type($timeline_type)
	{
		$types = \Config::get('timeline.types');
		$foreign_table = '';
		$foreign_id_column = '';
		switch ($timeline_type)
		{
			case $types['normal']:
			case $types['member_register']:
			case $types['member_name']:
			case $types['profile_image']:
			case $types['album']:
			case $types['album_image_timeline']:
				$foreign_table = 'timeline';
				$foreign_id_column = 'id';
				break;
			case $types['note']:
				$foreign_table = 'note';
				$foreign_id_column = 'foreign_id';
				break;
			case $types['album_image']:
			case $types['album_image_profile']:
				$foreign_table = 'album_image';
				$foreign_id_column = 'foreign_id';
				break;
		}

		return array($foreign_table, $foreign_id_column);
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
			case \Config::get('timeline.types.thread'):
				$info['model'] = 'thread';
				$info['option_type'] = 'public';
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

		return self::get_view_cache($timeline_id, $access_from_member_relation, true);
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

	public static function get_view_cache($timeline_id, $access_from_member_relation = null, $is_make_cache = false)
	{
		$cache_key = self::get_cache_key($timeline_id, $access_from_member_relation);
		$cache_expir = \Config::get('timeline.articles.cache.expir');
		try
		{
			$content =  \Cache::get($cache_key, $cache_expir);
		}
		catch (\CacheNotFoundException $e)
		{
			$content = null;
			if ($is_make_cache)
			{
				$content = self::get_article_main_content($timeline_id, $access_from_member_relation);
				\Cache::set($cache_key, $content, $cache_expir);
			}
		}

		return $content;
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

	public static function make_view_cache4foreign_table_and_foreign_id($foreign_table, $foreign_id, $type = null)
	{
		$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids($foreign_table, $foreign_id, $type);
		$timeline = array_shift($timelines);
		Site_Util::get_article_main_view($timeline->id);

		return \Cache::get(\Timeline\Site_Util::get_cache_key($timeline->id), \Config::get('timeline.articles.cache.expir'));
	}

	public static function get_importance_level($comment_count, $like_count)
	{
		if (!\Config::get('timeline.importanceLevel.isEnabled', false)) return;
		if (!$levels = (array)\Config::get('timeline.importanceLevel.levels')) return;

		$comment_count_rate = \Config::get('timeline.importanceLevel.commentCountRate', 2);
		$point = $comment_count * $comment_count_rate + $like_count;

		krsort($levels);
		foreach ($levels as $level => $limit)
		{
			if ($point > $limit) return (int)$level;
		}

		return 0;
	}

	public static function get_list4view($self_member_id = 0, $target_member_id = 0, $is_mytimeline = false, $viewType = null, $params = array())
	{
		list($list, $next_id) = Site_Model::get_list(
			$self_member_id,
			$target_member_id,
			$is_mytimeline,
			$viewType,
			$params['max_id'],
			$params['limit'],
			$params['is_latest'],
			$params['is_desc'],
			$params['since_id']
		);
		$liked_timeline_ids = (conf('like.isEnabled') && $self_member_id) ? \Site_Model::get_liked_ids('timeline', $self_member_id, $list) : array();
		$data = array(
			'list' => $list,
			'next_id' => $next_id,
			'since_id' => $params['since_id'] ?: 0,
			'is_display_load_before_link' => $params['max_id'] ? true : false,
			'liked_timeline_ids' => $liked_timeline_ids,
		);

		return $data;
	}
}
