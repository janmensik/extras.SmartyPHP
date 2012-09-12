<?php
/**
 * Smarty plugin
 * by Jan Mensik (jan@mensik.cz)
 */


/**
 * Smarty rounded modifier plugin
 *
 * Type:     modifier<br>
 * Name:     nice_num<br>
 * Purpose:  change integer format
 * Description: it rounds float number if it's like 'XXX.00'
 * @param float|string
 * @return int|float| (string)

 NUTNO DODELAT!!!!!!!!!

 */
function smarty_modifier_rounded($input) {
	if ($input ==  floor ($input))
		return ((int) floor ($input));
	else
		return ($input);
	}

?>
