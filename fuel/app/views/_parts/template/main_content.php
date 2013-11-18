<?php if (isset($sub_column)): ?>
<div class="row">
	<div class="col-md-10" id="main_column">
<?php echo $content; ?>
	</div><!--/col-md-10 -->
	<div class="col-md-2">
<?php echo $sub_column; ?>
	</div><!--/col-md-2 -->
</div><!--/row-->

<?php else: ?>
<div id="main">
<?php if (isset($content)) echo $content; ?>
</div>
<?php endif; ?>
