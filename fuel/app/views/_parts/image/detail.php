<div class="img_box">
	<?php echo (!empty($before_uri)) ? Html::anchor($before_uri, '<span class="glyphicon glyphicon-backward"></span><br>'.term('site.backward'), array('class' => 'btn btn-default btn-xs backward')) : ''; ?>
	<?php echo img($image_obj->get_image(), 'L', '', true, $image_obj->name ?: '', false, true, !empty($anchor_attr) ? $anchor_attr : array()); ?>
	<?php echo (!empty($after_uri)) ? Html::anchor($after_uri, '<span class="glyphicon glyphicon-forward"></span><br>'.term('site.forward'), array('class' => 'btn btn-default btn-xs forward')) : ''; ?>
</div>
