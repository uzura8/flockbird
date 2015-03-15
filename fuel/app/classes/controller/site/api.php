<?php
class DisableToUpdateException extends \FuelException {}

class Controller_Site_Api extends Controller_Base_Site
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}


	/**
	 * 以下、共通 controller
	 * 
	 */

	/**
	 * Api delete common controller
	 * 
	 * @access  protected
	 * @param   string  $table           Delete target table
	 * @param   int     $id              Delete target record's id
	 * @param   string  $method          Excecuting method name
	 * @return  Response(json)  
	 */
	protected function api_delete_common($table, $id = null, $method = 'delete')
	{
		$this->controller_common_api(function() use($table, $id, $method)
		{
			$id = intval(\Input::post('id') ?: $id);
			$model = Site_Model::get_model_name($table);
			$obj = $model::check_authority($id, $this->u->id);

			if (is_enabled('album') && $table == 'album')
			{
				if ($result = \Album\Site_Util::check_album_disabled_to_update($obj->foreign_table))
				{
					throw new \DisableToUpdateException($result['message']);
				}
			}
			\DB::start_transaction();
			if ($table == 'timeline')
			{
				$result = \Timeline\Site_Model::delete_timeline($obj, $this->u->id);
			}
			else
			{
				$result = $obj->{$method}();
			}
			\DB::commit_transaction();
			$target_conntent_name = Site_Model::get_content_name($table);
			$data = array(
				'result'  => (bool)$result,
				'message' => sprintf('%s%sしました。', $target_conntent_name ? $target_conntent_name.'を' : '', term('form.delete')),
			);

			$this->set_response_body_api($data);
		});
	}

	/**
	 * Get comments common api controller
	 * 
	 * @access  protected
	 * @param   string  $parent_table  target parent table
	 * @param   int     $parent_id  target parent record id
	 * @param   string  $public_flag_related  related table for check brows authority
	 * @param   array   $parent_member_id_relateds  related table and property array for check edit authority
	 * @param   int     $limit  record count for get
	 * @param   int     $limit_max  record limited count for get
	 * @param   string  $parent_id_prop  parent table id property.
	 * @return  Response (json|html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	protected function api_get_comments_common($parent_table, $parent_id, $public_flag_related = null, $parent_member_id_relateds = array(), $limit = 0, $limit_max = 0, $parent_id_prop = null)
	{
		$this->api_accept_formats = array('json', 'html');
		$this->controller_common_api(function()
			use($parent_table, $parent_id, $public_flag_related, $parent_member_id_relateds, $limit, $limit_max, $parent_id_prop)
		{
			$comment_table = $parent_table.'_comment';
			$comment_model = Site_Model::get_model_name($comment_table);
			$parent_id = (int)$parent_id;
			$parent_model = Site_Model::get_model_name($parent_table);
			$parent_obj = $parent_model::check_authority($parent_id, 0, $public_flag_related);
			if (!$parent_id_prop) $parent_id_prop = $parent_table.'_id';

			$auther_member_ids = Util_Orm::get_related_member_ids($parent_obj, $parent_member_id_relateds);
			foreach ($auther_member_ids as $member_id)
			{
				$this->check_browse_authority($public_flag_related ? $parent_obj->{$public_flag_related}->public_flag : $parent_obj->public_flag, $member_id);
			}

			$member_profile_image_size = Input::get('image_size') ?: 'M';
			if (!in_array($member_profile_image_size, array('SS', 'S', 'M'))) $member_profile_image_size = 'M';
			$default_params = array(
				'latest' => 1,
				'limit' => $limit ?: conf('view_params_default.list.comment.limit'),
			);
			list($limit, $is_latest, $is_desc, $since_id, $max_id)
				= $this->common_get_list_params($default_params, $limit_max ?: conf('view_params_default.list.comment.limit_max'));
			list($list, $next_id, $all_comment_count)
				= $comment_model::get_list(array($parent_id_prop => $parent_id), $limit, $is_latest, $is_desc, $since_id, $max_id, null, $this->format == 'json', $this->format == 'json');

			if (conf('like.isEnabled'))
			{
				$liked_ids = \Auth::check() ? \Site_Model::get_liked_ids($comment_table, $this->u->id, $list) : array();
			}

			$api_uri_path_prefix = Site_Model::convert_table2controller_path($parent_table);
			$get_uri = sprintf('%s/comment/api/list/%d.json', $api_uri_path_prefix, $parent_id);
			$data = array(
				'list' => $list,
				'parent' => $parent_obj,
				'next_id' => $next_id,
				'delete_uri' => sprintf('%s/comment/api/delete.json', $api_uri_path_prefix),
				'image_size' => $member_profile_image_size,
			);
			if ($since_id) $data['since_id'] = $since_id;

			if ($this->format == 'html')
			{
				$data += array(
					'auther_member_ids' => $auther_member_ids,
					'list_more_box_attrs' => array(
						'id' => 'listMoreBox_comment_'.$parent_id,
						'data-uri' => $get_uri,
						'data-list' => '#comment_list_'.$parent_id,
					),
					'counter_selector' => '#comment_count_'.$parent_id,
				);
				if (conf('like.isEnabled')) $data['liked_ids'] = $liked_ids;
			}
			else
			{
				$data += array(
					'count' => $all_comment_count,
					'get_uri' => $get_uri,
				);
				$data['parent'] = array('id' => $parent_id, 'member_id' => $parent_member_id_relateds ? array_shift($auther_member_ids) : $parent_obj->member_id);
				$data['image_size'] = array(
					'key' => $member_profile_image_size,
					'value' => conf('upload.types.img.types.m.sizes.'.$member_profile_image_size),
				);
				foreach ($list as $key => $row)
				{
					$row['body'] = convert_body($row['body'], array(
						'nl2br' => false,
						'is_truncate' => false,
						'mention2link' => true,
					));
					$row['member'] = Model_Member::get_one_basic4id($row['member_id']);
					if (conf('like.isEnabled'))
					{
						$row['get_like_members_uri'] = sprintf('%s/comment/like/api/member/%d.html', $api_uri_path_prefix, $row['id']);
						$row['post_like_uri'] = sprintf('%s/comment/like/api/update/%d.json', $api_uri_path_prefix, $row['id']);
						$row['is_liked'] = (\Auth::check() && in_array($row['id'], $liked_ids)) ? 1 : 0;
						$row['comment_table'] = $comment_table;
					}
					$list[$key] = $row;
				}
				$data['list'] = $list;
			}

			$this->set_response_body_api($data, $this->format == 'html' ? '_parts/comment/list' : null);
		});
	}

	/**
	 * Get liked members common api controller
	 * 
	 * @access  protected
	 * @param   string  $parent_table  target parent table
	 * @param   int     $parent_id  target parent record id
	 * @param   string  $public_flag_related  related table for check brows authority
	 * @param   array   $parent_member_id_relateds  related table and property array for check edit authority
	 * @param   int     $limit  record count for get
	 * @param   int     $limit_max  record limited count for get
	 * @param   string  $parent_id_prop  parent table id property.
	 * @return  Response (json|html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	protected function api_get_liked_members_common($parent_table, $parent_id, $public_flag_related = null, $parent_member_id_relateds = array(), $limit = 0, $limit_max = 0, $parent_id_prop = null)
	{
		$this->api_accept_formats = array('json', 'html');
		$this->controller_common_api(function()
			use($parent_table, $parent_id, $public_flag_related, $parent_member_id_relateds, $limit, $limit_max, $parent_id_prop)
		{
			if (!conf('like.isEnabled')) throw new HttpNotFoundException();

			$like_model = Site_Model::get_model_name($parent_table.'_like');
			$parent_id = (int)$parent_id;
			$parent_model = Site_Model::get_model_name($parent_table);
			$parent_obj = $parent_model::check_authority($parent_id, 0, $public_flag_related);
			if (!$parent_id_prop) $parent_id_prop = $parent_table.'_id';

			$auther_member_ids = Util_Orm::get_related_member_ids($parent_obj, $parent_member_id_relateds);
			foreach ($auther_member_ids as $member_id)
			{
				$this->check_browse_authority($public_flag_related ? $parent_obj->{$public_flag_related}->public_flag : $parent_obj->public_flag, $member_id);
			}

			$default_params = array(
				'desc' => 1,
				'latest' => 1,
				'limit' => $limit ?: conf('view_params_default.like.members.popover.limit'),
			);
			list($limit, $is_latest, $is_desc, $since_id, $max_id)
				= $this->common_get_list_params($default_params, $limit_max ?: conf('view_params_default.like.members.popover.limit_max'));
			$params[$parent_id_prop] = $parent_id;
			list($list, $next_id) = $like_model::get_list($params, $limit, $is_latest, $is_desc, $since_id, $max_id, 'member', ($this->format == 'json'));

			$data = array(
				'list' => $list,
				'next_id' => $next_id,
			);
			if ($this->format == 'html')
			{
				$data += array(
					'related_member_table_name' => 'member',
					'is_simple_list' => true,
					'list_id' => 'liked_member_list_'.$parent_id,
					'get_uri' => Site_Util::get_api_uri_get_liked_members(Site_Model::convert_table2controller_path($parent_table), $parent_id),
					'no_data_message' => sprintf('%sしている%sはいません', term('form.like'), term('member.view')),
				);
				if ($since_id) $data['since_id'] = $since_id;
			}

			$this->set_response_body_api($data, $this->format == 'html' ? '_parts/member_list' : null);
		});
	}

	/**
	 * Get edit menu common api controller
	 * 
	 * @access  protected
	 * @param   string  $table  target table
	 * @param   int     $id  target record id
	 * @param   bool    $is_watch_target    if true,add menu to watch 
	 * @param   string  $parent_selector_prefix  use to define delete target
	 * @param   string  $member_related  related table for get member_id
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	protected function api_get_menu_common($table, $id, $is_watch_target = false, $parent_selector_prefix = null, $member_related = null)
	{
		$this->api_accept_formats = 'html';
		$this->controller_common_api(function() use($id, $table, $is_watch_target, $parent_selector_prefix, $member_related)
		{
			$id        = (int)$id;
			$is_detail = (bool)\Input::get('is_detail', 0);
			$model     = Site_Model::get_model_name($table);
			$obj       = $model::check_authority($id);
			$member_id = $member_related ? $obj->{$member_related}->member_id : $obj->member_id;
			$this->check_browse_authority($obj->public_flag, $member_id);

			$is_enabled_to_edit = true;
			if (is_enabled('album') && $table == 'album' && \Album\Site_Util::check_album_disabled_to_update($obj->foreign_table, true)) $is_enabled_to_edit = false;

			$menus = array();
			if ($member_id == $this->u->id)
			{
				if (is_enabled('album') && $table == 'album_image')
				{
					if ($add_menu = \Album\Site_Util::get_album_image_edit_menu($obj, $this->u->file_name)) $menus += $add_menu;
				}
				if (is_enabled('note') && $table == 'note')
				{
					$menus[] = array('icon_term' => 'form.do_publish', 'attr' => array(
						'class' => 'js-simplePost',
						'data-uri' => Site_Util::get_action_uri($table, $id, 'publish'),
						'data-msg' => term('form.publish').'しますか？',
					));
				}
				if ($is_enabled_to_edit)
				{
					$edit_uri = $table == 'timeline' ? \Timeline\Site_Util::get_edit_action_uri($obj) : Site_Util::get_action_uri($table, $id, 'edit');
					if ($edit_uri) $menus[] = array('href' => $edit_uri, 'icon_term' => 'form.do_edit');

					$delete_api_uri = $table == 'timeline' ? \Timeline\Site_Util::get_delete_api_uri($obj) : Site_Util::get_action_uri($table, $id, 'delete', 'json');
					$menus[] = array('icon_term' => 'form.do_delete', 'attr' => array(
						'class' => $is_detail ? 'js-simplePost' : 'js-ajax-delete',
						'data-uri' => $is_detail ? Site_Util::get_action_uri($table, $id, 'delete') : $delete_api_uri,
						'data-msg' => term('form.delete').'します。よろしいですか。',
						'data-parent' => sprintf('%s%d', $parent_selector_prefix ?: 'article_', $id),
					));
				}
				// add divider.
				if (!$is_detail && $menus) array_unshift($menus, array('tag' => 'divider'));
			}
			else
			{
				if (is_enabled('notice') && $is_watch_target)
				{
					if ($table == 'timeline')
					{
						list($foreign_table, $foreign_id_prop) = \Timeline\Site_Util::get_member_watch_content_info4timeline_type($obj->type);
						$is_watched = \Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id($foreign_table, $obj->{$foreign_id_prop}, $this->u->id);
						$api_uri = \Timeline\Site_Util::get_member_watch_content_api_uri($obj);
					}
					else
					{
						$is_watched = \Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id($table, $id, $this->u->id);
						$api_uri = sprintf('member/notice/api/update_watch_status/%s/%d', $table, $id);
					}
					$menus[] = array('icon_term' => $is_watched ? 'form.do_unwatch' : 'form.do_watch', 'attr' => array(
						'class' => 'js-update_toggle',
						'data-uri' => $api_uri,
						//'data-msg' => $is_watched ? term('form.watch').'を解除しますか？' : term('form.watch').'しますか？',
					));
				}
				if ($table == 'timeline' && conf('articleUnfollow.isEnabled', 'timeline'))
				{
					$is_followed = (bool)\Timeline\Model_MemberFollowTimeline::get4timeline_id_and_member_id($obj->id, $this->u->id);
					$menus[] = array('icon_term' => $is_followed ? 'followed' : 'do_follow', 'attr' => array(
						'class' => 'js-update_toggle',
						'data-uri' => sprintf('timeline/api/update_follow_status/%d.json', $obj->id),
						//'data-msg' => $is_followed ? term('follow').'を解除しますか？' : term('follow').'しますか？',
					));
				}
			}

			$this->set_response_body_api(array('menus' => $menus, 'is_ajax_loaded' => true), '_parts/dropdown_menu');
		});
	}

	/**
	 * Update public_flag common api controller
	 * 
	 * @access  protected
	 * @param   string  $table   target table
	 * @param   int     $id      target record id
	 * @param   string  $method  Excecuting method name
	 * @param   string  $type    Public flag type
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	protected function api_update_public_flag_common($table, $id, $method = 'update_public_flag', $type = 'default')
	{
		$this->api_accept_formats = 'html';
		$this->controller_common_api(function() use($table, $id, $method, $type)
		{
			$icon_only_flag = (int)\Input::post('icon_only_flag', 0);
			$id    = intval(\Input::post('id') ?: $id);
			$model = Site_Model::get_model_name($table);
			$obj   = $model::check_authority($id, $this->u->id);
			list($public_flag, $posted_model) = Site_Util::validate_params_for_update_public_flag($obj->public_flag, $type);

			\DB::start_transaction();
			$obj->{$method}($public_flag);
			\DB::commit_transaction();

			$data = array(
				'model'              => $posted_model,
				'id'                 => $id,
				'public_flag'        => $public_flag,
				'is_mycontents'      => true,
				'without_parent_box' => true,
				'view_icon_only'     => $icon_only_flag,
			);
			$this->set_response_body_api($data, '_parts/public_flag_selecter');
		});
	}

	/**
	 * Update like status common api controller
	 * 
	 * @access  protected
	 * @param   string  $parent_table  target parent table
	 * @param   int     $parent_id  target parent record id
	 * @param   string  $public_flag_related  related table for check brows authority
	 * @param   string  $member_related  related table for get member_id
	 * @param   string  $parent_id_prop  parent table id property.
	 * @return  Response (json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	protected function api_update_like_common($parent_table, $parent_id = null, $public_flag_related = null, $member_related = null, $parent_id_prop = null)
	{
		$this->controller_common_api(function() use($parent_table, $parent_id, $public_flag_related, $member_related, $parent_id_prop)
		{
			if (!conf('like.isEnabled')) throw new \HttpNotFoundException();

			$parent_id = intval(\Input::post('id') ?: $parent_id);
			$like_model = Site_Model::get_model_name($parent_table.'_like');
			if (!$parent_id_prop) $parent_id_prop = $parent_table.'_id';
			$parent_model = Site_Model::get_model_name($parent_table);
			$parent_obj   = $parent_model::check_authority($parent_id);
			$this->check_browse_authority(
				$public_flag_related ? $parent_obj->{$public_flag_related}->public_flag : $parent_obj->public_flag,
				$member_related      ? $parent_obj->{$member_related}->member_id : $parent_obj->member_id
			);

			\DB::start_transaction();
			$is_liked = (bool)$like_model::change_registered_status4unique_key(array(
				$parent_id_prop => $parent_obj->id,
				'member_id' => $this->u->id
			));
			\DB::commit_transaction();

			$get_count_method = 'get_count4'.$parent_id_prop;
			$data = array(
				'result'  => (int)$is_liked,
				'message' => sprintf('%s%s。', term('form.like'), $is_liked ? 'しました' : 'を取り消しました'),
				'count'   => $like_model::{$get_count_method}($parent_obj->id),
			);

			$this->set_response_body_api($data);
		});
	}

	/**
	 * Create comment common api controller
	 * 
	 * @access  protected
	 * @param   string  $parent_table  target parent table
	 * @param   int     $parent_id  target parent record id
	 * @param   string  $member_related  related table for get member_id
	 * @return  Response (json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	protected function api_create_comment_common($parent_table, $parent_id = null, $member_related = null)
	{
		$this->controller_common_api(function() use($parent_table, $parent_id, $member_related)
		{
			$parent_id = intval(\Input::post('id') ?: $parent_id);
			$parent_model = Site_Model::get_model_name($parent_table);
			$parent_obj = $parent_model::check_authority($parent_id);
			$this->check_browse_authority($parent_obj->public_flag, $member_related? $parent_obj->{$member_related}->member_id : $parent_obj->member_id);
			$parent_id_prop = $parent_table.'_id';
			$model = Site_Model::get_model_name($parent_table.'_comment');

			// Lazy validation
			$body = trim(\Input::post('body', ''));
			if (!strlen($body)) throw new \ValidationFailedException(sprintf('%sの入力は%sです。', term('form.comment'), term('form.required')));

			if ($parent_table == 'timeline' && \Timeline\Site_Util::check_type_for_post_foreign_table_comment($parent_obj->type))
			{
				throw new \HttpInvalidInputException;
			}

			\DB::start_transaction();
			// Create a new comment
			$comment = $model::forge(array(
				'body' => $body,
				$parent_id_prop => $parent_id,
				'member_id' => $this->u->id,
			));
			$result = (int)$comment->save();
			\DB::commit_transaction();

			$this->set_response_body_api(array('result' => $result, 'id' => $comment->id));
		});
	}
}
