<?php $is_subcolm_positon_left = conf('site.common.main.subColumn.position', 'page') == 'left'; ?>
<?php if (isset($sub_column)): ?>
<div class="row">
	<div class="col-md-9<?php if ($is_subcolm_positon_left): ?> col-md-push-3<?php endif; ?>">
<?php echo $content; ?>
	</div><!--/col-md-9 -->
	<div class="col-md-3<?php if ($is_subcolm_positon_left): ?> col-md-pull-9<?php endif; ?>">
<?php echo $sub_column; ?>
	</div><!--/col-md-3 -->
</div><!--/row-->

<?php else: ?>
<div id="main">
<?php if (isset($content)) echo $content; ?>
</div>
<?php endif; ?>
