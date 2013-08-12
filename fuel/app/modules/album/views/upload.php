<?php echo render('_parts/upload_images', array('album' => $album, 'display_delete_button' => Config::get('album.display_setting.upload.display_delete_button'))); ?>
<?php echo render('_parts/main_link', array('href' => 'album/'.$id, 'text' => Config::get('term.album').'を見る')); ?>
