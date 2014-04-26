<?php
if (empty($type)) $type = 'warning';
?>
<div class="alert <?php if (!empty($attr)): ?><?php echo $attr; ?><?php else: ?>alert-<?php echo $type; ?><?php endif; ?>">
<?php if (!empty($with_close_btn)): ?><button type="button" class="close" data-dismiss="alert">&times;</button><?php endif; ?>
<?php if (!empty($title)): ?><h4><?php echo $title; ?></h4><?php endif; ?>
<?php if (!empty($body)): ?><?php echo $body; ?><?php endif; ?>
</div>
