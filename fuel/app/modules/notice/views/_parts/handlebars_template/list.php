{{#if list}}
{{#each list}}
<a href="{{getNoticeContentUrl notice.foreign_table notice.foreign_id notice.parent_table notice.parent_id}}" class="simpleList-item{{# unless is_read}} simpleList-item-warning{{/unless}}">
	<div>{{getNoticeInfo notice.foreign_table notice.type members members_count}}</div>
	<small>
		<span data-livestamp="{{{sort_datetime}}}"></span>
		<span class="ml10">{{# if is_read}}<?php echo term('site.AlreadyRead'); ?>{{else}}<?php echo term('site.unread'); ?>{{/if}}</span>
	</small>
</a>
{{/each}}
{{#if next_page}}
{{#if is_detail}}
<a href="#" data-uri="notice/api/list.json" data-get_data="{{conv2objStr 'page' next_page 'is_detail' 1}}" data-list="#article_list" data-template="#notices-template" id="listMoreBox_notice_detail" class="listMoreBox js-ajax-loadList"><?php echo term('site.see_more'); ?></a>
{{else}}
<?php echo Html::anchor('member/notice', term('site.see_more'), array('class' => 'listMoreBox')); ?>
{{/if}}
{{/if}}
{{else}}
<?php echo term('notice'); ?>はありません
{{/if}}
