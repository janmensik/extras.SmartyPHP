<?php
/*
 * Smarty plugin "Thumb"
 * Purpose: creates cached thumbnails
 * Home: http://www.cerdmann.com/thumb/
 * Copyright (C) 2005 Christoph Erdmann
 * 
 * This library is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA 
 * -------------------------------------------------------------
 * Author:   Christoph Erdmann (CE)
 * Internet: http://www.cerdmann.com
 *
 * Author: Benjamin Fleckenstein (BF)
 * Internet: http://www.benjaminfleckenstein.de
 *
 * Author: Marcus Gueldenmeister (MG)
 * Internet: http://www.gueldenmeister.de/marcus/
 *
 * Author: Andreas Bsch (AB)

 * Changelog:
 * 2006-09-24 Added overlay support (CE)
 * 2006-09-24 Added support for showing the hint without autolinking the image (CE)
 * 2006-09-24 Added frame support.(CE)
 * 2005-10-31 Fixed some small bugs (CE)
 * 2005-10-09 Rewrote crop-function (CE)
 * 2005-10-08 Decreased processing time by prescaling linear and cleaned code (CE)
 * 2005-07-13 Set crop=true as standard (CE)
 * 2005-07-12 Added crop parameter. Original code by "djneoform at gmail dot com" (AB)
 * 2005-07-02 Found a stupid mistake. Should be faster now (CE)
 * 2005-06-02 Added file_exists(SOURCE)-trigger (CE)
 * 2005-06-02 Added extrapolate parameter (CE)
 * 2005-06-12 Bugfix alt/title (MG)
 * 2005-06-10 Bugfix (MG)
 * 2005-06-02 Added window parameter (MG)
 * 2005-06-02 Made grey banner configurable, added possibility to keep format in thumbs
			  made cache path changeable (BF & MG)
 * 2004-12-01 New link, hint, quality and type parameter (CE)
 * 2004-12-02 Intergrated UnsharpMask (CE)
 * -------------------------------------------------------------
 */
 
