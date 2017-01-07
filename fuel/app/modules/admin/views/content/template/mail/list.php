<?php if (!$list): ?>
<?php echo term('site.template'); ?>がありません。
<?php else: ?>

<?php foreach ($list as $module => $templates): ?>
<?php 	if ($templates): ?>
<h3><?php echo ($module == 'admin') ? term('admin.view', 'page.view') : term('site.view'); ?></h3>
<table class="table table-hover table-responsive">
<tr>
	<th><?php echo term('site.item'); ?></th>
	<th class="small"><?php echo term('form.edit'); ?></th>
</tr>
<?php 		foreach ($templates as $key => $config): ?>
<?php 			if ($key == 'common_variables') continue; ?>
<tr>
	<td><?php echo $config['view']; ?></td>
	<td>
<?php if (is_enabled_i18n()): ?>
<?php
$menus = array();
foreach (conf('lang.options', 'i18n') as $lang => $label)
{
	$menus[] = array('label' => $label, 'href' => sprintf('admin/content/template/mail/edit/%s/%s/%s', $module, $key, $lang));
}
echo btn_dropdown('form.edit', $menus, false, 'xs', null, true, array('class' => array('dropdown', 'boxBtn')));
?>
<?php else: ?>
<?php echo btn('form.edit', sprintf('admin/content/template/mail/edit/%s/%s', $module, $key), '', false, 'xs'); ?>
<?php endif; ?>
	</td>
</tr>
<?php 		endforeach; ?>
</table>
<?php 	endif; ?>
<?php endforeach; ?>
<?php endif; ?>
