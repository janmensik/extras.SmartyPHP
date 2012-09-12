<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Include the {@link shared.make_timestamp.php} plugin
 */
require_once $smarty->_get_plugin_filepath('shared','make_timestamp');
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
function smarty_modifier_czday($unix_timestamp, $format = 1) {
	$czdny[1] = array (0=>'neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota');
	$czdny[2] = array (0=>'neděli', 'pondělí', 'úterý', 'středu', 'čtvrtek', 'pátek', 'sobotu');

	$dny = $czdny[$format];

	return $dny[date('w', $unix_timestamp)];
}

?>
