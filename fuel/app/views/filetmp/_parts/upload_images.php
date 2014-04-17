<?php
$data = array('thumbnail_size' => $thumbnail_size);
if (!empty($model)) $data['model'] = $model;
?>
<?php foreach ($files as $file): ?>
<?php $data['file'] = $file; ?>
<?php echo render('filetmp/_parts/upload_image', $data); ?>
<?php endforeach; ?>
