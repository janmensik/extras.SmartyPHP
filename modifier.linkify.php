<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty spacify modifier plugin
 *
 * Type:     modifier<br>
 * Name:     linkify<br>
 * Purpose:  v textu udela odkazy klikatelne a pripadne zkrati na $length
 * @link http://weblog.blackwolf.cz/webdesign/prevod-url-na-odkaz-v-php
 * @param string
 * @param int
 * @return string
 */
function smarty_modifier_linkify($string, $length = 9999)
{
	return (preg_replace ('`(http://|ftp://|(www\.))([\w\-]*\.[\w\-\.]*([/?][^\s]*)?)`e', "'<a href=\"'.('\\1'=='www.'?'http://':'\\1').'\\2\\3\">'.((strlen('\\2\\3')>($length+3))?(substr('\\2\\3',0,$length).'&hellip;'):'\\2\\3').'</a>'", $string));
}

/* vim: set expandtab: */

?>
