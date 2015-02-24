<?php
$data = array();
if (!empty($thumbnail_size)) $data['thumbnail_size'] = $thumbnail_size;
if (!empty($model)) $data['model'] = $model;
if (!empty($insert_target)) $data['insert_target'] = $insert_target;
?>
<?php foreach ($files as $file): ?>
<?php $data['file'] = $file; ?>
<?php echo render(($upload_type == 'img') ? 'filetmp/_parts/upload_image' : 'filetmp/_parts/upload_file', $data); ?>
<?php endforeach; ?>
