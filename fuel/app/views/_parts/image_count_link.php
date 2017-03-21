<?php if ($count): ?>
<?php echo Html::anchor($uri, '<span class="glyphicon glyphicon-picture"></span> '.str_unit($count, 'photo')); ?>
<?php else: ?>
<span class="glyphicon glyphicon-picture"></span> <?php echo str_unit(0, 'photo'); ?>
<?php endif; ?>
