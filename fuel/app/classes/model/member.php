<?php
use Orm\Model;

class Model_Member extends Model
{
	protected static $_table_name = 'member';
	protected static $_properties = array(
		'id',
		'username',
		'password',
		'group',
		'email',
		'nickname',
		'last_login',
		'profile_fields',
		'register_type',// 0: normal, 1:facebook
		'created_at',
		'updated_at'
	);

	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
		),
	);

	public static function validate($factory)
	{
		$val = Validation::forge($factory);
		//$val->add_field('title', 'Title', 'required|max_length[255]');

		return $val;
	}

	/**
	 * Create new member from facebook
	 *
	 * @param   string
	 * @param   string
	 * @param   string  must contain valid email address
	 * @param   int     group id
	 * @param   Array
	 * @return  bool
	 */
	public function create_member_from_facebook($facebook_id, $nickname)
	{
		$facebook_id = trim($facebook_id);
		if (empty($facebook_id))
		{
			throw new \SimpleUserUpdateException('Facebook can\'t be empty.');
		}

		$username = 'fb_'.$facebook_id.'_'.date('YmdHis');
		$nickname = trim($nickname);
		$group = 1;
		$profile_fields = array();

		Config::load('simpleauth', 'simpleauth');
		$same_users = \DB::select_array(\Config::get('simpleauth.table_columns', array('*')))
			->where('username', '=', $username)
			->from(\Config::get('simpleauth.table_name'))
			->execute(\Config::get('simpleauth.db_connection'));

		if ($same_users->count() > 0)
		{
			throw new \SimpleUserUpdateException('Username already exists');
		}

		$user = array(
			'username'        => (string) $username,
			'nickname'        => (string) $nickname,
			'group'           => (int) $group,
			'profile_fields'  => serialize($profile_fields),
			'register_type'   => 1,// 0: normal, 1:facebook
			'created_at'      => date('Y-m-d H:i:s')
		);

		$result = \DB::insert(\Config::get('simpleauth.table_name'))
			->set($user)
			->execute(\Config::get('simpleauth.db_connection'));

		return ($result[1] > 0) ? $result[0] : false;
	}
}
