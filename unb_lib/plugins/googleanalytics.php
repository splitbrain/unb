<?php
/*
Name:	Google Analytics UNB Plugin
Purpose: 	This plugin allows you to easily insert Google Analytics javascript code into your UNB forum.
		See http://www.google.com/analytics for more details on why you would want to do this.
Version:	1.0
Author:	Michael Cohen <saxtusgr@gmail.com>	
*/
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Google Analytics script inserter');
UnbPluginMeta('Michael Cohen <saxtusgr@gmail.com>', 'author');
UnbPluginMeta('en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.1', 'version');
UnbPluginMeta('unb.devel.20050914', 'version');
UnbPluginMeta('UnbHookGoogleAnalyticsConfig', 'config');

if (!UnbPluginEnabled()) return;


function UnbHookGoogleAnalyticsConfig(&$data)
{
	global $UNB;

	if ($data['request'] == 'fields')
	{
		$field = array();
		$field['fieldtype'] = 'text';
		$field['fieldname'] = 'GAcode';
		$field['fieldvalue'] = rc('ga_code');
		$field['fieldlabel'] = '_googleanalytics.GA code';
		$field['fielddesc'] = '_googleanalytics.GA code~';
		$field['fieldsize'] = 14;
		$field['fieldlength'] = 14;
		$data['fields'][] = $field;
	}

	if ($data['request'] == 'handleform')
	{
		if (isset($_POST['GAcode']))
		{
			$l = trim($_POST['GAcode']);
			$UNB['ConfigFile']['ga_code'] = $l;
		}
		$data['result'] = true;
	}

	return true;
}



function UnbHookInsertGA(&$data) 
{
	global $UNB;
	if ($UNB['_googleanalytics_code'] != "") {
		$ua = $UNB['_googleanalytics_code'];

		$data = <<<SCRIPT
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async="async" src="https://www.googletagmanager.com/gtag/js?id=$ua" type="text/javascript"></script>
<script type="text/javascript">
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '$ua', { 'anonymize_ip': true });
</script>
SCRIPT;
		return true;
	} else return false;
}

// Register hook functions
UnbRegisterHook('page.postfootline.simple', 'UnbHookInsertGA');

// Initialise variables
$UNB['_googleanalytics_code'] = rc('ga_code');
?>