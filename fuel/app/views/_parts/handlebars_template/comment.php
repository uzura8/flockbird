{{#if next_id}}
<a href="#" data-uri="{{get_uri}}" data-get_data="{{conv2objStr 'max_id' next_id 'since_id' since_id 'image_size' image_size.key}}" data-list="#comment_list_{{parent.id}}" data-template="#comment-template" id="listMoreBox_comment_{{parent.id}}" class="listMoreBox js-ajax-loadList">もっとみる</a>
{{/if}}
{{#each list}}
<div data-parent_auther_id="{{../parent.member_id}}" data-auther_id="{{member.id}}" data-id="{{id}}" id="commentBox_{{id}}" class="js-hide-btn-simple commentBox commentBox_{{id}}">
	<div class="row member_contents">
		<div class="col-xs-1">
{{#if member}}
			<a href="{{member_url member.id}}"><img src="{{img_url member.file ../../image_size.value}}" alt="{{member_screen_name member.name}}" class="img-responsive profile_image"></a>
{{else}}
			<img src="{{img_url member.file ../../image_size.value}}" alt="{{member_screen_name member.name}}" class="img-responsive profile_image">
{{/if}}
		</div>
		<div class="col-xs-11">
			<div class="main">
{{#if member}}
				<b class="fullname"><a href="{{member_url member.id}}">{{member_screen_name member.name}}</a></b>
{{else}}
				<b class="fullname">{{member_screen_name member.name}}</b>
{{/if}}
				<div>{{{body}}}</div>
			</div>
			<div class="sub_info">
				<small class="ml10"><span data-livestamp="{{created_at}}"></span></small>
<?php if (conf('mention.isEnabled', 'notice') && Auth::check()): ?>
{{#if member}}
				<small class="ml10">
					<a href="#" data-parent_id="{{../../parent.id}}" data-input="#textarea_comment_{{../../parent.id}}" data-hide="#link_show_comment_form_{{../../parent.id}}" data-open="#commentPostBox_{{../../parent.id}}" data-text="@{{member.name}}" id="link_reply_{{id}}" class="js-insert_text"><i class="fa fa-reply"></i><span class="hidden-xs-inline"> <?php echo term('form.do_reply'); ?></span></a>
				</small>
{{/if}}
<?php endif; ?>
<?php if (conf('like.isEnabled')): ?>
				<small class="ml10">
					<a href="#" data-uri="{{get_like_members_uri}}" data-is_list="1" data-target="#modal_like_member"
						 id="link_like_count_{{comment_table}}_{{id}}" class="js-modal">
							<i class="glyphicon glyphicon-thumbs-up"></i>
							<span data-id="{{id}}" id="like_count_{{comment_table}}_{{id}}" class="like_count unset_like_count">{{like_count}}</span>
					</a>
				</small>
<?php 	if (Auth::check()): ?>
				<small class="ml3"><a href="#" data-count="#like_count_{{comment_table}}_{{id}}" data-uri="{{post_like_uri}}" data-id="{{id}}" id="link_like_{{id}}" class="js-like">{{#if is_liked}}{{getTerm 'undo_like'}}{{else}}{{getTerm 'do_like'}}{{/if}}</a></small>
<?php 	endif; ?>
<?php endif; ?>
			</div><!-- sub_info -->
		</div>
	</div><!-- row -->
	<a href="#" data-msg="削除します。よろしいですか？" data-counter="#comment_count_{{../parent.id}}" data-uri="{{../delete_uri}}" data-parent="#commentBox_{{id}}" data-id="{{id}}" id="btn_comment_delete_{{id}}" class="btn btn-default boxBtn btn-xs js-ajax-delete hidden_btn"><i class="glyphicon glyphicon-trash"></i></a>
</div>
{{/each}}
