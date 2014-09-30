{{#if next_id}}
<a href="#" data-uri="{{{get_uri}}}" data-get_data="{{conv2objStr 'max_id' next_id 'since_id' since_id}}" data-list="#comment_list_{{{parent.id}}}" data-template="#comment-template" id="listMoreBox_comment_{{{parent.id}}}" class="listMoreBox js-ajax-loadList">もっとみる</a>
{{/if}}
{{#each list}}
<div data-parent_auther_id="{{{../parent.member_id}}}" data-auther_id="{{{member.id}}}" data-hidden_btn="btn_comment_delete_{{{id}}}" data-id="{{{id}}}" id="commentBox_{{{id}}}" class="js-hide-btn commentBox commentBox_{{{id}}}">
	<div class="row member_contents">
		<div class="col-xs-1">
			<a href="{{{member_url member.id}}}"><img src="{{img_url member.file.path member.file.name '30x30xc'}}" alt="{{{member.name}}}" class="img-responsive profile_image"></a>
		</div>
		<div class="col-xs-11">
			<div class="main">
				<b class="fullname"><a href="{{{member_url member.id}}}">{{{member.name}}}</a></b>
				<div>{{{body}}}</div>
			</div>
			<div class="sub_info">
				<small><span data-livestamp="{{{created_at}}}"></span></small>
			</div><!-- sub_info -->
		</div>
	</div><!-- row -->
	<a href="#" data-msg="削除します。よろしいですか？" data-counter="#comment_count_{{{../parent.id}}}" data-uri="{{{../delete_uri}}}" data-parent="commentBox_{{{id}}}" data-id="{{{id}}}" id="btn_comment_delete_{{{id}}}" class="btn btn-default boxBtn btn-xs js-ajax-delete"><i class="glyphicon glyphicon-trash"></i></a>
</div>
{{/each}}
