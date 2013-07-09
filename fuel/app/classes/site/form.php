<?php

class Site_Form
{
	public static function login($destination = '', $form_name = 'login', $is_horizontal = true, $input_size = 'input-large')
	{
		$form = Site_Util::get_form_instance($form_name, null, false, $is_horizontal);
		$form->add('email', 'メールアドレス', array('type' => 'email',  'class' => $input_size, 'value' => Input::post('email', '')))->add_rule('required');
		$form->add('password', 'パスワード',  array('type'=>'password', 'class' => $input_size))->add_rule('required');
		$form->add('rememberme', '', array('options' => array(1 => Config::get('site.term.remember_me')), 'type' => 'checkbox'));

		$form->add('destination', '',  array('type'=>'hidden', 'value' => $destination));
		$form->add('submit', '', array('type'=>'submit', 'value' => 'ログイン', 'class' => 'btn btn-primary'));

		return $form;
	}
}
