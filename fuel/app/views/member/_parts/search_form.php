<?php
if (!isset($is_simple_search)) $is_simple_search = true;
?>
<?php if (conf('profile.useCacheTable.isEnabled', 'member') && !$is_simple_search): ?>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="headingOne">
			<a role="button" data-toggle="collapse" data-parent="#accordion" href="#detail_search_block" aria-expanded="true" aria-controls="collapseOne">
				<h4 class="panel-title text-center fs12"><?php echo icon('form.search'); ?> <?php echo term('form.search', 'common.condition'); ?></h4>
			</a>
		</div>
		<div id="detail_search_block" class="panel-collapse collapse<?php if (Input::get('form_open')): ?> in<?php endif; ?>" role="tabpanel" aria-labelledby="headingOne">
			<div class="panel-body">
				<?php echo render('member/_parts/form_search_detail', array(
					'val' => $val,
					'inputs' => $inputs,
					'profiles' => $profiles,
				)); ?>
			</div>
		</div>
	</div>
</div>

<?php elseif ($is_simple_search): ?>
<?php echo render('_parts/search_form', array(
	'input_value' => !empty($search_word) ? $search_word : null,
	'input_attr' => array(
		'placeholder' => t('form.search_by', array('label' => term('member.name'))),
		'class' => 'form-control js-keyup',
		'data-btn' => '#btn_search_member',
	),
	'btn_attr' => array(
		'id' => 'btn_search_member',
		'data-list' => '#article_list',
		'data-uri' => 'member/api/list.json',
		'data-history_keys' => json_encode(array('q', 'max_id')),
	),
)); ?>
<?php endif; ?>