function smarty_function_thumb($params, &$smarty)
	{
	// Start time measurement
	if ($params['dev'])
		{
		if (!function_exists('getmicrotime'))
			{
			function getmicrotime()
				{
				list($usec, $sec) = explode(" ",microtime());
				return ((float)$usec + (float)$sec);
				}
			}
		$time['start'] = getmicrotime();
		}
		
	// Funktion zum Schrfen
	if (!function_exists('UnsharpMask'))
		{
		// Unsharp mask algorithm by Torstein Hnsi 2003 (thoensi_at_netcom_dot_no)
		// Christoph Erdmann: changed it a little, cause i could not reproduce the darker blurred image, now it is up to 15% faster with same results
		function UnsharpMask($img, $amount, $radius, $threshold)
			{
			// Attempt to calibrate the parameters to Photoshop:
			if ($amount > 500) $amount = 500;
			$amount = $amount * 0.016;
			if ($radius > 50) $radius = 50;
			$radius = $radius * 2;
			if ($threshold > 255) $threshold = 255;
	
			$radius = abs(round($radius)); 	// Only integers make sense.
			if ($radius == 0) {	return $img; imagedestroy($img); break;	}
			$w = imagesx($img); $h = imagesy($img);
			$imgCanvas = $img;
			$imgCanvas2 = $img;
			$imgBlur = imagecreatetruecolor($w, $h);
	
			// Gaussian blur matrix:
			//	1	2	1		
			//	2	4	2		
			//	1	2	1		

			// Move copies of the image around one pixel at the time and merge them with weight
			// according to the matrix. The same matrix is simply repeated for higher radii.
			for ($i = 0; $i < $radius; $i++)
				{
				imagecopy	  ($imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1); // up left
				imagecopymerge ($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50); // down right
				imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333); // down left
				imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25); // up right
				imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333); // left
				imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25); // right
				imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20 ); // up
				imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667); // down
				imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50); // center
				}
			$imgCanvas = $imgBlur;	
				
			// Calculate the difference between the blurred pixels and the original
			// and set the pixels
			for ($x = 0; $x < $w; $x++)
				{ // each row
				for ($y = 0; $y < $h; $y++)
					{ // each pixel
					$rgbOrig = ImageColorAt($imgCanvas2, $x, $y);
					$rOrig = (($rgbOrig >> 16) & 0xFF);
					$gOrig = (($rgbOrig >> 8) & 0xFF);
					$bOrig = ($rgbOrig & 0xFF);
					$rgbBlur = ImageColorAt($imgCanvas, $x, $y);
					$rBlur = (($rgbBlur >> 16) & 0xFF);
					$gBlur = (($rgbBlur >> 8) & 0xFF);
					$bBlur = ($rgbBlur & 0xFF);

					// When the masked pixels differ less from the original
					// than the threshold specifies, they are set to their original value.
					$rNew = (abs($rOrig - $rBlur) >= $threshold) ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig)) : $rOrig;
					$gNew = (abs($gOrig - $gBlur) >= $threshold) ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig)) : $gOrig;
					$bNew = (abs($bOrig - $bBlur) >= $threshold) ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig)) : $bOrig;
					
					if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew))
						{
						$pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
						ImageSetPixel($img, $x, $y, $pixCol);
						}
					}
				}
			return $img;
			}
		}

	$_CONFIG['types'] = array('','.gif','.jpg','.png');


	### bergebene Parameter auswerten und verifizieren
	if (empty($params['cache'])) $_CONFIG['cache'] = './cache/';
	else $_CONFIG['cache'] = $params['cache'];
	
	# added by Jan Mensik - support url
	if ($params['url']) {			
		require_once $smarty->_get_plugin_filepath('shared','filemtime_remote');
		
		# cache
		if (!$params['urlcache'])
			$params['urlcache'] = 3600;

		# abych mel korektni priponu
		unset ($back);
		if (eregi ('^.+\.(jpg|gif|png|jpeg)$', $params['url'], $back))
			$pripona = $back[1];
		else
			$pripona = 'url';

		# vytvorim nazev souboru
		$nazevsouboru = $_CONFIG['cache'] . 'remote-image.' . md5($params['url']) . '.'.$pripona;

		$fmt_local = filemtime ($nazevsouboru);

		# mam remote-image a urlcache zatim nevyprsel
		if (file_exists ($nazevsouboru) && $fmt_local > time () - $params['urlcache']) {
			$params['file'] = $nazevsouboru;
			}
		# mam remote-image a vzdaleny soubor neni mladsi nez moje kopie
		elseif (file_exists ($nazevsouboru) && $fmt_local >= smarty_filemtime_remote ($params['url']) && smarty_filemtime_remote ($params['url']) > 0) {
			$params['file'] = $nazevsouboru;
			touch ($params['file']);
			}
		# musim sosnout
		else {
			$defnocache = true;
			# zkusim nacist z url
			$input = @implode ('',(@file ($params['url'])));
			if ($input) {
				# ulozim do cache adresare
				$fp = fopen ($nazevsouboru, 'w');
				fwrite ($fp, $input);
				fclose ($fp);

				# zapisu nazev souboru do $params['file']
				$params['file'] = $nazevsouboru;
				}
			}
		if (!$params['name'])
			$params['name'] = 'remote-cache.' . md5($params['url'].implode('',$params));
		}

	# changed by Jan Mensik: no image = no error report
	if (empty($params['file']) OR !file_exists($params['file'])) {
		# defeault image?
		if ($params['default'] && file_exists($params['default']))
			$params['file'] = $params['default'];
		else
			return;
		}
	//if (empty($params['file'])) { $smarty->_trigger_fatal_error("thumb: parameter 'file' cannot be empty");return; }
	//if (!file_exists($params['file'])) { $smarty->_trigger_fatal_error("thumb: image file does not exist");return; }
	
	if (empty($params['link'])) $params['link'] = true;
	if (empty($params['window'])) $params['window'] = true;
	if (empty($params['hint'])) $params['hint'] = true;
	if (empty($params['extrapolate'])) $params['extrapolate'] = true;
	if (empty($params['dev'])) $params['crop'] = false;
	if (empty($params['crop'])) $params['crop'] = true;
	if (empty($params['width']) AND empty($params['height']) 
		AND empty($params['longside']) AND empty($params['shortside'])) $params['width'] = 100;
	if (empty($params['overlay_position'])) $params['overlay_position'] = 9;

	if (empty ($params['fitin'])) $params['fitin'] = false;
		
	### Info ber Source (SRC) holen
	$temp = getimagesize($params['file']);

	$_SRC['file']		= $params['file'];
	$_SRC['width']		= $temp[0];
	$_SRC['height']		= $temp[1];
	$_SRC['type']		= $temp[2]; // 1=GIF, 2=JPG, 3=PNG, SWF=4
	$_SRC['string']		= $temp[3];
	$_SRC['filename'] 	= basename($params['file']);
	$_SRC['modified'] 	= filemtime($params['file']);

	// Jan Mensik: maximalni velist obrazku, nez dojde RAM - 1000 px * 1000 px = 10 000
	if ($_SRC['width'] * $_SRC['height'] > 1000000)
		return '!';

	// Hash erstellen
	$_SRC['hash'] 		= md5($_SRC['file'].$_SRC['modified'].implode('',$params));


	### Infos ber Destination (DST) errechnen
	if (is_numeric($params['width'])) $_DST['width'] = $params['width'];
	else $_DST['width'] = round($params['height']/($_SRC['height']/$_SRC['width']));

	if (is_numeric($params['height'])) $_DST['height']	= $params['height'];
	else $_DST['height'] = round($params['width']/($_SRC['width']/$_SRC['height']));
	
	// Das Grenverhltnis soll erhalten bleiben egal ob das Bild hoch oder querformatig ist.
	if (is_numeric($params['longside']))
		{
		if ($_SRC['width'] < $_SRC['height']) 
			{
			$_DST['height']	= $params['longside'];
			$_DST['width']	= round($params['longside']/($_SRC['height']/$_SRC['width']));
			}
		else
			{
			$_DST['width']	= $params['longside'];
			$_DST['height']	= round($params['longside']/($_SRC['width']/$_SRC['height']));
			}
		}
	elseif (is_numeric($params['shortside']))
		{
		if ($_SRC['width'] < $_SRC['height']) 
			{
			$_DST['width']	= $params['shortside'];
			$_DST['height']	= round($params['shortside']/($_SRC['width']/$_SRC['height']));
			}
		else
			{
			$_DST['height']	= $params['shortside'];
			$_DST['width']	= round($params['shortside']/($_SRC['height']/$_SRC['width']));
			}
		}
	
	if ($params['fitin'] == 'true') {
		# zjistim si pomery stran
		$width_ratio = $_SRC['width']/$_DST['width'];
		$height_ratio = $_SRC['height']/$_DST['height'];

		# logika rika: vezmu vetsi pomer a vydelim jim puvodni rozmery
		$width_ratio > $height_ratio ? $ratio = $width_ratio : $ratio = $height_ratio;
		
		$_DST['width'] = $_SRC['width']/$ratio;
		$_DST['height'] = $_SRC['height']/$ratio;
		}

	// Soll beschnitten werden? (Standard)
	if($params['crop'] == 'true')
		{
		$width_ratio = $_SRC['width']/$_DST['width'];
		$height_ratio = $_SRC['height']/$_DST['height'];
		
		// Es muss an der Breite beschnitten werden
		if ($width_ratio > $height_ratio)
			{
			$_DST['offset_w'] = round(($_SRC['width']-$_DST['width']*$height_ratio)/2);
			$_SRC['width'] = round($_DST['width']*$height_ratio);
			}
		// es muss an der Hhe beschnitten werden
		elseif ($width_ratio < $height_ratio)
			{
			$_DST['offset_h'] = round(($_SRC['height']-$_DST['height']*$width_ratio)/2);
			$_SRC['height'] = round($_DST['height']*$width_ratio);
			}
		}

	// Wenn das Ursprungsbild kleiner als das Ziel-Bild ist, soll nicht hochskaliert werden und die neu berechneten Werte werden wieder berschrieben
	if ($params['extrapolate'] == 'false' && $_DST['height'] > $_SRC['height'] && $_DST['width'] > $_SRC['width'])
		{
		$_DST['width'] = $_SRC['width'];
		$_DST['height'] = $_SRC['height'];
		}
		
	if (!empty($params['type'])) $_DST['type']	= $params['type'];
	else $_DST['type']	= $_SRC['type'];

	# change by Jan Mensik
	if ($params['name'])
		$_DST['file']	= $_CONFIG['cache'].$params['name'].$_CONFIG['types'][$_DST['type']];
	else
		$_DST['file']		= $_CONFIG['cache'].$_SRC['hash'].$_CONFIG['types'][$_DST['type']];
	
	$_DST['string']	= 'width="'.$_DST['width'].'" height="'.$_DST['height'].'"';

	# change by Jan Mensik
	if ($params['baseimgurl'])
		$_DST['imgurl']	= addslashes ($params['baseimgurl']) . substr ($_DST['file'], 1);
	else
		$_DST['imgurl']		= $_DST['file'];

	// Gibts evtl. einen Rahmen
	if (!empty($params['frame']))
		{
		// schauen obs gltig ist
		$imagesize = getimagesize($params['frame']);
		if ($imagesize[0] != $imagesize[1] OR $imagesize[0]%3 OR !file_exists($params['frame'])) { $smarty->_trigger_fatal_error("thumb: wrong dimensions of 'frame'-image or width and height is not a multiplier of 3"); return; }
		// Blockgre brauche ich schon hier, falls ein gecachtes Bild wiedergegeben werden soll
		$frame_blocksize = $imagesize[0]/3;
		
		$_DST['string'] = 'width="'.($_DST['width']+2*$frame_blocksize).'" height="'.($_DST['height']+2*$frame_blocksize).'"';
		}

	### Rckgabe-Strings erstellen
	// change by Jan Mensik (added 'justimg')
	if ($params['justimg'])
		$_RETURN['img'] = $_DST['imgurl'].($params['vgen'] ? '?v='.time() : '');
	elseif (empty($params['html'])) $_RETURN['img'] = '<img src="'.$_DST['imgurl'].($params['vgen'] ? '?v='.time() : '').'" '.$params['html'].' '.$_DST['string'].' alt="" title="" />';
	else $_RETURN['img'] = '<img src="'.$_DST['imgurl'].($params['vgen'] ? '?v='.time() : '').'" '.$params['html'].' '.$_DST['string'].' />';

	if ($params['link'] == "true")
		{
		if (empty($params['linkurl'])) {
			unset ($temp);
			if ($params['baseimgurl'] && preg_match ('/^\.\/(.*)$/',$_SRC['file'], $temp))
				$params['linkurl'] = $params['baseimgurl'] . substr ($_SRC['file'],1);
			else
				$params['linkurl'] = $_SRC['file'];
			}
		
		# change by Jan Mensik (added 'linkhtml')
		if ($params['window'] == "true") $returner = '<a href="'.$params['linkurl'].'" target="_blank" ' . $params['linkhtml'] . '>'.$_RETURN['img'].'</a>';
		else $returner = '<a href="'.$params['linkurl'].'" ' . $params['linkhtml'] . '>'.$_RETURN['img'].'</a>';
		}
	else
		$returner = $_RETURN['img'];

	# Jan Mensik - pokud je zdrojovy obrazek mladsi, vygeneruji znovu
	if (file_exists($_DST['file']) && filemtime ($_DST['file'])<filemtime ($_SRC['file']))
		$defnocache = true;
	
	### Cache-Datei abfangen
	if (file_exists($_DST['file']) AND !$params['dev'] AND !$defnocache) return $returner;
	
	
	### ansonsten weitermachen
	
	// SRC einlesen
	if ($_SRC['type'] == 1)	$_SRC['image'] = imagecreatefromgif($_SRC['file']);
	if ($_SRC['type'] == 2)	$_SRC['image'] = imagecreatefromjpeg($_SRC['file']);
	if ($_SRC['type'] == 3)	$_SRC['image'] = imagecreatefrompng($_SRC['file']);

	// Wenn das Bild sehr gro ist, zuerst linear auf vierfache Zielgre herunterskalieren und $_SRC berschreiben
	if ($_DST['width']*4 < $_SRC['width'] AND $_DST['height']*4 < $_SRC['height'])
		{
		// Multiplikator der Zielgre
		$_TMP['width'] = round($_DST['width']*4);
		$_TMP['height'] = round($_DST['height']*4);
		
		$_TMP['image'] = imagecreatetruecolor($_TMP['width'], $_TMP['height']);
		imagecopyresized($_TMP['image'], $_SRC['image'], 0, 0, $_DST['offset_w'], $_DST['offset_h'], $_TMP['width'], $_TMP['height'], $_SRC['width'], $_SRC['height']);
		$_SRC['image'] = $_TMP['image'];
		$_SRC['width'] = $_TMP['width'];
		$_SRC['height'] = $_TMP['height'];
		
		// Wenn vorskaliert wird, darf ja nicht nochmal ein bestimmter Bereich ausgeschnitten werden
		$_DST['offset_w'] = 0;
		$_DST['offset_h'] = 0;
		unset($_TMP['image']);
		}

	// DST erstellen
	$_DST['image'] = imagecreatetruecolor($_DST['width'], $_DST['height']);
	imagecopyresampled($_DST['image'], $_SRC['image'], 0, 0, $_DST['offset_w'], $_DST['offset_h'], $_DST['width'], $_DST['height'], $_SRC['width'], $_SRC['height']);
	if ($params['sharpen'] != "false") $_DST['image'] = UnsharpMask($_DST['image'],80,.5,3);

	// Soll eine Lupe eingefgt werden?
	if ($params['hint'] == "true")
		{
		// Soll der weie Balken wirklich hinzugefgt werden?
		if ($params['addgreytohint'] != 'false')
			{
			$trans = imagecolorallocatealpha($_DST['image'], 255, 255, 255, 25);
			imagefilledrectangle($_DST['image'], 0, $_DST['height']-9, $_DST['width'], $_DST['height'], $trans);
			}

		$magnifier = imagecreatefromstring(gzuncompress(base64_decode("eJzrDPBz5+WS4mJgYOD19HAJAtLcIMzBBiRXrilXA1IsxU6eIRxAUMOR0gHkcxZ4RBYD1QiBMOOlu3V/gIISJa4RJc5FqYklmfl5CiGZuakMBoZ6hkZ6RgYGJs77ex2BalRBaoLz00rKE4tSGXwTk4vyc1NTMhMV3DKLUsvzi7KLFXwjFEAa2svWnGdgYPTydHEMqZhTOsE++1CAyNHzm2NZjgau+dAmXlAwoatQmOld3t/NPxlLMvY7sovPzXHf7re05BPzjpQTMkZTPjm1HlHkv6clYWK43Zt16rcDjdZ/3j2cd7qD4/HHH3GaprFrw0QZDHicORXl2JsPsveVTDz//L3N+WpxJ5Hff+10Tjdd2/Vi17vea79Om5w9zzyne9GLnWGrN8atby/ayXPOsu2w4quvVtxNCVVz5nAf3nDpZckBCedpqSc28WTOWnT7rZNXZSlPvFybie9EFc6y3bIMCn3JAoJ+kyyfn9qWq+LZ9Las26Jv482cDRE6Ci0B6gVbo2oj9KabzD8vyMK4ZMqMs2kSvW4chz88SXNzmeGjtj1QZK9M3HHL8L7HITX3t19//VVY8CYDg9Kvy2vDXu+6mGGxNOiltMPsjn/t9eJr0ja/FOdi5TyQ9Lz3fOqstOr99/dnro2vZ1jy76D/vYivPsBoYPB09XNZ55TQBAAJjs5s</body>")));
		imagealphablending($_DST['image'], true);
		imagecopy($_DST['image'], $magnifier, $_DST['width']-15, $_DST['height']-14, 0, 0, 11, 11);
		imagedestroy($magnifier);
		}

	// Soll ein Overlay-Bild hinzugefgt werden
	if (!empty($params['overlay']))
		{
		// "overlay"-Bild laden
		$overlay = imagecreatefrompng($params['overlay']);
		$overlay_size = getimagesize($params['overlay']);

		// Overlay-Bild an die richtige Stelle kopieren
		if ($params['overlay_position'] == '1') imagecopy($_DST['image'], $overlay, 0, 0, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($params['overlay_position'] == '2') imagecopy($_DST['image'], $overlay, $_DST['width']/2-$overlay_size[0]/2, 0, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($params['overlay_position'] == '3') imagecopy($_DST['image'], $overlay, $_DST['width']-$overlay_size[0], 0, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($params['overlay_position'] == '4') imagecopy($_DST['image'], $overlay, 0, $_DST['height']/2-$overlay_size[1]/2, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($params['overlay_position'] == '5') imagecopy($_DST['image'], $overlay, $_DST['width']/2-$overlay_size[0]/2, $_DST['height']/2-$overlay_size[1]/2, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($params['overlay_position'] == '6') imagecopy($_DST['image'], $overlay, $_DST['width']-$overlay_size[0], $_DST['height']/2-$overlay_size[1]/2, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($params['overlay_position'] == '7') imagecopy($_DST['image'], $overlay, 0, $_DST['height']-$overlay_size[1], 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($params['overlay_position'] == '8') imagecopy($_DST['image'], $overlay, $_DST['width']/2-$overlay_size[0]/2, $_DST['height']-$overlay_size[1], 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($params['overlay_position'] == '9') imagecopy($_DST['image'], $overlay, $_DST['width']-$overlay_size[0], $_DST['height']-$overlay_size[1], 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		}
	
	// Berechnungszeit hinzufgen
	if ($params['dev'])
		{
		// Zeit anhalten
		$time['end'] = getmicrotime();
		$time = round($time['end'] - $time['start'],2);
		
		// Farben definieren
		$white_trans = imagecolorallocatealpha($_DST['image'], 255, 255, 255, 25);
		$black = ImageColorAllocate ($_DST['image'], 0, 0, 0);

		// Weier Balken oben
		imagefilledrectangle($_DST['image'], 0, 0, $_DST['width'], 10, $white_trans);

		// Schrift mit Zeitangabe
		imagestring($_DST['image'], 1, 5, 2, 'time: '.$time.'s', $black);
		}

	// Soll ein Rahmen hinzugefgt werden
	if (!empty($params['frame']))
		{
		// "frame"-Bild laden und initialisieren 
		$frame = imagecreatefrompng($params['frame']);
		$frame_blocksize = $imagesize[0]/3;

		// Neues Bild erstellen und bisher erzeugtes Bild hereinkopieren
		$_FRAME['image'] = imagecreatetruecolor($_DST['width']+2*$frame_blocksize, $_DST['height']+2*$frame_blocksize);
		imagecopy($_FRAME['image'], $_DST['image'], $frame_blocksize, $frame_blocksize, 0, 0, $_DST['width'], $_DST['height']);

		// Jetzt die ganzen anderen Rahmen herum zeichnen
		// die Ecken
		imagecopy($_FRAME['image'], $frame, 0, 0, 0, 0, $frame_blocksize, $frame_blocksize); // ecke links oben
		imagecopy($_FRAME['image'], $frame, $_DST['width']+$frame_blocksize, 0, 2*$frame_blocksize, 0, $frame_blocksize, $frame_blocksize); // ecke rechts oben
		imagecopy($_FRAME['image'], $frame, $_DST['width']+$frame_blocksize, $_DST['height']+$frame_blocksize, 2*$frame_blocksize, 2*$frame_blocksize, $frame_blocksize, $frame_blocksize); // ecke rechts unten
		imagecopy($_FRAME['image'], $frame, 0, $_DST['height']+$frame_blocksize, 0, 2*$frame_blocksize, $frame_blocksize, $frame_blocksize); // ecke links unten
		// jetzt die Seiten
		imagecopyresized($_FRAME['image'], $frame, $frame_blocksize, 0, $frame_blocksize, 0, $_DST['width'], $frame_blocksize, $frame_blocksize, $frame_blocksize); // oben
		imagecopyresized($_FRAME['image'], $frame, $_DST['width']+$frame_blocksize, $frame_blocksize, 2*$frame_blocksize, $frame_blocksize, $frame_blocksize, $_DST['height'], $frame_blocksize, $frame_blocksize); // rechts
		imagecopyresized($_FRAME['image'], $frame, $frame_blocksize, $_DST['height']+$frame_blocksize, $frame_blocksize, 2*$frame_blocksize, $_DST['width'], $frame_blocksize, $frame_blocksize, $frame_blocksize); // unten
		imagecopyresized($_FRAME['image'], $frame, 0, $frame_blocksize, 0, $frame_blocksize, $frame_blocksize, $_DST['height'], $frame_blocksize, $frame_blocksize); // links
	
		$_DST['image']	= $_FRAME['image'];
		$_DST['width']	= $_DST['width']+2*$frame_blocksize;
		$_DST['height']	= $_DST['height']+2*$frame_blocksize;
		$_DST['string2']	= 'width="'.$_DST['width'].'" height="'.$_DST['height'].'"';

		$returner = str_replace($_DST['string'], $_DST['string2'], $returner);
		}
	
	// Thumbnail abspeichern
	if ($_DST['type'] == 1)
		{
		imagetruecolortopalette($_DST['image'], false, 256);
		imagegif($_DST['image'], $_DST['file']);
		}
	if ($_DST['type'] == 2)
		{
		Imageinterlace($_DST['image'], 1);
		if (empty($params['quality'])) $params['quality'] = 85;
		imagejpeg($_DST['image'], $_DST['file'],$params['quality']);
		}
	if ($_DST['type'] == 3)
		{
		imagepng($_DST['image'], $_DST['file']);
		}
	
	imagedestroy($_DST['image']);
	imagedestroy($_SRC['image']);

	# Jan Mensik
	# pokud externi obrazek a nechci lokalne ukladat, smazu
	if ($params['url'] && $params['urlnocache'] && $params['file'])
		unlink ($params['file']);
	
	// Und Bild ausgeben
	return $returner;
	
	}
?>