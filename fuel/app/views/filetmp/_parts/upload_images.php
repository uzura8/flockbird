<?php
$data = array();
if (!empty($thumbnail_size)) $data['thumbnail_size'] = $thumbnail_size;
if (!empty($model))          $data['model'] = $model;
?>
<?php foreach ($files as $file): ?>
<?php $data['file'] = $file; ?>
<?php echo render(($upload_type == 'img') ? 'filetmp/_parts/upload_image' : 'filetmp/_parts/upload_file', $data); ?>
<?php endforeach; ?>
