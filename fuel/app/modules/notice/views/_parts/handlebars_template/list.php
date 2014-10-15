{{#each list}}
<a href="{{getNoticeContentUrl notice.foreign_table notice.foreign_id}}" class="simpleList-item{{# unless is_read}} simpleList-item-warning{{/unless}}">
	<div>{{getNoticeInfo notice.foreign_table notice.type members members_count}}</div>
	<small><span data-livestamp="{{{sort_datetime}}}"></span><span class="ml10">{{# if is_read}}既読{{else}}未読{{/if}}</span></small>
</a>
{{/each}}
{{#if next_page}}
<a href="#" data-uri="notice/api/list.json" data-get_data="{{conv2objStr 'page' next_page}}" data-list="<?php if (empty($is_detail)): ?>#notice_list_modal<?php else: ?>#article_list<?php endif; ?>" data-template="#notices-template" id="listMoreBox_notice<?php if (!empty($is_detail)): ?>_detail<?php endif; ?>" class="listMoreBox js-ajax-loadList">もっとみる</a>
{{/if}}
