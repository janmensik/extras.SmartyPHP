<?php
/**
 * Smarty {image_info} function plugin
 *
 * Type:     function<br>
 * Name:     image_info<br>
 * Purpose:  print out a counter value
 * Author: Jan Mensik (mensik@webworks.cz)
 * @param array parameters (image, assign, show, get)
 * @param Smarty
 * @return string|null
 */
function smarty_function_image_info($params, &$smarty) {
	static $images = array();

	if (!is_array ($params))
		return (null);
	if (!$params['image'])
		return (null);

	# bud nactu z cache nebo ze souboru a ulozim do cache
	if (isset ($images[$params['image']]))
		$data = $images[$params['image']];
	else {
		$rawdata =  getimagesize ($params['image']);
		if (!$rawdata)
			return (null);

		# prepsani dat na rozmumne
		$data['width'] = $rawdata[0];
		$data['height'] = $rawdata[1];

		$types = array (1 => 'GIF', 2 => 'JPG', 3 => 'PNG', 4 => 'SWF', 5 => 'PSD', 6 => 'BMP', 7 => 'TIFF', 8 => 'TIFF', 9 => 'JPC', 10 => 'JP2', 11 => 'JPX', 12 => 'JB2', 13 => 'SWC', 14 => 'IFF', 15 => 'WBMP', 16 => 'XBM');

		$data['type'] = $types[$rawdata[2]];
		$data['html'] = $rawdata[3];

		# ulozeni do cache
		$images[$params['image']] = $data;
		}
	
	# co chci vratit?
	switch ($params['get']) {
		case 'width':
		case 'height':
		case 'type':
		case 'html':
			$output = $data[$params['get']];
			break;	
		default:
			$output = $data;
		}

	if (!empty($params['assign']))
        $smarty->assign($params['assign'], $output);

	if ($params['show'] || empty($params['assign']))
		return ($output);
}

?>
