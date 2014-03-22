<h2><?php echo sprintf('基本%s設定', term('profile')); ?></h2>

<h3 class="clearfix">
	<?php echo term('member.name'); ?>設定
	<?php echo Html::anchor('admin/profile/edit_name', '<i class="ls-icon-edit"></i> '.term('form.edit'), array('class' => 'btn btn-default btn-sm pull-right')); ?>
</h3>
<table class="table">
<tr>
	<th>変更</th>
	<th>検索</th>
</tr>
<tr>
	<td><?php echo !empty($site_configs['profile_name_is_disp_config']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_name_is_disp_search']) ? '◯' : '×'; ?></td>
</tr>
</table>

<h3 class="clearfix">
	<?php echo term('member.sex'); ?>設定
	<?php echo Html::anchor('admin/profile/edit_sex', '<i class="ls-icon-edit"></i> '.term('form.edit'), array('class' => 'btn btn-default btn-sm pull-right')); ?>
</h3>
<?php if (empty($site_configs['profile_sex_is_enable'])): ?>
<p>使用しない</p>
<?php else: ?>
<table class="table">
<tr>
	<th>新規登録</th>
	<th>変更</th>
	<th>検索</th>
	<th>必須</th>
	<th>場所</th>
	<th>公開範囲の選択</th>
	<th>公開設定デフォルト値</th>
</tr>
<tr>
	<td><?php echo !empty($site_configs['profile_sex_is_disp_regist']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_sex_is_disp_config']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_sex_is_disp_search']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_sex_is_required']) ? '◯' : '×'; ?></td>
	<td><?php echo Site_Profile::get_display_type_options($site_configs['profile_sex_display_type'], true); ?></td>
	<td><?php echo !empty($site_configs['profile_sex_is_edit_public_flag']) ? '◯' : '×'; ?></td>
	<td><?php echo Site_Form::get_public_flag_options($site_configs['profile_sex_default_public_flag']); ?></td>
</tr>
</table>
<?php endif; ?>

<h3 class="clearfix">
	<?php echo term('member.birthday'); ?>設定
	<?php echo Html::anchor('admin/profile/edit_birthday', '<i class="ls-icon-edit"></i> '.term('form.edit'), array('class' => 'btn btn-default btn-sm pull-right')); ?>
</h3>
<?php if (empty($site_configs['profile_birthday_is_enable'])): ?>
<p>使用しない</p>
<?php else: ?>
<table class="table">
<tr>
	<th rowspan="2">新規登録</th>
	<th rowspan="2">変更</th>
	<th rowspan="2">検索</th>
	<th colspan="3">年代</th>
	<th colspan="4">生年</th>
	<th colspan="4">誕生日</th>
</tr>
<tr>
	<th>表示</th>
	<th>区切り</th>
	<th>形式</th>
	<th>場所</th>
	<th>必須</th>
	<th>公開範囲の選択</th>
	<th>公開設定デフォルト値</th>
	<th>場所</th>
	<th>必須</th>
	<th>公開範囲の選択</th>
	<th>公開設定デフォルト値</th>
</tr>
<tr>
	<td><?php echo !empty($site_configs['profile_birthday_is_disp_regist']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_disp_config']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_disp_search']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_enable_generation_view']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_generation_unit']) ? '5年' : '10年'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_birthyear_view_type']) ? '年齢' : '生年'; ?></td>
	<td><?php echo Site_Profile::get_display_type_options($site_configs['profile_birthday_display_type_birthyear'], true); ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_required_birthyear']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_edit_public_flag_birthyear']) ? '◯' : '×'; ?></td>
	<td><?php echo Site_Form::get_public_flag_options($site_configs['profile_birthday_default_public_flag_birthyear']); ?></td>
	<td><?php echo Site_Profile::get_display_type_options($site_configs['profile_birthday_display_type_birthday'], true); ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_required_birthday']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_edit_public_flag_birthday']) ? '◯' : '×'; ?></td>
	<td><?php echo Site_Form::get_public_flag_options($site_configs['profile_birthday_default_public_flag_birthday']); ?></td>
</tr>
</table>
<?php endif; ?>

<h2 class="clearfix">
	<?php echo sprintf('オプション%s設定', term('profile')); ?>
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
<table class="table" id="jqui-sortable">
<tr>
	<th><i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" title="ドラッグ・アンド・ドロップで並び順を変更できます"></i></th>
	<th colspan="2">操作</th>
	<th>id</th>
<?php foreach ($labels as $label): ?>
	<th class="font-size-small"><?php echo $label; ?></th>
<?php endforeach; ?>
	<th>選択肢</th>
</tr>
<?php foreach ($profiles as $profile): ?>
<tr class="jqui-sortable-item" id="<?php echo $profile->id; ?>">
	<td><i class="glyphicon glyphicon-sort jqui-sortable-handle"></i></td>
	<td><?php echo btn('edit', 'admin/profile/edit/'.$profile->id, '', false, 'xs'); ?></td>
	<td><?php echo btn('delete', '#', 'btn_profile_delete', false, 'xs', 'default', array('data-id' => $profile->id)); ?></td>
	<td><?php echo $profile->id; ?></td>
	<td><?php echo $profile->caption; ?></td>
	<td><?php echo $profile->name; ?></td>
	<td><?php echo Site_Profile::get_display_type_options($profile->display_type, true); ?></td>
	<td><?php echo $profile->is_required ? '◯' : '×'; ?></td>
	<td><?php echo $profile->is_edit_public_flag ? '◯' : '×'; ?></td>
	<td><?php echo Site_Form::get_public_flag_options($profile->default_public_flag); ?></td>
	<td><?php echo $profile->is_unique ? '×' : '◯'; ?></td>
	<td><?php echo Site_Profile::get_form_type_options($profile->form_type); ?></td>
	<td><?php echo $profile->is_disp_regist ? '◯' : '×'; ?></td>
	<td><?php echo $profile->is_disp_config ? '◯' : '×'; ?></td>
	<td><?php echo $profile->is_disp_search ? '◯' : '×'; ?></td>
	<td><?php if (in_array($profile->form_type, Site_Profile::get_form_types_having_profile_options())): ?><?php echo Html::anchor('admin/profile/show_options/'.$profile->id, '一覧'); ?><?php else: ?>-<?php endif; ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<?php echo term('profile'); ?>項目がありません。
<?php endif; ?>
