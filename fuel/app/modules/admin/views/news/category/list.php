<table class="table" id="jqui-sortable">
<tr>
	<th class="small"><i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" title="ドラッグ・アンド・ドロップで並び順を変更できます"></i></th>
	<th class="small">削除</th>
	<th class="small">ID</th>
	<th>項目名</th>
</tr>
<?php foreach ($news_categories as $news_category): ?>
<?php echo render('news/category/_parts/table_row', array('news_category' => $news_category)); ?>
<?php endforeach; ?>
</table>

<div class="well">
<?php echo Form::open(array('class' => 'form-inline')); ?>
<div class="form-group">
	<?php echo Form::input('label', '', array('id' => 'input_label', 'class' => 'form-control input-xlarge')); ?>
</div>
<div class="form-group">
	<?php echo Form::button('button', icon_label('plus', 'form.do_add'), array('class' => 'btn btn-default', 'id' => 'btn_create')); ?>
</div>
<?php echo Form::close(); ?>
</div><!-- well -->
