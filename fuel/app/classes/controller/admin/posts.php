<?php
class Controller_Admin_Posts extends Controller_Admin 
{

	public function action_index()
	{
		$data['posts'] = Model_Post::find('all');
		$this->template->title = "Posts";
		$this->template->content = View::forge('admin/posts/index', $data);

	}

	public function action_view($id = null)
	{
		$data['post'] = Model_Post::find($id);

		$this->template->title = "Post";
		$this->template->content = View::forge('admin/posts/view', $data);

	}

	public function action_create($id = null)
	{
		if (Input::method() == 'POST')
		{
			$val = Model_Post::validate('create');
			
			if ($val->run())
			{
				$post = Model_Post::forge(array(
					'title' => Input::post('title'),
					'slug' => Input::post('slug'),
					'summary' => Input::post('summary'),
					'body' => Input::post('body'),
					'user_id' => Input::post('user_id'),
				));

				if ($post and $post->save())
				{
					Session::set_flash('success', 'Added post #'.$post->id.'.');

					Response::redirect('admin/posts');
				}

				else
				{
					Session::set_flash('error', 'Could not save post.');
				}
			}
			else
			{
				Session::set_flash('error', $val->show_errors());
			}
		}

		$this->template->title = "Posts";
		$this->template->content = View::forge('admin/posts/create');

	}

	public function action_edit($id = null)
	{
		$post = Model_Post::find($id);
		$val = Model_Post::validate('edit');

		if ($val->run())
		{
			$post->title = Input::post('title');
			$post->slug = Input::post('slug');
			$post->summary = Input::post('summary');
			$post->body = Input::post('body');
			$post->user_id = Input::post('user_id');

			if ($post->save())
			{
				Session::set_flash('success', 'Updated post #' . $id);

				Response::redirect('admin/posts');
			}

			else
			{
				Session::set_flash('error', 'Could not update post #' . $id);
			}
		}

		else
		{
			if (Input::method() == 'POST')
			{
				$post->title = $val->validated('title');
				$post->slug = $val->validated('slug');
				$post->summary = $val->validated('summary');
				$post->body = $val->validated('body');
				$post->user_id = $val->validated('user_id');

				Session::set_flash('error', $val->show_errors());
			}
			
			$this->template->set_global('post', $post, false);
		}

		$this->template->title = "Posts";
		$this->template->content = View::forge('admin/posts/edit');

	}

	public function action_delete($id = null)
	{
		if ($post = Model_Post::find($id))
		{
			$post->delete();

			Session::set_flash('success', 'Deleted post #'.$id);
		}

		else
		{
			Session::set_flash('error', 'Could not delete post #'.$id);
		}

		Response::redirect('admin/posts');

	}


}