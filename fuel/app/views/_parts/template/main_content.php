<?php if (isset($sub_column)): ?>
<div class="row-fluid">
	<div class="span10" id="main_column">
<?php echo $content; ?>
	</div><!--/span-->
	<div class="span2">
<?php echo $sub_column; ?>
	</div><!--/span-->
</div><!--/row-->

<?php else: ?>
<div id="main">
<?php if (isset($content)) echo $content; ?>
</div>
<?php endif; ?>
