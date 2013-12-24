<?php if ($count): ?>
<?php echo Html::anchor($uri, '<span class="glyphicon glyphicon-picture"></span> '.$count.' 枚'); ?>
<?php else: ?>
<span class="glyphicon glyphicon-picture"></span> 0 枚
<?php endif; ?>
