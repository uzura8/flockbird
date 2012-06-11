<?php
class Model_Member extends \Orm\Model
{
	//protected static $_has_one = array('memberauth');
	protected static $_has_one = array(
		'memberauth' => array(
			'key_from' => 'id',
			'model_to' => 'Model_MemberAuth',
			'key_to' => 'member_id',
			'cascade_save' => false,
			'cascade_delete' => false,
		)
	);

	protected static $_table_name = 'member';
	protected static $_properties = array(
		'id',
		'name' => array(
			'validation' => array(
				'trim',
				'required',
				'max_length' => array(255),
			),
		),
		'login_hash' => array(
			'validation' => array(
				'trim',
				'max_length' => array(255),
			),
		),
		'last_login',
		'register_type' => array(// 0: normal, 1:facebook
			'validation' => array(
				'trim',
				'required',
				'match_pattern' => array('/[01]{1}/'),
			),
		),
		'image' => array(
			'validation' => array(
				'trim',
				'max_length' => array(100),
			),
		),
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
	public function create_member_from_facebook($facebook_id, $name)
	{
		$facebook_id = trim($facebook_id);
		if (empty($facebook_id)) return false;
		$name = trim($name);

		$user = array(
			'name'        => (string) $name,
			'register_type'   => 1,// 0: normal, 1:facebook
			'created_at'      => date('Y-m-d H:i:s')
		);
		Config::load('simpleauth', 'simpleauth');
		$result = \DB::insert(\Config::get('simpleauth.table_name'))
			->set($user)
			->execute(\Config::get('simpleauth.db_connection'));

		return ($result[1] > 0) ? $result[0] : false;
	}

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find()->where('id', $id)->get_one();
		if (!$obj) return false;

		if ($target_member_id && $obj->id != $target_member_id) return false;

		return $obj;
	}
}
