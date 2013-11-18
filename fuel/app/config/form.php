<?php
/**
 * Part of the Fuel framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */


return array(
	'prep_value' => true,
	'auto_id' => true,
	'auto_id_prefix' => 'form_',
	'form_method' => 'post',
	'form_template' => "\n\t\t{open}\n\t\t\n{fields}\n\t\t\n\t\t{close}\n",
	'fieldset_template' => "\n\t\t{open}\n{fields}\n\t\t{close}\n",
	//'field_template' => "\t\t<div class=\"control-group\">\n\t\t\t{label}{required}\n\t\t\t<div class=\"controls\">{field} {description} {error_msg}</div></div>\n\t\t\n",
	'field_template' => "\t\t<div class=\"form-group\">\n\t\t\t<div class=\"control-label col-sm-2\">{label}</div>\n\t\t\t<div class=\"col-sm-10\">{field} {description} {error_msg}</div></div>\n\t\t\n",
	//'multi_field_template'       => "\t\t<tr>\n\t\t\t<td class=\"{error_class}\">{group_label}{required}</td>\n\t\t\t<td class=\"{error_class}\">{fields}\n\t\t\t\t{field} {label}<br />\n{fields}<span>{description}</span>\t\t\t{error_msg}\n\t\t\t</td>\n\t\t</tr>\n",
	//'multi_field_template' => "\t\t<div class=\"form-group\">\n\t\t\t<div class=\"control-label col-sm-2\">{group_label}</div>\n\t\t\t<div class=\"col-sm-10\">{fields}\n\t\t\t\t{field} {label}</div><div class=\"controls\">{fields}\n\t\t\t</div>\n{description}\t\t{error_msg}</div>\n",
	'multi_field_template' => "\t\t<div class=\"form-group\">\n\t\t\t<div class=\"control-label col-sm-2\">{group_label}</div>\n\t\t\t<div class=\"col-sm-10\">{fields}\n\t\t\t\t<div class=\"radio\">\n\t\t\t\t\t{field} {label}\n\t\t\t\t</div>{fields} {description} {error_msg}\n\t\t\t\t</div>\n\t\t</div>\n",
	'error_template' => '<span class="error_msg">{error_msg}</span>',
	'required_mark' => "<span class=\"required\">*</span>",
	'inline_errors' => true,
	'error_class' => 'validation_error',
);
