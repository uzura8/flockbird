<?php
namespace Album;

class Site_Model
{
	public static function get_albums($limit, $page = 1, $self_member_id = 0, $target_member_obj = null, $is_mypage = false, $params = array(), $is_return_array = false)
	{
		if ($target_member_obj && !$target_member_obj instanceof \Model_Member) throw new InvalidArgumentException('parameter target_member_obj is invalid.');
		if (!is_array($params)) $params = (array)$params;
		if (!empty($params['select']) && !\DBUtil::field_exists('album', $params['select'])) throw new \ValidationFailedException();

		$params = array_merge($params, array(
			'where' => \Site_Model::get_where_params4list(
				$target_member_obj ? $target_member_obj->id : 0,
				$self_member_id ?: 0,
				$is_mypage,
				!empty($params['where']) ? $params['where'] : array()
			),
			'limit' => $limit,
			'order_by' => array('id' => 'desc'),
		));
		$data = Model_Album::get_pager_list($params, $page, $is_return_array);
		if (!$is_return_array)
		{
			$data['member'] = $target_member_obj;
			$data['is_member_page'] = $target_member_obj ? true : false;
		}		

		return $data;
	}

	public static function get_album_images($limit, $page = 1, $self_member_id = 0, $target_member_obj = null, $is_mypage = false, $params = array(), $is_return_array = false)
	{
		if (!is_array($params)) $params = (array)$params;
		if ($target_member_obj && !$target_member_obj instanceof \Model_Member) throw new InvalidArgumentException('parameter target_member_obj is invalid.');
		if (!empty($params['select']) && !\DBUtil::field_exists('album_image', $params['select'])) throw new \ValidationFailedException();

		$params = array_merge($params, array(
			'where' => \Site_Model::get_where_params4list(
				$target_member_obj ? $target_member_obj->id : 0,
				$self_member_id ?: 0,
				$is_mypage,
				!empty($params['where']) ? $params['where'] : array(),
				$target_member_obj ? 'album.member_id' : 'member_id'
			),
			'limit' => $limit,
			'order_by' => array('id' => 'desc'),
		));
		if ($target_member_obj) $params['related'] = array('album');
		$data = Model_AlbumImage::get_pager_list($params, $page, $is_return_array);
		$data['liked_album_image_ids'] = (conf('like.isEnabled') && $self_member_id) ?
			\Site_Model::get_liked_ids('album_image', $self_member_id, $data['list']) : array();

		return $data;
	}
}

