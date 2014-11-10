<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Show Google AdSense advertisements on forum pages');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('de en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.2', 'version');
UnbPluginMeta('unb.devel.20051009', 'version');
UnbPluginMeta('UnbHookAdvertisementConfig', 'config');

if (!UnbPluginEnabled()) return;

function UnbHookAdvertisementConfig(&$data)
{
	global $UNB;

	$ads = array(
		array('VerticalLeft', 'vertical_left', 'vertical left'),
		array('VerticalRight', 'vertical_right', 'vertical right'),
		array('HorizontalPrelogo', 'horizontal_prelogo', 'horizontal prelogo'),
		array('HorizontalTopright', 'horizontal_topright', 'horizontal topright'),
		array('HorizontalPostnavi', 'horizontal_postnavi', 'horizontal postnavi'),
		array('HorizontalPrefootline', 'horizontal_prefootline', 'horizontal prefootline'),
		array('HorizontalPostfootline', 'horizontal_postfootline', 'horizontal postfootline'));

	if ($data['request'] == 'fields')
	{
		$field = array();
		$field['fieldtype'] = 'text';
		$field['fieldname'] = 'Clientid';
		$field['fieldvalue'] = rc('adsense_clientid');
		$field['fieldlabel'] = '_adsense.client id';
		$field['fieldunit'] = '';
		$field['fielddesc'] = '_adsense.client id~';
		$field['fieldsize'] = 20;
		$field['fieldlength'] = 20;
		$data['fields'][] = $field;

		foreach ($ads as $ad)
		{
			$field = array();
			$field['fieldtype'] = 'checkbox';
			$field['fieldname'] = $ad[0] . 'Enable';
			$field['fieldvalue'] = rc('adsense_' . $ad[1] . '_enable');
			$field['fieldlabel'] = '_adsense.' . $ad[2] . '.enable';
			$field['fielddesc'] = '_adsense.' . $ad[2] . '.enable~';
			$data['fields'][] = $field;

			$field = array();
			$field['fieldtype'] = 'checkbox';
			$field['fieldname'] = $ad[0] . 'HideForMembers';
			$field['fieldvalue'] = rc('adsense_' . $ad[1] . '_hideformembers');
			$field['fieldlabel'] = '_adsense.hide for members';
			$field['fieldunit'] = '';
			$field['fielddesc'] = '_adsense.hide for members~';
			$data['fields'][] = $field;

			$field = array();
			$field['fieldtype'] = 'text';
			$field['fieldname'] = $ad[0] . 'Format';
			$field['fieldvalue'] = rc('adsense_' . $ad[1] . '_format');
			$field['fieldlabel'] = '_adsense.format';
			$field['fieldunit'] = '';
			$field['fielddesc'] = '_adsense.format~';
			$field['fieldsize'] = 20;
			$field['fieldlength'] = 20;
			$data['fields'][] = $field;

			$field = array();
			$field['fieldtype'] = 'text';
			$field['fieldname'] = $ad[0] . 'Type';
			$field['fieldvalue'] = rc('adsense_' . $ad[1] . '_type');
			$field['fieldlabel'] = '_adsense.type';
			$field['fieldunit'] = '';
			$field['fielddesc'] = '_adsense.type~';
			$field['fieldsize'] = 10;
			$field['fieldlength'] = 10;
			$data['fields'][] = $field;

			$field = array();
			$field['fieldtype'] = 'text';
			$field['fieldname'] = $ad[0] . 'Channel';
			$field['fieldvalue'] = rc('adsense_' . $ad[1] . '_channel');
			$field['fieldlabel'] = '_adsense.channel';
			$field['fieldunit'] = '';
			$field['fielddesc'] = '_adsense.channel~';
			$field['fieldsize'] = 20;
			$field['fieldlength'] = 20;
			$data['fields'][] = $field;

			if ($ad[0] == 'HorizontalTopright')
			{
				$field = array();
				$field['fieldtype'] = 'text';
				$field['fieldname'] = $ad[0] . 'LogospaceTop';
				$field['fieldvalue'] = rc('adsense_' . $ad[1] . '_logospacetop');
				$field['fieldlabel'] = '_adsense.logospace top';
				$field['fieldunit'] = '_adsense.logospace top.unit';
				$field['fielddesc'] = '_adsense.logospace top~';
				$field['fieldsize'] = 10;
				$field['fieldlength'] = 10;
				$data['fields'][] = $field;

				$field = array();
				$field['fieldtype'] = 'text';
				$field['fieldname'] = $ad[0] . 'LogospaceBottom';
				$field['fieldvalue'] = rc('adsense_' . $ad[1] . '_logospacebottom');
				$field['fieldlabel'] = '_adsense.logospace bottom';
				$field['fieldunit'] = '_adsense.logospace bottom.unit';
				$field['fielddesc'] = '_adsense.logospace bottom~';
				$field['fieldsize'] = 10;
				$field['fieldlength'] = 10;
				$data['fields'][] = $field;
			}
		}
	}

	if ($data['request'] == 'handleform')
	{
		if (isset($_POST['Clientid']))
			$UNB['ConfigFile']['adsense_clientid'] = $_POST['Clientid'];

		foreach ($ads as $ad)
		{
			if (isset($_POST['Set' . $ad[0] . 'Enable']))
				$UNB['ConfigFile']['adsense_' . $ad[1] . '_enable'] = $_POST[$ad[0] . 'Enable'] ? 1 : 0;
			if (isset($_POST['Set' . $ad[0] . 'HideForMembers']))
				$UNB['ConfigFile']['adsense_' . $ad[1] . '_hideformembers'] = $_POST[$ad[0] . 'HideForMembers'] ? 1 : 0;
			if (isset($_POST[$ad[0] . 'Format']))
				$UNB['ConfigFile']['adsense_' . $ad[1] . '_format'] = $_POST[$ad[0] . 'Format'];
			if (isset($_POST[$ad[0] . 'Type']))
				$UNB['ConfigFile']['adsense_' . $ad[1] . '_type'] = $_POST[$ad[0] . 'Type'];
			if (isset($_POST[$ad[0] . 'Channel']))
				$UNB['ConfigFile']['adsense_' . $ad[1] . '_channel'] = $_POST[$ad[0] . 'Channel'];

			if ($ad[0] == 'HorizontalTopright')
			{
				if (isset($_POST[$ad[0] . 'LogospaceTop']))
					$UNB['ConfigFile']['adsense_' . $ad[1] . '_logospacetop'] = $_POST[$ad[0] . 'LogospaceTop'];
				if (isset($_POST[$ad[0] . 'LogospaceBottom']))
					$UNB['ConfigFile']['adsense_' . $ad[1] . '_logospacebottom'] = $_POST[$ad[0] . 'LogospaceBottom'];
			}
		}
		$data['result'] = true;
	}

	return true;
}

