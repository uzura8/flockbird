<?php echo \Config::get('term.album'); ?> <?php echo Html::anchor('album/'.$album_id, strim($name, Config::get('timeline.articles.trim_width.title_in_body'))); ?> に写真を <?php echo $count; ?> 枚投稿しました。
