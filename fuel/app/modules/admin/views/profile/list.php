<h2>生年月日設定</h2>
<p><?php echo Html::anchor('admin/profile/edit_birthday', '<i class="ls-icon-edit"></i> '.term('form.edit'), array('class' => 'btn btn-default')); ?></p>
<?php if (empty($site_configs['profile_birthday_is_enable'])): ?>
<p>使用しない</p>
<?php else: ?>
<table class="table">
<tr>
	<th>新規登録</th>
	<th>プロフィール変更</th>
	<th>メンバー検索</th>
	<th>年代表示</th>
	<th>年代区切り</th>
	<th>表示</th>
	<th>場所(生年)</th>
	<th>公開範囲の選択(生年)</th>
	<th>公開設定デフォルト値(生年)</th>
	<th>場所(誕生日)</th>
	<th>公開範囲の選択(誕生日)</th>
	<th>公開設定デフォルト値(誕生日)</th>
</tr>
<tr>
	<td><?php echo !empty($site_configs['profile_birthday_is_disp_regist']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_disp_config']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_disp_search']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_enable_generation_view']) ? '◯' : '×'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_generation_unit']) ? '5年' : '10年'; ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_birthyear_view_type']) ? '年齢' : '生年'; ?></td>
	<td><?php echo Site_Profile::get_display_type_options($site_configs['profile_birthday_display_type_birthyear'], true); ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_edit_public_flag_birthyear']) ? '◯' : '×'; ?></td>
	<td><?php echo Site_Form::get_public_flag_options($site_configs['profile_birthday_default_public_flag_birthyear']); ?></td>
	<td><?php echo Site_Profile::get_display_type_options($site_configs['profile_birthday_display_type_birthday'], true); ?></td>
	<td><?php echo !empty($site_configs['profile_birthday_is_edit_public_flag_birthday']) ? '◯' : '×'; ?></td>
	<td><?php echo Site_Form::get_public_flag_options($site_configs['profile_birthday_default_public_flag_birthday']); ?></td>
</tr>
</table>
<?php endif; ?>

<h2><?php echo sprintf('通常%s設定', term('profile')); ?></h2>
<p><?php echo Html::anchor('admin/profile/create', '<i class="ls-icon-edit"></i> 新規作成', array('class' => 'btn btn-default')); ?></p>
<p>
<?php echo Form::open(array('action' => 'admin/profile/create', 'method' => 'get', 'class' => 'form-inline', 'role' => 'form')); ?>
<div class="form-group">
	<label class="sr-only" for="preset">プリセットプロフィール項目</label>
<?php echo Form::select('preset', 'none', array(
	'none' => 'プリセットプロフィール項目',
	'preset_sex' => '性別'
), array('class' => 'form-control')); ?>
</div>
<button type="submit" class="btn btn-default">追加</button>
<?php echo form_close(); ?>
</p>

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
