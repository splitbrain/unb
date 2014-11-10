<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Thread attributes');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('en', 'lang');
UnbPluginMeta('unb.stable.1.6.1 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.devel.20060113', 'version');
UnbPluginMeta('UnbHookThreadAttrConfig', 'config');

if (!UnbPluginEnabled()) return;

// NOTE ON ACCESS CONTROL:
// The ACL action IDs used in this plug-in are hard-coded to start at 101.
// If they need to be moved to another range, follow the start ID here:
//
$UNB['_plugin_threadattributes_startaclid'] = 101;

function UnbHookThreadAttrConfig(&$data)
{
	global $UNB;

	if ($data['request'] == 'fields')
	{
		$fields = UnbPlugInThreadAttrGetFields();

		$field = array();
		$field['fieldtype'] = 'label';
		$field['fieldheader'] = 2;
		$field['fieldlabel'] = '_threadattributes.current attributes';
		$field['fielddesc'] = '';
		$data['fields'][] = $field;

		if (is_array($fields)) foreach ($fields as $f)
		{
			$field = array();
			$field['fieldtype'] = 'tablegroup';
			$field['fieldparam'] = 1;   // open group
			$data['fields'][] = $field;

			$field = array();
			$field['fieldtype'] = 'tabletext';
			$field['fieldname'] = 'FieldTitle_' . $f['fieldid'];
			$field['fieldvalue'] = $f['title'];
			$field['fieldlabel'] = '_threadattributes.field title';
			$field['fieldunit'] = '';
			$field['fielddesc'] = '';
			$field['fieldsize'] = 20;
			$field['fieldlength'] = 20;
			$data['fields'][] = $field;

			$field = array();
			$field['fieldtype'] = 'tabletext';
			$field['fieldname'] = 'FieldType_' . $f['fieldid'];
			$field['fieldvalue'] = $f['type'];
			$field['fieldlabel'] = '_threadattributes.field type';
			$field['fieldunit'] = '';
			$field['fielddesc'] = '';
			$field['fieldsize'] = 60;
			$field['fieldlength'] = 255;
			$data['fields'][] = $field;

			$field = array();
			$field['fieldtype'] = 'tabletext';
			$field['fieldname'] = 'FieldAclId_' . $f['fieldid'];
			$field['fieldvalue'] = $f['aclid'] > 0 ? $f['aclid'] : '';
			$field['fieldlabel'] = '_threadattributes.field aclid';
			$field['fieldunit'] = '';
			$field['fielddesc'] = '';
			$field['fieldsize'] = 4;
			$field['fieldlength'] = 4;
			$data['fields'][] = $field;

			$field = array();
			$field['fieldtype'] = 'tablegroup';
			$field['fieldparam'] = 0;   // close group
			$data['fields'][] = $field;
		}

		$field = array();
		$field['fieldtype'] = 'label';
		$field['fieldheader'] = 2;
		$field['fieldlabel'] = '_threadattributes.add new attribute';
		$field['fielddesc'] = '';
		$data['fields'][] = $field;

		$field = array();
		$field['fieldtype'] = 'tablegroup';
		$field['fieldparam'] = 1;   // open group
		$data['fields'][] = $field;

		$field = array();
		$field['fieldtype'] = 'tabletext';
		$field['fieldname'] = 'FieldTitle_0';
		$field['fieldvalue'] = '';
		$field['fieldlabel'] = '_threadattributes.field title';
		$field['fieldunit'] = '';
		$field['fielddesc'] = '';
		$field['fieldsize'] = 20;
		$field['fieldlength'] = 20;
		$data['fields'][] = $field;

		$field = array();
		$field['fieldtype'] = 'tabletext';
		$field['fieldname'] = 'FieldType_0';
		$field['fieldvalue'] = '';
		$field['fieldlabel'] = '_threadattributes.field type';
		$field['fieldunit'] = '';
		$field['fielddesc'] = '';
		$field['fieldsize'] = 60;
		$field['fieldlength'] = 255;
		$data['fields'][] = $field;

		$field = array();
		$field['fieldtype'] = 'tabletext';
		$field['fieldname'] = 'FieldAclId_0';
		$field['fieldvalue'] = '';
		$field['fieldlabel'] = '_threadattributes.field aclid';
		$field['fieldunit'] = '';
		$field['fielddesc'] = '';
		$field['fieldsize'] = 4;
		$field['fieldlength'] = 4;
		$data['fields'][] = $field;

		$field = array();
		$field['fieldtype'] = 'tablegroup';
		$field['fieldparam'] = 0;   // close group
		$data['fields'][] = $field;

		$field = array();
		$field['fieldtype'] = 'label';
		$field['fieldheader'] = 2;
		$field['fieldlabel'] = '_threadattributes.information';
		$field['fielddesc'] = '';
		$data['fields'][] = $field;

		$field = array();
		$field['fieldtype'] = 'label';
		$field['fieldheader'] = 0;
		$field['fieldlabel'] = '_threadattributes.field type info';
		$field['fielddesc'] = '';
		$data['fields'][] = $field;
	}

	if ($data['request'] == 'handleform')
	{
		$data['result'] = true;
		foreach ($_POST as $key => $value)
		{
			if (preg_match('/^FieldTitle_(\d+)$/', $key, $m))
			{
				$id = intval($m[1]);
				if ($id == 0)
				{
					if (trim($value) != '')
					{
						// Add field
						$type = $_POST['FieldType_' . $id];
						$aclid = $_POST['FieldAclId_' . $id];
						if (!UnbPlugInThreadAttrAddField($value, $type, $aclid))
							$data['result'] = false;
					}
				}
				else
				{
					if (trim($value) == '')
					{
						// Remove field
						if (!UnbPlugInThreadAttrRemoveField($id))
							$data['result'] = false;
					}
					else
					{
						// Change field
						$type = $_POST['FieldType_' . $id];
						$aclid = $_POST['FieldAclId_' . $id];
						if (!UnbPlugInThreadAttrChangeField($id, $value, $type, $aclid))
							$data['result'] = false;
					}
				}
			}
		}
	}

	return true;
}

