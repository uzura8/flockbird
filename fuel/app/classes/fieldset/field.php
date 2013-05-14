<?php

class Fieldset_Field extends Fuel\Core\Fieldset_Field
{
	protected function template($build_field)
	{
		$form = $this->fieldset()->form();

		$required_mark = $this->get_attribute('required', null) ? $form->get_config('required_mark', null) : null;
		$label = $this->label ? $form->label($this->label.$required_mark, null, array('for' => $this->get_attribute('id', null), 'class' => 'control-label')) : '';
		$error_template = $form->get_config('error_template', '');
		$error_msg = ($form->get_config('inline_errors') && $this->error()) ? str_replace('{error_msg}', $this->error(), $error_template) : '';
		$error_class = $this->error() ? $form->get_config('error_class') : '';

		if (is_array($build_field))
		{
			$label = $this->label ? $form->label($this->label) : '';
			$template = $this->template ?: $form->get_config('multi_field_template', "\t\t<tr>\n\t\t\t<td class=\"{error_class}\">{group_label}{required}</td>\n\t\t\t<td class=\"{error_class}\">{fields}\n\t\t\t\t{field} {label}<br />\n{fields}\t\t\t{error_msg}\n\t\t\t</td>\n\t\t</tr>\n");
			if ($template && preg_match('#\{fields\}(.*)\{fields\}#Dus', $template, $match) > 0)
			{
				$build_fields = '';
				foreach ($build_field as $lbl => $bf)
				{
					$bf_temp = str_replace('{label}', $lbl, $match[1]);
					$bf_temp = str_replace('{required}', $required_mark, $bf_temp);
					$bf_temp = str_replace('{field}', $bf, $bf_temp);
					$build_fields .= $bf_temp;
				}

				$template = str_replace($match[0], '{fields}', $template);
				$template = str_replace(array('{group_label}', '{fields}', '{error_msg}', '{error_class}', '{description}'), array($label, $build_fields, $error_msg, $error_class, $this->description), $template);

				return $template;
			}

			// still here? wasn't a multi field template available, try the normal one with imploded $build_field
			$build_field = implode(' ', $build_field);
		}

		$template = $this->template ?: $form->get_config('field_template', "\t\t<tr>\n\t\t\t<td class=\"{error_class}\">{label}{required}</td>\n\t\t\t<td class=\"{error_class}\">{field} {description} {error_msg}</td>\n\t\t</tr>\n");
		$template = str_replace(array('{label}', '{field}', '{error_msg}', '{error_class}', '{description}'),
			array($label, $build_field, $error_msg, $error_class, $this->description),
			$template);

		return $template;
	}
}
