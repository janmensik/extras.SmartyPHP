<?php

function smarty_modifier_utf2ascii($string) {
	$string=iconv('utf-8','windows-1250',$string);
	$win = "ìšèøžýáíéòïúùóöüäÌŠÈØŽÝÁÍÉÒÏÚÙÓÖÜËÄ\x97\x96\x91\x92\x84\x93\x94\xAB\xBB";
	$ascii="escrzyaietnduuoouaESCRZYAIETNDUUOOUEAOUEA\x2D\x2D\x27\x27\x22\x22\x22\x22\x22";
	$string = StrTr($string,$win,$ascii);
	return $string;
}

?>
