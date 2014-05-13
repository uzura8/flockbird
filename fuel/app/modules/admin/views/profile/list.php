<h2><?php echo sprintf('基本%s設定', term('profile')); ?></h2>

<?php $prefix = 'profile.name.'; ?>
<h3 class="clearfix">
	<?php echo term('member.name', 'site.setting'); ?>
	<?php echo Html::anchor('admin/profile/name_setting', '<i class="ls-icon-edit"></i> '.term('form.edit'), array('class' => 'btn btn-default btn-sm pull-right')); ?>
</h3>
<table class="table table-bordered">
<tr>
	<th><?php echo term('form.update'); ?></th>
	<th><?php echo term('form.search'); ?></th>
</tr>
<tr>
	<td><?php echo symbol_bool(conf($prefix.'isDispConfig')); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'isDispSearch')); ?></td>
</tr>
</table>

<?php $prefix = 'profile.sex.'; ?>
<h3 class="clearfix">
	<?php echo term('member.sex.label', 'site.setting'); ?>
	<?php echo Html::anchor('admin/profile/sex_setting', '<i class="ls-icon-edit"></i> '.term('form.edit'), array('class' => 'btn btn-default btn-sm pull-right')); ?>
</h3>
<?php if (!conf($prefix.'isEnable')): ?>
<p><?php echo term('form.unuse'); ?></p>
<?php else: ?>
<table class="table table-bordered">
<tr>
	<th><?php echo term('site.registration'); ?></th>
	<th><?php echo term('form.update'); ?></th>
	<th><?php echo term('form.search'); ?></th>
	<th><?php echo term('form.place'); ?></th>
	<th><?php echo term('form.required'); ?></th>
	<th><?php echo term('public_flag.label', 'form.choice'); ?></th>
	<th><?php echo term('form.publish', 'site.setting', 'form.default', 'form.value'); ?></th>
</tr>
<tr>
<?php $prefix = 'profile.sex.'; ?>
	<td><?php echo symbol_bool(conf($prefix.'isDispRegist')); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'isDispConfig')); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'isDispSearch')); ?></td>
	<td><?php echo Site_Profile::get_display_type_options(conf($prefix.'displayType'), true); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'isRequired')); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'publicFlag.isEdit')); ?></td>
	<td><?php echo Site_Form::get_public_flag_options(conf($prefix.'publicFlag.default')); ?></td>
</tr>
</table>
<?php endif; ?>

<?php $prefix = 'profile.birthday.'; ?>
<h3 class="clearfix">
	<?php echo term('member.birthday', 'site.setting'); ?>
	<?php echo Html::anchor('admin/profile/birthday_setting', '<i class="ls-icon-edit"></i> '.term('form.edit'), array('class' => 'btn btn-default btn-sm pull-right')); ?>
</h3>
<?php if (!conf($prefix.'isEnable')): ?>
<p><?php echo term('form.unuse'); ?></p>
<?php else: ?>
<table class="table table-bordered">
<tr>
	<th rowspan="2"><?php echo term('site.registration'); ?></th>
	<th rowspan="2"><?php echo term('form.update'); ?></th>
	<th rowspan="2"><?php echo term('form.search'); ?></th>
	<th colspan="5"><?php echo term('member.birthyear'); ?></th>
	<th colspan="4"><?php echo term('member.birthday'); ?></th>
<?php if (conf('member.profile.birthday.use_generation_view')): ?>
	<th colspan="4"><?php echo term('member.generation'); ?></th>
<?php endif; ?>
</tr>
<tr>
	<th><?php echo term('form.format'); ?></th>
	<th><?php echo term('form.place'); ?></th>
	<th><?php echo term('form.required'); ?></th>
	<th><?php echo term('public_flag.label', 'form.choice'); ?></th>
	<th><?php echo term('form.publish', 'site.setting', 'form.default', 'form.value'); ?></th>
	<th><?php echo term('form.place'); ?></th>
	<th><?php echo term('form.required'); ?></th>
	<th><?php echo term('public_flag.label', 'form.choice'); ?></th>
	<th><?php echo term('form.publish', 'site.setting', 'form.default', 'form.value'); ?></th>
<?php if (conf('member.profile.birthday.use_generation_view')): ?>
	<th><?php echo term('form.enabled'); ?></th>
	<th>区切り</th>
	<th><?php echo term('public_flag.label', 'form.choice'); ?></th>
	<th><?php echo term('form.publich', 'site.setting', 'form.default', 'form.value'); ?></th>
<?php endif; ?>
</tr>
<tr>
	<td><?php echo symbol_bool(conf($prefix.'isDispRegist')); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'isDispConfig')); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'isDispSearch')); ?></td>
