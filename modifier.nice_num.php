<?php
/**
 * Smarty plugin
 * by Jan Mensik (jan@mensik.cz)
 */


/**
 * Smarty nice_num modifier plugin
 *
 * Type:     modifier<br>
 * Name:     nice_num<br>
 * Purpose:  change integer format
 * Description:it change interger (or string in integer format) to nice format in czech language: delimiter every third number, change . to , trim leading zeros (if good)
 * @param string
 * @return string

 NUTNO DODELAT!!!!!!!!!

 */
function smarty_modifier_nice_num($string, $deliminiter = ' ', $decplaces = 0, $decdelimim = ',') {
  return (str_replace(" ", $deliminiter, number_format ($string, $decplaces , $decdelimim, " ")));	
	}

?>
