<script type="text/x-handlebars-template" id="link_count_and_execute-template">
<small class="mr3">
	<i class="glyphicon glyphicon-comment"></i>
	<span data-id="{{id}}" id="comment_count_{{id}}" class="comment_count">{{comment.count}}</span>
</small>
<!-- Modal -->
<small>
	<a href="#" data-is_list="1" data-uri="{{like.get_uri}}" data-target="#modal_like_count_{{id}}"
		id="link_like_count_{{id}}" class="js-modal link_like_count">
			<i class="glyphicon glyphicon-thumbs-up"></i>
			<span data-id="{{id}}" id="like_count_{{id}}" class="like_count unset_like_count">{{like.count}}</span>
	</a>
</small>

<!-- share button -->
<?php if (get_uid()): ?>
<small class="mr10">
	<a href="#" data-count="#like_count_{{id}}" data-uri="{{like.post_uri}}" data-id="{{id}}" id="link_like_{{id}}"
		class="js-like link_like mr3">{{#if like.is_executed}}<?php echo term('form.undo_like'); ?>{{else}}<?php echo term('form.do_like'); ?>{{/if}}</a>
</small>
<?php endif; ?>
</script>

