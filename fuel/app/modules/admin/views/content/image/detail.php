<?php
$data = array('image_obj' => $site_image);
if ($before_id) $data['before_uri'] = 'admin/content/image/'.$before_id;
if ($after_id) $data['after_uri'] = 'admin/content/image/'.$after_id;
echo render('_parts/image/detail', $data);
?>

<?php $sizes = conf('upload.types.img.types.si.sizes'); ?>
<ul class="media-list mt20">
<?php foreach ($sizes as $key => $size): ?>
<li class="media">
	<?php echo img($site_image->file_name, $key, '#', false, $site_image->name ?: '', false, false, array('class' => 'pull-left'), array('class' => 'media-object')); ?>
	<div class="media-body">
		<h4 class="media-heading"><?php echo $size; ?></h4>
		<?php echo Site_Util::get_media_uri(Site_Upload::get_uploaded_file_path($site_image->file_name, $size, 'img', false, true), true); ?>
	</div>
</li>
<?php endforeach; ?>
</ul>
