{{#if list}}
{{#each list}}
<a href="{{site_url detail_page_uri}}" class="simpleList-item{{# unless is_read}} simpleList-item-warning{{/unless}}">
	<img class="pull-left-img profile_image" alt="{{member_from.name}}" src="{{getImgUri member_from.file '50x50xc'}}">
	<div class="clearfix">
		<h4>{{getMessageInfo member_from.name type}}</h4>
		<p>{{strimwidth message.body 50}}</p>
		<small>
			<span data-livestamp="{{{last_sent_at}}}"></span>
			<span class="ml10">{{# if is_read}}<?php echo term('site.AlreadyRead'); ?>{{else}}<?php echo term('site.unread'); ?>{{/if}}</span>
		</small>
	</div>
</a>
{{/each}}
{{#if next_page}}
{{#if is_detail}}
<a href="#" data-uri="message/api/list.json" data-get_data="{{conv2objStr 'page' next_page 'is_detail' 1}}" data-list="#article_list" data-template="#message-template" id="listMoreBox_message_detail" class="listMoreBox js-ajax-loadList"><?php echo term('site.see_more'); ?></a>
{{else}}
<?php echo Html::anchor('member/message', term('site.see_more'), array('class' => 'listMoreBox')); ?>
{{/if}}
{{/if}}
{{else}}
<?php echo term('message.view'); ?>はありません
{{/if}}