// Hook function to add my CSS file
//
// Lets you add your own CSS file for the new page. Add your CSS filename to
// the passed $data array. The CSS file must currently reside with the other
// CSS files in the current design's directory.
//
function UnbHookThreadAttrAddcss(&$data)
{
	global $UNB;

	/*if ($UNB['ThisPage'] == '@forum')*/ $data[] = 'threadattributes';

	return true;
}

function UnbHookThreadAttrPreSubject(&$data)
{
	if ($data['threadid'] <= 0) return true;

	$fields = UnbPlugInThreadAttrGetFields();

	if (is_array($fields)) foreach ($fields as $field)
	{
		$type = $field['type'];
		$value = UnbPlugInThreadAttrGetAttribute($data['threadid'], $field['fieldid']);
		$colour = null;

		if (preg_match('/^enum (.*)/', $type, $m))
		{
			$parts = explode('|', $m[1]);
			foreach ($parts as $part)
			{
				list($v, $c) = explode(':', $part);
				if (trim($v) == $value) $colour = trim($c);
			}
		}

		if (isset($colour))
			$data['output'] .= '<span class="threadattributetag ' . t2i($colour) . '">' . t2h($value) . '</span>';
	}

	if ($data['output']) $data['output'] .= ' ';

	return true;
}

function UnbHookThreadAttrEditFields(&$data)
{
	global $UNB_T;

	$forumid = $data['thread']->GetForum();
	$allowEditAll = UnbCheckRights('threadattr_edit_attributes', $forumid, $data['threadid']);
	$fields = UnbPlugInThreadAttrGetFields();

	if (is_array($fields)) foreach ($fields as $field)
	{
		$out = '';
		$id = $field['fieldid'];
		$title = $field['title'];
		$type = $field['type'];
		$aclid = $field['aclid'];

		// Check access: global and (if available) for attributes group
		if ($allowEditAll ||
		    $aclid > 0 && UnbCheckRights('threadattr_edit_attributes:' . $aclid, $forumid, $data['threadid']))
		{
			$value = UnbPlugInThreadAttrGetAttribute($data['threadid'], $field['fieldid']);

			if (preg_match('/^enum (.*)/', $type, $m))
			{
				$out .= t2h($title) . ': <select name="ThreadAttr_' . $id . '">';
				$out .= '<option value=""' . ($value == '' ? ' selected="selected"' : '') . '>' .
					$UNB_T['_threadattributes.unset'] . '</option>';
				$parts = explode('|', $m[1]);
				foreach ($parts as $part)
				{
					list($v, $c) = explode(':', $part);
					$v = trim($v);
					$c = trim($c);
					$out .= '<option value="' . t2i($v) . '"' . ($value == $v ? ' selected="selected"' : '') . '>' .
						t2h($v) . '</option>';
				}
				$out .= '</select><br />';
			}
			$data['output'] .= $out;
		}
	}

	return true;
}

