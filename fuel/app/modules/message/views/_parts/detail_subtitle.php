<?php echo render('_parts/member_contents_box', array(
	'member'      => $message->member,
	'id'          => $message->id,
	'model'       => 'message',
	'size'        => 'M',
	'date'        => array(
		'datetime' => $message->sent_at ? $message->sent_at : $message->updated_at,
		'label'    => $message->sent_at ? term('form.send', 'site.datetime') : term('form.updated', 'site.datetime'),
	)
)); ?>

