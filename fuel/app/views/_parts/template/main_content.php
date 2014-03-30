<?php if (isset($sub_column)): ?>
<div class="row">
	<div class="col-md-9">
<?php echo $content; ?>
	</div><!--/col-md-9 -->
	<div class="col-md-3">
<?php echo $sub_column; ?>
	</div><!--/col-md-3 -->
</div><!--/row-->

<?php else: ?>
<div id="main">
<?php if (isset($content)) echo $content; ?>
</div>
<?php endif; ?>