function UnbHookThreadAttrHandleEditFields(&$data)
{
	$forumid = $data['thread']->GetForum();
	$allowEditAll = UnbCheckRights('threadattr_edit_attributes', $forumid, $data['threadid']);
	$fields = UnbPlugInThreadAttrGetFields();

	if (is_array($fields)) foreach ($fields as $field)
	{
		$id = $field['fieldid'];
		$title = $field['title'];
		$type = $field['type'];
		$aclid = $field['aclid'];

		if (isset($_POST['ThreadAttr_' . $id]))
		{
			// Check access: global and (if available) for attributes group
			if ($allowEditAll ||
				$aclid > 0 && UnbCheckRights('threadattr_edit_attributes:' . $aclid, $forumid, $data['threadid']))
			{
				UnbPlugInThreadAttrSetAttribute($data['threadid'], $id, $_POST['ThreadAttr_' . $id]);
			}
		}
	}
	return true;
}

function UnbHookThreadAttrThreadRemoved(&$data)
{
	UnbAddLog('remove_thread_attr ' . $data);

	$threadid = intval($data);
	if ($threadid > 0)
	{
		$fields = UnbPlugInThreadAttrGetFields();
		#UnbAddLog('remove_thread_attr debug:fields.length=' . sizeof($fields));
		if (is_array($fields)) foreach ($fields as $field)
		{
			#UnbAddLog('remove_thread_attr debug:field=' . $field['fieldid']);
			UnbPlugInThreadAttrSetAttribute($threadid, $field['fieldid'], null);
		}
	}

	return true;
}

// Hook function to check access for a custom action.
// Set $data['grant'] to indicate access or a numeric limit.
//
function UnbHookThreadAttrCheckAccess(&$data)
{
	global $UNB;
	$action = $data['action'];
	$forum = $data['forum'];
	$thread = $data['thread'];
	$user = $data['user'];
	$date = $data['date'];
	$isLastPost = $data['isLastPost'];
	$read_only = $data['read_only'];

	switch ($data['action'])
	{
		// This is the right place to add your custom actions. You need to map an action name
		// to a numeric ID that is stored in the database. You should use numbers above 100.
		// To check a global action, add PHP code like this:
		/*case 'myglobalaction':
			$data['grant'] = $UNB['ACL'][101][0];
			break;*/

		// To check an action that depends on the current forum, thread or other parameters,
		// add PHP code like this:
		case 'threadattr_edit_attributes':
			if ($read_only)
			{
				$data['grant'] = false;
				break;
			}
			$aclid = $UNB['_plugin_threadattributes_startaclid'];
			if (isset($UNB['ACL'][$aclid][-$thread]))
			{
				$data['grant'] = $UNB['ACL'][$aclid][-$thread];
				break;
			}
			$data['grant'] = $UNB['ACL'][$aclid][$forum];
			break;

		// Thread IDs are stored as negative numbers, forum IDs are positive numbers.
		// Numeric grants can be implemented similar to the above numeric cases like 'maxattachsize'.
		//
		// To prevent access if the board is in global read-only mode, add the $read_only
		// lines as above.
	}

	if (preg_match('/^threadattr_edit_attributes:(\d+)$/', $data['action'], $m))
	{
		if ($read_only)
		{
			$data['grant'] = false;
		}
		else
		{
			$aclid = $UNB['_plugin_threadattributes_startaclid'] + $m[1];
			if (isset($UNB['ACL'][$aclid][-$thread]))
			{
				$data['grant'] = $UNB['ACL'][$aclid][-$thread];
			}
			else
			{
				$data['grant'] = $UNB['ACL'][$aclid][$forum];
			}
		}
	}

	return true;
}