<?php $prefix = 'profile.birthday.birthyear.'; ?>
	<td><?php echo symbol_bool(conf($prefix.'viewType'), term('member.age'), term('member.birthyear')); ?></td>
	<td><?php echo Site_Profile::get_display_type_options(conf($prefix.'displayType'), true); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'isRequired')); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'publicFlag.isEdit')); ?></td>
	<td><?php echo Site_Form::get_public_flag_options(conf($prefix.'publicFlag.default')); ?></td>
<?php $prefix = 'profile.birthday.birthday.'; ?>
	<td><?php echo Site_Profile::get_display_type_options(conf($prefix.'displayType'), true); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'isRequired')); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'publicFlag.isEdit')); ?></td>
	<td><?php echo Site_Form::get_public_flag_options(conf($prefix.'publicFlag.default')); ?></td>
<?php if (conf('member.profile.birthday.use_generation_view')): ?>
<?php $prefix = 'profile.birthday.generationView.'; ?>
	<td><?php echo symbol_bool(conf($prefix.'isEnable')); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'unit'), '5年', '10年'); ?></td>
	<td><?php echo symbol_bool(conf($prefix.'publicFlag.isEdit')); ?></td>
	<td><?php echo Site_Form::get_public_flag_options(conf($prefix.'publicFlag.default')); ?></td>
<?php endif; ?>
</tr>
</table>
<?php endif; ?>

<h2 class="clearfix">
	<?php echo term('site.option', 'profile', 'site.setting'); ?>
	<?php echo Html::anchor('admin/profile/create', '<i class="ls-icon-edit"></i> '.term('form.create'), array('class' => 'btn btn-default btn-sm pull-right')); ?>
</h2>
<?php /*
<p>
<?php echo Form::open(array('action' => 'admin/profile/create', 'method' => 'get', 'class' => 'form-inline', 'role' => 'form')); ?>
<div class="form-group">
	<label class="sr-only" for="preset">プリセットプロフィール項目</label>
<?php echo Form::select('preset', 'none', array(
	'none' => 'プリセットプロフィール項目',
), array('class' => 'form-control')); ?>
</div>
<button type="submit" class="btn btn-default">追加</button>
<?php echo form_close(); ?>
</p>
*/ ?>

<?php if ($profiles): ?>
<table class="table table-bordered" id="jqui-sortable">
<tr>
	<th><i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" title="ドラッグ・アンド・ドロップで並び順を変更できます"></i></th>
	<th colspan="2"><?php echo term('form.operation'); ?></th>
	<th>id</th>
<?php foreach ($labels as $label): ?>
	<th class="font-size-small"><?php echo $label; ?></th>
<?php endforeach; ?>
	<th><?php echo term('form.choices'); ?></th>
</tr>
<?php foreach ($profiles as $profile): ?>
<tr class="jqui-sortable-item" id="<?php echo $profile->id; ?>">
	<td><i class="glyphicon glyphicon-sort jqui-sortable-handle"></i></td>
	<td><?php echo btn('form.edit', 'admin/profile/edit/'.$profile->id, '', false, 'xs'); ?></td>
	<td><?php echo btn('form.delete', '#', 'js-simplePost', false, 'xs', 'default', array('data-id' => $profile->id)); ?></td>
	<td><?php echo $profile->id; ?></td>
	<td><?php echo $profile->caption; ?></td>
	<td><?php echo $profile->name; ?></td>
	<td><?php echo Site_Profile::get_display_type_options($profile->display_type, true); ?></td>
	<td><?php echo symbol_bool($profile->is_required); ?></td>
	<td><?php echo symbol_bool($profile->is_edit_public_flag); ?></td>
	<td><?php echo Site_Form::get_public_flag_options($profile->default_public_flag); ?></td>
	<td><?php echo symbol_bool(!$profile->is_unique); ?></td>
	<td><?php echo Site_Profile::get_form_type_options($profile->form_type); ?></td>
	<td><?php echo symbol_bool($profile->is_disp_regist); ?></td>
	<td><?php echo symbol_bool($profile->is_disp_config); ?></td>
	<td><?php echo symbol_bool($profile->is_disp_search); ?></td>
	<td><?php if (in_array($profile->form_type, Site_Profile::get_form_types_having_profile_options())): ?><?php echo Html::anchor('admin/profile/options/'.$profile->id, term('site.list')); ?><?php else: ?><?php echo symbol('noValue'); ?><?php endif; ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<?php echo term('profile', 'site.item'); ?>がありません。
<?php endif; ?>
