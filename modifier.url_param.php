<?php
/**
 * Smarty plugin
 * by Jan Mensik (jan@mensik.cz)
 */


/**
 * Smarty url_param modifier plugin
 *
 * Type:     modifier<br>
 * Name:     url_param<br>
 * Purpose:  transfer string to nice url string format
 * Description: it transfer string to string where spaces are changeed to char "-" and all but a-z0-9 chars are removed (changed to "-")
 * @param string
 * @return string
 */
function smarty_modifier_url_param($string) {
  # main magic
	$data =  strtolower (eregi_replace ("[^a-z0-9-]", "-", $string));

	# we don't want "text - text" to be "text---text" but "text-text"
	$data = ereg_replace ("-{2,}", "-", $data);

	# we dont want "-" on beginning or end for string
	if (substr ($data, 0, 1) == "-")
		$data = substr ($data, 1);
	if (substr ($data, -1) == "-")
	$data = substr ($data, 0, -1);
	
	return ($data);
	}

?>
