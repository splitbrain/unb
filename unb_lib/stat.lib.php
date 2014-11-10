<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// stat.lib.php
// Statistics table access functiuons

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Currently unused
/*
// Read out one day's Statistic values
//
// key = Value to read or '' for an array with all values
// day = 0h timestamp of requested day or 0 for current day
//
function UnbReadStat($key = '', $date = 0)
{
	global $UNB;

	// set current time if needed
	if (!$date) $date = time();
	// calculate last 0h from that time on
	$last_0h = getdate(time());
	$last_0h = gmmktime(0, 0, 0, $last_0h['mon'], $last_0h['mday'], $last_0h['year']);

	#$lt = localtime();
	#$last_0h = $date - (($lt[2] * 60 + $lt[1]) * 60 + $lt[0]);

	// there can possibly be a second change between both above lines!
	// then $last_0h is ...1 what we have to correct now
	#$last_0h -= $last_0h % 5;

	if ($key != '')
	{
		return $UNB['Db']->FastQuery1st('Stat', $key, 'Date=' . $last_0h);
	}
	else
	{
		return $UNB['Db']->FastQuery('Stat', '*', 'Date=' . $last_0h);
	}
}
*/

// Update a statistics value in the database
//
// in key = (string) "NewThreads", "NewPosts", "OnlineUsers", "OnlineGuests", "NewUsers"
// in value = (int)
//   for "OnlineUsers" and "OnlineGuests" keys:
//     New absolute value, will be saved if it's greater than current value
//   else:
//     Value to add/subtract to current value (difference value)
//
function UnbUpdateStat($key, $offset)
{
	global $UNB;

	// Clean parameters
	$key = trim(strval($key));
	$offset = intval($offset);

	// calculate last 0h of today
	$last_0h = getdate(time());
	$last_0h = gmmktime(0, 0, 0, $last_0h['mon'], $last_0h['mday'], $last_0h['year']);

	#var_dump(gmdate('d.m.Y H:i:s', $last_0h));

	#$lt = localtime();
	#$last_0h = time() - (($lt[2] * 60 + $lt[1]) * 60 + $lt[0]);

	// there can possibly be a second change between both above lines!
	// then $last_0h is ...1 what we have to correct now
	#$last_0h -= $last_0h % 5;

	$ret = $UNB['Db']->FastQuery1st('Stat', 'COUNT(*)', 'Date=' . $last_0h);

	if ($ret !== false && $ret == 0)
	{
		// we are on another day now than we ware the last page hit.
		// let's see if we missed some days in the past?
		$maxdate = $UNB['Db']->FastQuery1st('Stat', 'MAX(Date)');
		if ($maxdate > 0 && $maxdate < $last_0h - 86400)
		{
			// some lines are missing. insert them now...
			$maxdate += 86400;
			while ($maxdate < $last_0h)
			{
				if (!$UNB['Db']->AddRecord("Date = $maxdate", 'Stat')) break;
				$maxdate += 86400;
			}
		}

		return $UNB['Db']->AddRecord("Date = $last_0h, $key = $offset", 'Stat');
	}
	else
	{
		if ($key == 'OnlineUsers' || $key == 'OnlineGuests')
		{
			return $UNB['Db']->ChangeRecord("$key = $offset", 'Date=' . $last_0h, 'Stat');
		}
		else
		{
			return $UNB['Db']->ChangeRecord("$key = $key + $offset", 'Date=' . $last_0h, 'Stat');
		}
	}
}

// Update user/guest count in Stat table
//
function UnbUpdateUserStat()
{
	global $UNB;

	// count today's online users
	$last_0h = getdate(time());
	$last_0h = gmmktime(0, 0, 0, $last_0h['mon'], $last_0h['mday'], $last_0h['year']);

	#$a = localtime();
	#$last_0h = time() - (($a[2] * 60 + $a[1]) * 60 + $a[0]);

	// there can possibly be a second change between both above lines!
	// then $last_0h is ...1 what we have to correct now
	#$last_0h -= $last_0h % 5;

	$count = $UNB['Db']->FastQuery1st('Users', 'COUNT(*)', 'LastActivity > ' . $last_0h);
	$guests = $UNB['Db']->FastQuery1st('Guests', 'COUNT(*)', 'LastActivity > ' . $last_0h . " AND UserName <> '_not_a_browser_'");
	#$guests = $UNB['Db']->FastQuery1st('Guests', 'COUNT(*)', 'LastActivity > ' . $last_0h);

	// update statistics table, no error detection here
	UnbUpdateStat('OnlineUsers', $count);
	UnbUpdateStat('OnlineGuests', $guests);
}

?>