// Hook function to determine whether a custom action is a numeric value.
// Set $data['numeric'] = true, if the numeric action ID in $data['action'] describes
// a numeric action limit.
//
function UnbHookThreadAttrNumeric(&$data)
{
	global $UNB;

	switch ($data['action'])
	{
		case $UNB['_plugin_threadattributes_startaclid']:
			$data['numeric'] = false;
			break;
	}

	if ($data['action'] >= $UNB['_plugin_threadattributes_startaclid'] + 1 &&
		$data['action'] <= $UNB['_plugin_threadattributes_maxaclid'])
	{
		$data['numeric'] = false;
	}

	return true;
}

// Hook function to determine whether a custom action is forum- or thread-specific.
// Set $data['specific'] = true, if the numeric action ID in $data['action'] describes
// a forum- or thread-specific action.
//
function UnbHookThreadAttrSpecific(&$data)
{
	global $UNB;

	switch ($data['action'])
	{
		case $UNB['_plugin_threadattributes_startaclid']:
			$data['specific'] = true;
			break;
	}

	if ($data['action'] >= $UNB['_plugin_threadattributes_startaclid'] + 1 &&
		$data['action'] <= $UNB['_plugin_threadattributes_maxaclid'])
	{
		$data['specific'] = true;
	}

	return true;
}

function UnbHookThreadAttrPostLogin()
{
	global $UNB, $UNB_T;

	$fields = UnbPlugInThreadAttrGetFields();
	$startid = $UNB['_plugin_threadattributes_startaclid'];
	$UNB_T['_acleditor.action.' . $startid] = $UNB_T['_threadattributes.acl.editall'];

	$endid = $startid;
	if (is_array($fields)) foreach ($fields as $field)
	{
		if ($field['aclid'] > 0 && $startid + $field['aclid'] > $endid)
			$endid = $startid + $field['aclid'];
	}
	$UNB['_plugin_threadattributes_maxaclid'] = $endid;

	for ($id = $startid + 1; $id <= $endid; $id++)
	{
		$UNB_T['_acleditor.action.' . $id] = $UNB_T['_threadattributes.acl.basename'] . ' ' . ($id - $startid);
	}

	return true;
}

// Register hook functions
UnbRegisterHook('page.addcss', 'UnbHookThreadAttrAddcss');
UnbRegisterHook('threadlist.presubject', 'UnbHookThreadAttrPreSubject');
UnbRegisterHook('threadview.presubject', 'UnbHookThreadAttrPreSubject');
UnbRegisterHook('threadlist.editfields', 'UnbHookThreadAttrEditFields');
UnbRegisterHook('threadlist.handleeditfields', 'UnbHookThreadAttrHandleEditFields');
UnbRegisterHook('thread.removed', 'UnbHookThreadAttrThreadRemoved');
UnbRegisterHook('acl.customaction', 'UnbHookThreadAttrCheckAccess');
UnbRegisterHook('acl.customaction.specific', 'UnbHookThreadAttrSpecific');
UnbRegisterHook('acl.customaction.numeric', 'UnbHookThreadAttrNumeric');
UnbRegisterHook('session.postlogin', 'UnbHookThreadAttrPostLogin');

// ---------- Internal functions ----------

// See if our database table exists and create it if not
//
function UnbPlugInThreadAttrCheckTable()
{
	global $UNB;
	if ($UNB['_plugin_threadattributes_checked']) return;

	// This table contains the actual attribute values for each thread
	$table = 'threadattributes';
	$fields = 'threadid INT NOT NULL DEFAULT 0, fieldid INT NOT NULL DEFAULT 0, value VARCHAR(255)';
	if ($UNB['Db']->ListTableCols($table) === false)
	{
		$UNB['Db']->CreateTable($table, $fields);
	}

	// This table contains the field names
	// type = "text", "number", "enum value:colour | ..."
	$table = 'threadattributes_def';
	$fields = 'fieldid INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL DEFAULT "", type VARCHAR(255), aclid SMALLINT UNSIGNED NOT NULL DEFAULT 0';
	if ($UNB['Db']->ListTableCols($table) === false)
	{
		$UNB['Db']->CreateTable($table, $fields);
	}

	$UNB['_plugin_threadattributes_checked'] = true;
}

