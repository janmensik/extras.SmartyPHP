<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_czech_num_items($int, $jedna = null, $dveazctyri = null, $petavice = null) {
	switch (substr ((string) $int, -1)) {
		case 1:
			if ((int) $int < 11 || (int) $int > 20)
				return ($jedna);
		
		case 2:
		case 3:
		case 4:
			if ((int) $int < 11 || (int) $int > 20)
				return ($dveazctyri);

   	case 0:
		case 5:
		case 6:
		case 7:
		case 8:
		case 9:
		default:
			return ($petavice);
		} 
	}

/* vim: set expandtab: */

?>
