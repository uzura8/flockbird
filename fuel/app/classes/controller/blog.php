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

	public function action_view($slug)
	{
		$post = Model_Post::find_by_slug($slug);

		$this->template->title = $post->title;
		$this->template->content = View::forge('blog/view', array(
			'post' => $post,
		));
	}
}