// Hook function to generate the ad view
//
function UnbHookAdvertisement(&$data)
{
	global $UNB;

	$id = UnbCurrentHook();

	// Common AdSense parameters
	//
	// Client ID. Insert your client ID here:
	$client = rc('adsense_clientid');

	if (!$client) return true;

	// Format definitions
	//
	// Select the format to be used for each possible slot on the page.
	// Valid dimensions for "text" type are:
	//   "Leaderboard"      728 x  90 (728x90_as)
	//   "Banner"           468 x  60 (468x60_as)
	//   "Button"           125 x 125 (125x125_as)
	//   "Half Banner"      234 x  60 (234x60_as)
	//   "Skyscraper"       120 x 600 (120x600_as)
	//   "Wide Skyscraper"  160 x 600 (160x600_as)
	//   "Small Rectangle"  180 x 150 (180x150_as)
	//   "Vertical Banner"  120 x 240 (120x240_as)
	//   "Medium Rectangle" 300 x 250 (300x250_as)
	//   "Square"           250 x 250 (250x250_as)
	//   "Large Rectangle"  336 x 280 (336x280_as)
	// Valid dimensions for "image" type are:
	//   "Leaderboard"      728 x  90 (728x90_as)
	//   "Banner"           468 x  60 (468x60_as)
	//   "Skyscraper"       120 x 600 (120x600_as)
	//   "Wide Skyscraper"  160 x 600 (160x600_as)
	//   "Medium Rectangle" 300 x 250 (300x250_as)
	// Valid dimensions for links (type = "") are:
	//   120 x 90 (120x90_0ads_al, 120x90_0ads_al_s)
	//   160 x 90 (160x90_0ads_al, 160x90_0ads_al_s)
	//   180 x 90 (180x90_0ads_al, 180x90_0ads_al_s)
	//   200 x 90 (200x90_0ads_al, 200x90_0ads_al_s)
	//   468 x 15 (468x15_0ads_al, 468x15_0ads_al_s)
	//   728 x 15 (728x15_0ads_al, 728x15_0ads_al_s)
	//   (each with 4 or 5 links)

	$places = array(
		'vertical_left',
		'vertical_right',
		'horizontal_prelogo',
		'horizontal_topright',
		'horizontal_postnavi',
		'horizontal_prefootline',
		'horizontal_postfootline');

	$ads = array();
	foreach ($places as $place)
	{
		$ads[$place] = array(
			'enable' => rc('adsense_' . $place . '_enable') && (!$UNB['LoginUserID'] || !rc('adsense_' . $place . '_hideformembers')),
			'format' => rc('adsense_' . $place . '_format'),
			'type' => rc('adsense_' . $place . '_type'),
			'channel' => rc('adsense_' . $place . '_channel'));
	}

	#$url = urlencode(TrailingSlash(rc('home_url')) . UnbLink('@this', $_SERVER['QUERY_STRING'], false, /*sid*/ false));
	$url = urlencode(TrailingSlash(rc('home_url')));
	if ($UNB['ThisPage'] == '@main' && $GLOBALS['toplevel'] > 0)
	{
		$url .= urlencode(UnbLink('@this', 'id=' . $GLOBALS['toplevel'], false, /*sid*/ false));
	}
	elseif ($UNB['ThisPage'] == '@thread' && $GLOBALS['threadid'] > 0 && $GLOBALS['page'] > 0)
	{
		$url .= urlencode(UnbLink('@this', 'id=' . $GLOBALS['threadid'] . '&page=' . $GLOBALS['page'], false, /*sid*/ false));
	}
	elseif ($UNB['ThisPage'] == '@thread' && $GLOBALS['threadid'] > 0)
	{
		$url .= urlencode(UnbLink('@this', 'id=' . $GLOBALS['threadid'], false, /*sid*/ false));
	}
	elseif ($UNB['ThisPage'] == '@register' || $UNB['ThisPage'] == '@stat')
	{
		$url .= urlencode(UnbLink('@this', null, false, /*sid*/ false));
	}
	else
	{
		$url .= urlencode(UnbLink('@main', null, false, /*sid*/ false));
	}

	if ($ads['vertical_left']['enable'])
	{
		$format = $ads['vertical_left']['format'];
		$width = UnbHookAdvertisementFormatWidth($format);
		$height = UnbHookAdvertisementFormatHeight($format);
		$type = $ads['vertical_left']['type'];
		$channel = $ads['vertical_left']['channel'];
		#$margin = '0 25px 0 0; position: fixed';
		$margin = '0 25px 0 0';

		$filename = $UNB['LibraryURL'] . 'plugins/adsense.frm.php?client=' . $client . '&width=' . $width . '&height=' . $height . '&format=' . $format . '&type=' . $type . '&channel=' . $channel . '&url=' . $url;

		if ($id == 'page.prelogo')
		{
			$data .= '<table width="100%" cellspacing="0" cellpadding="0"><tr valign="top"><td width="145">' . endl;
			if ($UNB['ContentTypeXML'])
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><object width="' . $width . '" height="' . $height . '" data="' . t2i($filename) . '" type="text/html"></object></div>' . endl;
			else
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><iframe width="' . $width . '" height="' . $height . '" src="' . t2i($filename) . '" frameborder="0"></iframe></div>' . endl;
			$data .= '</td><td>' . endl;
		}
		if ($id == 'page.postfootline')
		{
			$data .= '</td></tr></table>' . endl;
		}
	}
	if ($ads['vertical_right']['enable'])
	{
		$format = $ads['vertical_right']['format'];
		$width = UnbHookAdvertisementFormatWidth($format);
		$height = UnbHookAdvertisementFormatHeight($format);
		$type = $ads['vertical_right']['type'];
		$channel = $ads['vertical_right']['channel'];
		#$margin = '0 0 0 25px; position: fixed';
		$margin = '0 0 0 25px';

		$filename = $UNB['LibraryURL'] . 'plugins/adsense.frm.php?client=' . $client . '&width=' . $width . '&height=' . $height . '&format=' . $format . '&type=' . $type . '&channel=' . $channel . '&url=' . $url;

		if ($id == 'page.prelogo')
		{
			$data .= '<table width="100%" cellspacing="0" cellpadding="0"><tr valign="top"><td>' . endl;
		}
		if ($id == 'page.postfootline')
		{
			$data .= '</td><td width="145">' . endl;
			if ($UNB['ContentTypeXML'])
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><object width="' . $width . '" height="' . $height . '" data="' . t2i($filename) . '" type="text/html"></object></div>' . endl;
			else
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><iframe width="' . $width . '" height="' . $height . '" src="' . t2i($filename) . '" frameborder="0"></iframe></div>' . endl;
			$data .= '</td></tr></table>' . endl;
		}
	}
	if ($ads['horizontal_prelogo']['enable'])
	{
		$format = $ads['horizontal_prelogo']['format'];
		$width = UnbHookAdvertisementFormatWidth($format);
		$height = UnbHookAdvertisementFormatHeight($format);
		$type = $ads['horizontal_prelogo']['type'];
		$channel = $ads['horizontal_prelogo']['channel'];
		$margin = '0 auto 15px';

		$filename = $UNB['LibraryURL'] . 'plugins/adsense.frm.php?client=' . $client . '&width=' . $width . '&height=' . $height . '&format=' . $format . '&type=' . $type . '&channel=' . $channel . '&url=' . $url;

		if ($id == 'page.prelogo')
		{
			if ($UNB['ContentTypeXML'])
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><object width="' . $width . '" height="' . $height . '" data="' . t2i($filename) . '" type="text/html"></object></div>' . endl;
			else
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><iframe width="' . $width . '" height="' . $height . '" src="' . t2i($filename) . '" frameborder="0"></iframe></div>' . endl;
		}
	}
	if ($ads['horizontal_topright']['enable'])
	{
		$format = $ads['horizontal_topright']['format'];
		$width = UnbHookAdvertisementFormatWidth($format);
		$height = UnbHookAdvertisementFormatHeight($format);
		$type = $ads['horizontal_topright']['type'];
		$channel = $ads['horizontal_topright']['channel'];
		$margin = '0; float:right';
		$logospace_top = rc('adsense_horizontal_topright_logospacetop');
		$logospace_bottom = rc('adsense_horizontal_topright_logospacebottom');

		$filename = $UNB['LibraryURL'] . 'plugins/adsense.frm.php?client=' . $client . '&width=' . $width . '&height=' . $height . '&format=' . $format . '&type=' . $type . '&channel=' . $channel . '&url=' . $url;

		if ($id == 'page.prelogo')
		{
			if ($UNB['ContentTypeXML'])
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><object width="' . $width . '" height="' . $height . '" data="' . t2i($filename) . '" type="text/html"></object></div>' . endl;
			else
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><iframe width="' . $width . '" height="' . $height . '" src="' . t2i($filename) . '" frameborder="0"></iframe></div>' . endl;
			if ($logospace_top)
				$data .= '<div style="width:10px; height:' . $logospace_top . 'px;"></div>' . endl;
		}
		if ($id == 'page.postlogo')
		{
			if ($logospace_bottom)
				$data .= '<div style="width:10px; height:' . $logospace_bottom . 'px;"></div>' . endl;
		}
	}
	if ($ads['horizontal_postnavi']['enable'])
	{
		$format = $ads['horizontal_postnavi']['format'];
		$width = UnbHookAdvertisementFormatWidth($format);
		$height = UnbHookAdvertisementFormatHeight($format);
		$type = $ads['horizontal_postnavi']['type'];
		$channel = $ads['horizontal_postnavi']['channel'];
		$margin = '12px auto';

		$filename = $UNB['LibraryURL'] . 'plugins/adsense.frm.php?client=' . $client . '&width=' . $width . '&height=' . $height . '&format=' . $format . '&type=' . $type . '&channel=' . $channel . '&url=' . $url;

		if ($id == 'page.postnavi')
		{
			if ($UNB['ContentTypeXML'])
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><object width="' . $width . '" height="' . $height . '" data="' . t2i($filename) . '" type="text/html"></object></div>' . endl;
			else
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><iframe width="' . $width . '" height="' . $height . '" src="' . t2i($filename) . '" frameborder="0"></iframe></div>' . endl;
		}
	}
	if ($ads['horizontal_prefootline']['enable'])
	{
		$format = $ads['horizontal_prefootline']['format'];
		$width = UnbHookAdvertisementFormatWidth($format);
		$height = UnbHookAdvertisementFormatHeight($format);
		$type = $ads['horizontal_prefootline']['type'];
		$channel = $ads['horizontal_prefootline']['channel'];
		$margin = '0 auto 12px';

		$filename = $UNB['LibraryURL'] . 'plugins/adsense.frm.php?client=' . $client . '&width=' . $width . '&height=' . $height . '&format=' . $format . '&type=' . $type . '&channel=' . $channel . '&url=' . $url;

		if ($id == 'page.prefootline')
		{
			$data .= '<div style="clear: both;">&nbsp;</div>';   // important for floated CP categories
			if ($UNB['ContentTypeXML'])
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><object width="' . $width . '" height="' . $height . '" data="' . t2i($filename) . '" type="text/html"></object></div>' . endl;
			else
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><iframe width="' . $width . '" height="' . $height . '" src="' . t2i($filename) . '" frameborder="0"></iframe></div>' . endl;
		}
	}
	if ($ads['horizontal_postfootline']['enable'])
	{
		$format = $ads['horizontal_postfootline']['format'];
		$width = UnbHookAdvertisementFormatWidth($format);
		$height = UnbHookAdvertisementFormatHeight($format);
		$type = $ads['horizontal_postfootline']['type'];
		$channel = $ads['horizontal_postfootline']['channel'];
		$margin = '12px auto';

		$filename = $UNB['LibraryURL'] . 'plugins/adsense.frm.php?client=' . $client . '&width=' . $width . '&height=' . $height . '&format=' . $format . '&type=' . $type . '&channel=' . $channel . '&url=' . $url;

		if ($id == 'page.postfootline')
		{
			if ($UNB['ContentTypeXML'])
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><object width="' . $width . '" height="' . $height . '" data="' . t2i($filename) . '" type="text/html"></object></div>' . endl;
			else
				$data .= '<div style="width:' . $width . 'px; height:' . $height . 'px; margin:' . $margin . ';"><iframe width="' . $width . '" height="' . $height . '" src="' . t2i($filename) . '" frameborder="0"></iframe></div>' . endl;
		}
	}

	return true;
}

function UnbHookAdvertisementFormatWidth($format)
{
	if (preg_match('/^(\d+)x/', $format, $m))
		return intval($m[1]);
	return 0;
}

function UnbHookAdvertisementFormatHeight($format)
{
	if (preg_match('/^\d+x(\d+)_/', $format, $m))
		return intval($m[1]);
	return 0;
}

// Register hook functions
UnbRegisterHook('page.prelogo', 'UnbHookAdvertisement');
UnbRegisterHook('page.postlogo', 'UnbHookAdvertisement');
UnbRegisterHook('page.postnavi', 'UnbHookAdvertisement');
UnbRegisterHook('page.prefootline', 'UnbHookAdvertisement');
UnbRegisterHook('page.postfootline', 'UnbHookAdvertisement');

?>