<?php
if (empty($thumbnail_size)) $thumbnail_size = 'M';
switch ($thumbnail_size)
{
	case 'S':
		$box_class_attr = 'col-sm-4 col-md-3';
		$is_display_original_name = false;
		$is_display_textarea = false;
		$is_subinfo_pull_right = true;
		break;
	case 'M':
	default:
		$box_class_attr = 'col-sm-6 col-md-4';
		$is_display_original_name = true;
		$is_display_textarea = true;
		$is_subinfo_pull_right = false;
		break;
}
?>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
	<div class="<?php echo $box_class_attr; ?> template-upload fade">
		<div class="thumbnail">
			<span class="preview thumbnail"></span>
			<div class="caption clearfix">
<?php if ($is_display_original_name): ?>
				<h5>{%=file.name%}</h5>
<?php endif; ?>
				<strong class="error text-danger"></strong>
				<p class="subinfo<?php if ($is_subinfo_pull_right): ?> pull-right<?php endif; ?>">
					<span class="size">Processing...</span>
					{% if (!i) { %}
						<button type="reset" class="btn btn-xs btn-warning cancel">
								<i class="glyphicon glyphicon-ban-circle"></i>
								<span>Cancel</span>
						</button>
					{% } %}
				</p>
			</div><!-- caption -->
			<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
				<div class="progress-bar progress-bar-success" style="width:0%;"></div>
			</div>

		</div><!-- thumbnail -->
	</div><!-- template-upload -->
{% } %}
</script>

<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
	<div class="<?php echo $box_class_attr; ?> template-download fade" id="{%=file.is_tmp?'image_tmp':'image'%}_{%=file.id%}">
		<div class="thumbnail">
			<span class="preview thumbnail">
				{% if (file.thumbnailUrl) { %}
					<a href="{%=file.url%}" title="{%=file.original_name%}" download="{%=file.name%}" data-gallery>
						<img src="{%=file.thumbnailUrl%}" alt="{%=file.original_name%}">
					</a>
				{% } %}
			</span>
			<div class="caption clearfix">
<?php if ($is_display_original_name): ?>
				<h5>
					{% if (file.url) { %}
						<a href="{%=file.url%}" title="{%=file.original_name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>
							{%=file.original_name%}
						</a>
					{% } else { %}
						<span>{%=file.original_name%}</span>
					{% } %}
				</h5>
<?php endif; ?>
				{% if (file.error) { %}
					<strong class="error text-danger">Error: {%=file.error%}</strong>
				{% } %}
				<p class="subinfo<?php if ($is_subinfo_pull_right): ?> pull-right<?php endif; ?>">
					<span class="size">{%=o.formatFileSize(file.size)%}</span>
					<button class="btn btn-xs btn-default delete_file{%=file.is_tmp?'_tmp':''%}"
						data-id="{%=file.id%}" data-file_type="img" data-type="image{%=file.is_tmp?'_tmp':''%}"
						{% if (!file.is_tmp) { %}<?php if (!empty($model)): ?>data-model="<?php echo $model; ?>"<?php endif; ?>{% } %}
						{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
							<i class="glyphicon glyphicon-trash"></i>
					</button>
				</p>
<?php if ($is_display_textarea): ?>
				<p>
					<textarea name="image{%=file.is_tmp?'_tmp':''%}_description[{%=file.id%}]"
						id="image{%=file.is_tmp?'_tmp':''%}_description_{%=file.id%}"
						class="form-control"
						placeholder="<?php echo t('common.delimitter.of', array('subject' => t('common.description'), 'object' => t('site.picture'))); ?>"
						rows="2"></textarea>
				</p>
<?php endif; ?>
<?php if (!empty($insert_target)): ?>
				{% if (file.accept_sizes) { %}
					<p><select class="form-control" id="select_size_{%=file.id%}">
						{% for (var size_key in file.accept_sizes) { %}
							<option value="{%=file.accept_sizes[size_key]%}">{%=file.accept_sizes[size_key]%}</option>
						{% } %}
					</select></p>
					<button name="button" data-file_name_prefix="{%=file.name_prefix%}" data-file_name="{%=file.name%}"
						data-body="<?php echo $insert_target; ?>"
						data-id="{%=file.id%}" id="img_insert_btn_{%=file.id%}"
						class="btn btn-default btn-sm js-insert_img">
						<i class="glyphicon glyphicon-plus"></i> <?php echo t('form.insert_picture'); ?>
					</button>
				{% } %}
<?php endif; ?>
			</div><!-- caption -->
			<input type="hidden"
				name="image{%=file.is_tmp?'_tmp':''%}[{%=file.id%}]"
				value="{%=file.name_prefix%}{%=file.name%}"
				id="form_image{%=file.is_tmp?'_tmp':''%}[{%=file.id%}]"
				class="image{%=file.is_tmp?'_tmp':''%}">
		</div><!-- thumbnail -->
	</div><!-- template-download -->
{% } %}
</script>
