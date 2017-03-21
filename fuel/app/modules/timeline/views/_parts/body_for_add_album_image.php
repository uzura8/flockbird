<?php echo __('album_message_post_images_to_album_of_with_count', array(
	'label' => t('album.view').' '.Html::anchor('album/'.$album_id, strim($name, Config::get('timeline.articles.trim_width.title_in_body'))),
	'num' => $count,
)); ?>
