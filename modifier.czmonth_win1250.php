<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Include the {@link shared.make_timestamp.php} plugin
 */
//require_once $smarty->_get_plugin_filepath('shared','make_timestamp');
/**
 * Smarty date_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     date_format<br>
 * Purpose:  format datestamps via strftime<br>
 * Input:<br>
 *         - string: input date string
 *         - format: strftime format for output
 *         - default_date: default date if $string is empty
 * @link http://smarty.php.net/manual/en/language.modifier.date.format.php
 *          date_format (Smarty online manual)
 * @param string
 * @param string
 * @param string
 * @return string|void
 * @uses smarty_make_timestamp()
 */
function smarty_modifier_czmonth_win1250($string, $format = 1, $default_date=null)
{
		$czmesice[1] = array (1=>'leden', 'únor', 'bøezen', 'duben', 'kvìten', 'èerven', 'èervenec', 'srpen', 'záøí', 'øíjen', 'listopad', 'prosinec');
		$czmesice[2] = array (1=>'ledna', 'února', 'bøezna', 'dubna', 'kvìtna', 'èervna', 'èervence', 'srpna', 'záøí', 'øíjna', 'listopadu', 'prosince');

		$mesice = $czmesice[$format];

    if (0 < (int) $string && (int) $string < 13)
			return $mesice[(int) $string];
		elseif($string != '')
			return $mesice[date('n', $string)];
		elseif (isset($default_date) && $default_date != '')
			return $mesice[date('n', $default_date)];
		else
			return;
}

/* vim: set expandtab: */

?>
