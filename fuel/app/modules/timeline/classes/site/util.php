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

	public static function get_timeline_type($body = null, $foreign_table = null, $foreign_id = null, $foreign_column = null)
	{
		switch ($foreign_table)
		{
			case 'member':
				if ($foreign_column == 'file_id') return 3;// profile 写真投稿
				return 2;// SNS への参加
				break;
			case null:
				if ($body) return 1;// 通常 timeline 投稿(つぶやき)
				break;
			default :
				break;
		}

		return 0;
	}

	public static function get_timeline_body($type, $body = null)
	{
		switch ($type)
		{
			case 1:// 通常 timeline 投稿(つぶやき)
				return $body;
				break;
			case 2:// SNS への参加
				return PRJ_SITE_NAME.' に参加しました。';
				break;
			case 3:// profile 写真投稿
				return \Config::get('term.profile').'写真を設定しました。';
				break;
			case 4:// note 投稿
				return \Config::get('term.note').'を投稿しました。';
				break;
			default :
				break;
		}

		return $body;
	}

	public static function get_quote_article($type, $foreign_table, $foreign_id)
	{
		$accept_types = array();
		$accept_types[] = \Config::get('timeline.types.note');
		if (!in_array($type, $accept_types)) return null;

		$title = array('value' => '', 'truncate_count' => 0);
		$body  = array('value' => '', 'truncate_count' => 0, 'truncate_type' => 'line');
		$read_more_uri  = '';
		switch ($type)
		{
			case 4:// note 投稿
				$note = \Note\Model_Note::find($foreign_id);
				$title['value'] = $note->title;
				$title['truncate_count'] = \Config::get('site.view_params_default.list.trim_width.title');
				$body['value'] = $note->body;
				$body['truncate_count'] = \Config::get('timeline.articles.truncate_lines.body');
				$read_more_uri = 'note/'.$note->id;
				break;
			default :
				break;
		}

		return render('_parts/quote_article', array('title' => $title, 'body' => $body, 'read_more_uri' => $read_more_uri));
	}

	public static function get_timeline_images($type, $foreign_table, $foreign_id)
	{
		$accept_types = array();
		$accept_types[] = \Config::get('timeline.types.profile_image');
		$accept_types[] = \Config::get('timeline.types.note');
		if (!in_array($type, $accept_types)) return null;

		$images = array();
		if ($type == \Config::get('timeline.types.profile_image') && $foreign_table == 'album_image')
		{
			$images['list']   = array();
			$images['list'][] = \Album\Model_AlbumImage::find($foreign_id);
			$images['file_cate']        = 'ai';
			$images['additional_table'] = 'profile';
			$images['size']             = 'P_LL';
			$images['column_count']     = 2;
		}
		elseif ($type == \Config::get('timeline.types.profile_image') && $foreign_table == 'file')
		{
			$images['list']   = array();
			$images['list'][] = \Model_File::find($foreign_id);
			$images['file_cate']        = 'm';
			$images['size']             = 'LL';
			$images['column_count']     = 2;
		}
		elseif ($type == \Config::get('timeline.types.note') && $foreign_table == 'note')
		{
			$images['list']   = array();
			$images['list'] = \Note\Model_NoteAlbumImage::get_album_image4note_id($foreign_id, 3);
			$images['file_cate']        = 'ai';
			$images['additional_table'] = 'note';
			$images['size']             = 'N_M';
			$images['column_count']     = 3;
		}

		return $images;
	}

	public static function check_is_editable($type)
	{
		switch ($type)
		{
			case 1:
				return true;
				break;
		}

		return false;
	}

	public static function get_article_view(Model_Timeline $timeline)
	{
		//$timeline_data = Model_TimelineData::query()->related('member')->where('timeline_id', $timeline->id)->get_one();
		$timeline_data = Model_TimelineData::find('first', array(
			'where' => array('timeline_id' => $timeline->id),
			'related' => array('member')
		));

		return render('_parts/timeline/article', array(
			'timeline' => $timeline,
			'timeline_data' => $timeline_data,
			'truncate_lines' =>\Config::get('timeline.articles.truncate_lines.body'),
		));
	}
}
