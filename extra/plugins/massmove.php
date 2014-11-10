<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Mass-move threads from one forum to another');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('de', 'lang');
UnbPluginMeta('unb.stable.1.6.5 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.devel.20071015', 'version');
#UnbPluginMeta('UnbHookMassMoveConfig', 'config');

if (!UnbPluginEnabled()) return;

function UnbHookMassMoveConfig(&$data)
{
	global $UNB;

	if ($data['request'] == 'fields')
	{
		// No web configuration available
	}

	if ($data['request'] == 'handleform')
	{
		// No web configuration available
		$data['result'] = true;
	}

	return true;
}

// Hook function to add a new control panel category
//
function UnbHookMassMoveAddCPCategory(&$data)
{
	if (UnbCheckRights('is_admin'))
	{
		$data[] = array(
			'title' => '_massmove.cpcategory',
			'link' => UnbLink('@cp', 'cat=massmove', true),
			'cpcat' => 'massmove');
	}

	return true;
}

// Hook function to show the control panel category
//
function UnbHookMassMoveCPCategoryPage(&$data)
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	if ($data['cat'] == 'massmove' &&
	    UnbCheckRights('is_admin'))
	{
		$src_forum = intval($_POST['src_forum']);
		$dst_forum = intval($_POST['dst_forum']);
		$untildate = trim($_POST['untildate']);

		if (isset($_POST['src_forum']) && isset($_POST['dst_forum']))
		{
			$error = false;

			if ($untildate != '')
			{
				if (preg_match('_^([0-9]{4})-([0-9]{2})-([0-9]{2})$_', $untildate, $m))
				{
					$year = $m[1];
					$mon = $m[2];
					$day = $m[3];

					if ($year < 1970 || $year > 2037)
					{
						$error .= $UNB_T['_massmove.err.invalid year value'] . '<br />';
					}
					else
					{
						$untildate_ts = mktime(0, 0, 0, $mon, $day + 1, $year);   // Go to following midnight (server timezone) to include the whole specified day
					}
				}
				else
				{
					$error .= $UNB_T['_massmove.err.invalid date format'] . '<br />';
				}
			}
			else
			{
				$untildate_ts = 2147483647;   // 2^31 - 1 as a maximum value
			}

			if ($src_forum == $dst_forum)
			{
				$error .= $UNB_T['_massmove.err.both forums equal'] . '<br />';
			}

			if (!$error)
			{
				// Move all threads
				$res = $UNB['Db']->ChangeRecord(array('Forum' => $dst_forum), '`Forum` = ' . $src_forum . ' AND `Date` < ' . $untildate_ts, 'Threads');
				UnbAddLog('Mass-moving threads from forum ' . $src_forum . ' to ' . $dst_forum . ' until ' . $untildate);
				if (!$res) $error = $UNB_T['_massmove.err.database error'] . ': ' . $UNB['Db']->LastError();
			}

			if ($error)
			{
				$TP['errorMsg'] .= $error;
			}
			else
			{
				$_SESSION['UnbSavedSuccess'] = true;
				UnbForwardHTML(UnbLink('@this', 'cat=massmove'));
			}
		}

		if ($_SESSION['UnbSavedSuccess'])
		{
			$_SESSION['UnbSavedSuccess'] = false;
			$TP['infoMsg'] .= $UNB_T['_massmove.alldone'] . '<br />';
		}

		$TP['controlpanelMoreCats'][] = 'controlpanel_generic.html';

		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);

		global $output;
		$output = '';
		UnbListForumsRec($src_forum, true, 0, 0, true, true, true);

		$precont = '<form action="" method="post">';
		$precont .= '<input type="hidden" name="cat" value="massmove" />';

		$cont = '<div class="option first">';
		$cont .= '<div class="box">' . $UNB_T['_massmove.srcforum'] . ': ';
		$cont .= '<select name="src_forum">';
		$cont .= $output;
		$cont .= '</select></div>';

		$output = '';
		UnbListForumsRec($dst_forum, true, 0, 0, true, true, true);

		$cont .= '<div class="box">' . $UNB_T['_massmove.dstforum'] . ': ';
		$cont .= '<select name="dst_forum">';
		$cont .= $output;
		$cont .= '</select></div>';

		$cont .= '<div class="box">' . $UNB_T['_massmove.untildate'] . ': ';
		$cont .= '<input type="text" name="untildate" size="11" value="' . t2i($untildate) . '" />';
		$cont .= ' <small>(' . $UNB_T['_massmove.untildate~'] . ')</small></div>';
		$cont .= '</div>';

		$postcont = '<div class="buttons"><input type="submit" value="' . $UNB_T['_massmove.do'] . '" /></div>';
		$postcont .= '</form>';

		$TP['GenericTitle'] = '_massmove.cpcategory';
		$TP['GenericPreContent'] = $precont;
		$TP['GenericContent'] = $cont;
		$TP['GenericPostContent'] = $postcont;
	}

	return true;
}

// Register hook functions
UnbRegisterHook('cp.addcategory', 'UnbHookMassMoveAddCPCategory');
UnbRegisterHook('cp.categorypage', 'UnbHookMassMoveCPCategoryPage');

?>