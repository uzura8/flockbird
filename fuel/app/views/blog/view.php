<h2><?php echo $post->title ?></h2>

<p><strong>Posted: </strong><?php echo date('jS F, Y', $post->created_at) ?> (<?php echo Date::time_ago($post->created_at) ?>)</p>

<p><?php echo nl2br($post->body) ?></p>
