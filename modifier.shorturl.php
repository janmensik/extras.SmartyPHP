<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


function smarty_modifier_shorturl($string, $lenght = 20)
{
		# jsme v pohode
		if (strlen ($string) <= $lenght)
			return ($string);
		
		# odriznu GET parametry
		if (strpos ($string, '?'))
			$string = substr ($string, 0, strpos ($string, '?')) . '...';

		if (strlen ($string) <= $lenght)
			return ($string);
		
		# odriznu http://, ftp://, mailto
		unset ($replace);
		eregi ('^(([a-z]+://)|(mailto:))(.*)', $string, $replace);
		if ($replace)
			$string = $replace[4];
		if (strlen ($string) <= $lenght)
			return ($string);

		# odriznu www.
		unset ($replace);
		eregi ('^www\.(.*)', $string, $replace);
		if ($replace)
			$string = $replace[1];
		if (strlen ($string) <= $lenght)
			return ($string);

		// odriznu XXX zbyvajicich znaku zprava doleva od posledniho k domene
		unset ($replace);
		eregi ('^[^/]+(.+)[^/]+$', $string, $replace);
		if ($replace)
			$string = $replace[1];
		if (strlen ($string) <= $lenght)
			return ($string);
		
		return ($string);
}

/* vim: set expandtab: */

?>
