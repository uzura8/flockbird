<?php if (!empty($timelines['list'])): ?>
<h3><?php echo term('site.latest', 'timeline.plural'); ?></h3>
<?php echo render('timeline::_parts/list', $timelines); ?>
<?php endif; ?>

<?php if (!empty($news_list['list'])): ?>
<h3><?php echo term('site.latest', 'news.view'); ?></h3>
<?php
$view = View::forge('news::_parts/list', $news_list);
$view->set_safe('html_bodys', $html_bodys);
echo $view->render();
?>
<?php endif; ?>

<?php if (!empty($album_images['list'])): ?>
<h3><?php echo term('site.latest', 'album.image.plural'); ?></h3>
<?php echo render('album::image/_parts/list', $album_images); ?>
<?php 	if (isset($album_images['next_page']) && $album_images['next_page'] > 1): ?>
<?php echo Html::anchor('album/image', icon_label('site.see_more', 'both', false, null, 'fa fa-'), array('class' => 'listMoreBox')); ?>
<?php 	endif; ?>
<?php endif; ?>
