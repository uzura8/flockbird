<?php

class Controller_Blog extends Controller_Base
{
	public function action_index()
	{
		$view = View::forge('blog/index');

		$view->posts = Model_Post::find('all');

		$this->template->title = 'My Awesome Blog';
		$this->template->content = $view;
	}
}
