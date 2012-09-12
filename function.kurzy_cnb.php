<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {kurzy_cnb} function plugin
 *
 
 */
function smarty_function_kurzy_cnb ($params, &$smarty) {
	static $data;
  $cacheFile = './' . $smarty->cache_dir . '/kurzy_cnb.txt';
  $cacheDuration = 3600; # 1 hodina
  $reload = true; # načíst stav z webu?

  clearstatcache(); # smazat vyrovnávací pamět s informacemi o souborech

  if(file_exists($cacheFile) && ((time() - filemtime($cacheFile)) < $cacheDuration)) { 
		# soubor s cache existuje a je ještě platný
    $data = file_get_contents($cacheFile);
		if ($data)
			$data = unserialize ($data);
    # pokud se nepodařilo soubor přečíst nebo je v něm blbost, načti stav z webu
    if (!is_array ($data))
			$reload = true;
		}

	# je třeba načíst stav z webu
	if($reload) { 
    $data = kurzy_cnb_get (); # načti stav

    # ulož stav do cache
    $fp = fopen($cacheFile, 'w');
    fwrite($fp, serialize ($data));
    fclose($fp);
		}
	if (isset ($params['assign']))
		$smarty->assign ($params['assign'], $data);
	else
		return $data;
}	

function kurzy_cnb_get () {
	$fp = @fopen ('http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt', 'r');
	if ($fp) {
		while (!feof ($fp)) {
			$radka = fgets ($fp);
			if (preg_match ('#([a-ž ]+)\|([a-ž ]+)\|([0-9]+)\|([a-ž]{3})\|([0-9,]+)#i', $radka, $kurz_data)) {
				$kurzy[strtolower ($kurz_data[4])]['zeme'] = $kurz_data[1];
				$kurzy[strtolower ($kurz_data[4])]['mena'] = $kurz_data[2];
				$kurzy[strtolower ($kurz_data[4])]['mnozstvi'] = $kurz_data[3];
				$kurzy[strtolower ($kurz_data[4])]['kod'] = $kurz_data[4];
				$kurzy[strtolower ($kurz_data[4])]['kurz'] = (float) strtr ($kurz_data[5], ',', '.');
				}
			}
		fclose ($fp);
		}
	return ($kurzy);
	}
?>