// Get all thread attribute fields
//
function UnbPlugInThreadAttrGetFields()
{
	global $UNB;

	UnbPlugInThreadAttrCheckTable();

	$table = 'threadattributes_def';
	#return $UNB['Db']->FastQueryArray($table, '*', '', /*order*/ 'title');

	// better: read all once and cache
	if ($UNB['_plugin_threadattributes_cache_def'] === null)
	{
		$records = $UNB['Db']->FastQueryArray($table, '*', '', /*order*/ 'title');
		$UNB['_plugin_threadattributes_cache_def'] = $records;
	}
	else
	{
		$records = $UNB['_plugin_threadattributes_cache_def'];
	}

	return $records;
}

// Add an attribute field
//
function UnbPlugInThreadAttrAddField($title, $type, $aclid)
{
	global $UNB;

	UnbPlugInThreadAttrCheckTable();

	$table = 'threadattributes_def';
	$fields = array('title' => $title, 'type' => $type, 'aclid' => intval($aclid));
	return $UNB['Db']->AddRecord($fields, $table);
}

// Change an attribute field
//
function UnbPlugInThreadAttrChangeField($fieldid, $title, $type, $aclid)
{
	global $UNB;

	$fieldid = intval($fieldid);

	UnbPlugInThreadAttrCheckTable();

	$table = 'threadattributes_def';
	$fields = array('title' => $title, 'type' => $type, 'aclid' => intval($aclid));
	$where = 'fieldid = ' . $fieldid;
	return $UNB['Db']->ChangeRecord($fields, $where, $table);
}

// Remove an attribute field
//
function UnbPlugInThreadAttrRemoveField($fieldid)
{
	global $UNB;

	$fieldid = intval($fieldid);

	UnbPlugInThreadAttrCheckTable();

	$table = 'threadattributes_def';
	$where = 'fieldid = ' . $fieldid;
	$res1 = $UNB['Db']->RemoveRecord($where, $table);

	$table = 'threadattributes';
	$where = 'fieldid = ' . $fieldid;
	$res2 = $UNB['Db']->RemoveRecord($where, $table);

	// TODO: remove access grants from ACL table (first find out aclid of this field!)

	return $res1 && $res2;
}

// Set a thread attribute
//
// in threadid = (int) Thread ID
// in fieldid = (int) Field ID
// in value = (string) New field value
//
// returns (bool) success
//
function UnbPlugInThreadAttrSetAttribute($threadid, $fieldid, $value)
{
	global $UNB;

	$threadid = intval($threadid);
	$fieldid = intval($fieldid);

	UnbPlugInThreadAttrCheckTable();

	$table = 'threadattributes';
	$where = 'threadid = ' . $threadid . ' AND fieldid = ' . $fieldid;
	$fields = array('threadid' => $threadid, 'fieldid' => $fieldid, 'value' => trim($value));
	$oldvalue = $UNB['Db']->FastQuery1st($table, 'value', $where);
	if ($oldvalue === false && $value !== null)
		return $UNB['Db']->AddRecord($fields, $table);
	elseif ($oldvalue != $value && $value !== null)
		return $UNB['Db']->ChangeRecord($fields, $where, $table);
	elseif ($oldvalue !== false && $value === null)
		return $UNB['Db']->RemoveRecord($where, $table, 1);
	else
		return true;
}

// Get a thread attribute
//
// in threadid = (int) Thread ID
// in fieldid = (int) Field ID
//
// returns (string) value with "" for unset value, or (bool) false on error
//
function UnbPlugInThreadAttrGetAttribute($threadid, $fieldid)
{
	global $UNB;

	$threadid = intval($threadid);
	$fieldid = intval($fieldid);

	UnbPlugInThreadAttrCheckTable();

	$table = 'threadattributes';
	#$where = 'threadid = ' . $threadid . ' AND fieldid = ' . $fieldid;
	#$value = $UNB['Db']->FastQuery1st($table, 'value', $where);

	// better: read all once and cache
	if ($UNB['_plugin_threadattributes_cache'] === null)
	{
		$where = '';
		$records = $UNB['Db']->FastQueryArray($table, '*', $where);

		// build cache
		$cache = array();
		foreach ($records as $record)
		{
			$_threadid = $record['threadid'];
			$_fieldid = $record['fieldid'];
			$_value = $record['value'];
			$cache[$_threadid][$_fieldid] = $_value;
		}
		$UNB['_plugin_threadattributes_cache'] = $cache;
	}
	else
	{
		$cache = $UNB['_plugin_threadattributes_cache'];
	}

	return trim($cache[$threadid][$fieldid]);
}

?>