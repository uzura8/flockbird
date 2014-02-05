<?php if (!is_array($files)) $files = (array)$files; ?>
<?php foreach ($files as $file): ?>
<?php echo Asset::js($file);?>
<?php endforeach; ?>
