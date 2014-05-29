<?php if ($list): ?>
<?php
$file_cate = !empty($file_cate) ? $file_cate : 'nw';
$file_uri_base = conf('upload.types.file.tmp.root_path.raw_dir');
?>
<?php 	if (!empty($title)): ?>
<h4><?php echo $title; ?></h4>
<?php 	endif; ?>
<ul>
<?php 	foreach ($list as $file): ?>
<?php
$file_obj  = $file->file;
$file_uri  = Site_Upload::get_uploaded_file_uri_path($file_obj->path, $file_obj->name, 'raw', 'file');
?>
	<li><?php echo anchor($file_uri, $file->name ?: $file_obj->original_filename); ?></li>
<?php 	endforeach; ?>
</ul>
<?php endif; ?>
