<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {icq_status} function plugin
 *
 * Type:     function<br>
 * Name:     icq_status<br>
 * Date:     2007-07-18<br>
 * Purpose:  return ICQ status (true = online, false = offline, null = uknown
 * Input:
 *         - icq = ICQ number (ex. "123456")
 *         - assign = boolean, assigns to template var instead of
 *                    printed.
 * Examples:<br>
 * <pre>
 * My status is {icq_status icq="123456"}
 * </pre>
 * @author Jan Mensik <jan@mensik.cz>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return string|bool|null
 */
function smarty_function_icq_status ($params, &$smarty) {
	static $status;
  $cacheFile = './' . $smarty->cache_dir .'/icqstav.txt';
  $cacheDuration = 600; // 10 minut
  $reload = true; // na��st stav z webu?
	$textstatus = array ('online', 'offline', 'unknown');

  clearstatcache(); // smazat vyrovn�vac� pam�t s informacemi o souborech

  if(file_exists($cacheFile) && ((time() - filemtime($cacheFile)) < $cacheDuration)) { 
		// soubor s cache existuje a je je�t� platn�
    $status = file_get_contents($cacheFile);
    // pokud se nepoda�ilo soubor p�e��st nebo je v n�m blbost, na�ti stav z webu
    $reload = ((!$status) || !in_array ($status, $textstatus));  
		}

	// je t�eba na��st stav z webu
	if($reload) { 
    $status = icqStatus($params['icq']); // na�ti stav

    // ulo� stav do cache
    $fp = fopen($cacheFile, 'w');
    fwrite($fp, $status);
    fclose($fp);
		}

	if (isset ($params['assign']))
		$smarty->assign ($params['assign'], $status);
	else
		return $status;
}	

function icqStatus ($icq) {
  $file = fopen('http://status.icq.com/online.gif?icq='.$icq.'&img=5', "r");
  $pom = fread($file, 14);
  fclose($file);
  if(ord($pom{13}) == 64) 
		return('online');
  elseif(ord($pom{13}) == 132) 
		return('offline');
  else 
		return('uknown');
	}
?>