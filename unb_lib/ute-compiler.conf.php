<?php
// Unclassified Template Engine
// http://unclassified.de
// Copyright 2005 by Yves Goergen
//
// ute-compiler.conf.php
// Template compiler configuration

if (!isset($UTE)) $UTE = array();

// ---------- COMPILER OPTIONS ----------

// Skip all PHP regions to prevent arbitrary code being executed. This option
// can be considered security-relevant if the template author has no access to
// the cached PHP files or other parts of the main application. Recommended to
// true. Must be set to false if PHP code embedded in templates should be
// allowed.
//
// WARNING: This will strip out XML file definitions that use the same tag
//          delimiters like short PHP tags.
//
// WARNING: This option is useless if the PHP option 'asp_tags' is enabled
//          because it can only recognise standard (long and short) PHP tags.
//
$UTE['__skipPHP'] = true;

// Template code tag delimiter keys. These are the opening and closing keys
// that define the beginning and end of template code tags. You can use keys
// longer than one character and a different length for opening and closing
// keys.
//
$UTE['__keyStart'] = '{';
$UTE['__keyEnd'] = '}';

// Don't open a template code tag on an occurance of this key. It can be used
// if the opening key needs to be used literally. Each occurance of this key
// in any place where a tag opening key would fit, this key is replaced by the
// actual opening key and no tag will be opened. If the opening key is "{", you
// can use "{{" here and open your JavaScript or CSS blocks with "{{" instead
// of "{" then.
//
$UTE['__keyNoStart'] = '{{';

// Prefix to template code tags for their closing part. You can set this to
// use i.e. {if ...}{endif} or {if ...}{/if}.
//
$UTE['__prefixEnd'] = 'end';

// Maximum allowed identifier length. This value is required for performance
// and error checking reasons. No function name, template code tag name or
// variable/array expression must be longer than this. A value of 11 at least
// is required by the UTE itself.
//
$UTE['__maxTagLen'] = 50;

// Array of registered functions. The keys are the Template Code's function
// names, the values are either an array(parameter_count, real_function_name)
// or a string:full_template.
//
// Example: 'round' => array(1, 'round')
//          'round' => "round(\1)"
//
$UTE['__registeredFunctions'] = array(
	// comparison_operator
	'eq' => "(\1 == \2)",
	'neq' => "(\1 != \2)",
	'eqi' => "(strcasecmp(\1, \2) == 0)",
	'neqi' => "(strcasecmp(\1, \2) != 0)",
	'lt' => "(\1 < \2)",
	'lte' => "(\1 <= \2)",
	'gt' => "(\1 > \2)",
	'gte' => "(\1 >= \2)",
	'lti' => "(strcasecmp(\1, \2) < 0)",
	'ltei' => "(strcasecmp(\1, \2) <= 0)",
	'gti' => "(strcasecmp(\1, \2) > 0)",
	'gtei' => "(strcasecmp(\1, \2) >= 0)",
	'startswith' => "(UteSubstr(\1, 0, UteStrlen(\2)) == \2)",
	'endswith' => "(UteSubstr(\1, -UteStrlen(\2)) == \2)",
	'contains' => "(strpos(\1, \2) !== false)",
	'even' => "(\1 % 2 == 0)",
	'odd' => "(\1 % 2 == 1)",
	'ifnull' => "(\1 ? '' : \2)",

	// logic_operator
	'and' => "(\1 && \2)",
	'or' => "(\1 || \2)",
	'xor' => "(\1 ^^ \2)",
	'not' => "!\1",

	// bitwise_operator
	'bitand' => "(\1 & \2)",
	'bitor' => "(\1 | \2)",
	'bitxor' => "(\1 ^ \2)",
	'bitnot' => "~\1",
	'shl' => "(\1 << \2)",
	'shr' => "(\1 >> \2)",

	// arithemtic_operator
	'add' => "(\1 + \2)",
	'sub' => "(\1 - \2)",
	'mul' => "(\1 * \2)",
	'div' => "(\1 / \2)",
	'mod' => "(\1 % \2)",
	'round' => array(1),
	'ceil' => array(1),
	'floor' => array(1),
	'roundn' => array(2, 'round'),
	'bool' => "(\1 ? 1 : 0)",

	// string_operator
	'concat' => "(\1 . \2)",
	'ucase' => array(1, 'strtoupper'),
	'lcase' => array(1, 'strtolower'),
	'tohtml' => array(1, 't2h'),
	'addslashes' => array(1),
	'stripslashes' => array(1),
	'urlencode' => array(1),
	'urldecode' => array(1),
	'trim' => array(1),
	'ltrim' => array(1),
	'rtrim' => array(1),
	'strlen' => array(1, 'UteStrlen'),
	'sizeof' => array(1),
	'truncate' => array(3, 'UteStrlimit'),

	// system_internal
	'PushEnv' => array(0, 'UtePushEnv'),
	'PopEnv' => array(0, 'UtePopEnv'),

	// html_forms
	'form_htmldata' => "t2i(\$_REQUEST[\1])",
	'form_checked' => "(\$_REQUEST[\1] == 1 ? ' checked=\"checked\" ' : '')",
	'form_checkedval' => "(\$_REQUEST[\1] == \2 ? ' checked=\"checked\" ' : '')",
	'form_checkednot' => "(\$_REQUEST[\1] != \2 ? ' checked=\"checked\" ' : '')",
	'form_selectedval' => "(\$_REQUEST[\1] == \2 ? ' selected=\"selected\" ' : '')",
	'form_selectednot' => "(\$_REQUEST[\1] != \2 ? ' selected=\"selected\" ' : '')",
	'form_checked_if' => "(\1 ? ' checked=\"checked\" ' : '')",
	'form_selected_if' => "(\1 ? ' selected=\"selected\" ' : '')",

	// variable_check
	'true' => 'true',
	'false' => 'false',
	'is_numeric' => array(1),
	'is_string' => array(1),
	'is_int' => array(1),
	'is_bool' => array(1),
	'is_array' => array(1),
	'empty' => "(sizeof(\1) == 0)",
	'dump' => array(1, 'var_dump'),

	// custom_function_name
	'showtemplates' => array(0, 'UteShowAll'),
	'phpversion' => array(0),
	'rc' => array(1),
	't2h' => array(1),
	't2i' => array(1),
	'require-css' => array(1, 'UnbRequireCss'),
	'require-js' => array(1, 'UnbRequireJs'),
	'require-txt' => array(1, 'UnbRequireTxt'),
	'UnbFormSessionId' => array(0),
	'imgurl' => '$GLOBALS[\'UNB\'][\'ImgBaseURL\']',
	'html_hide_id' => array(1, 'JSCollapseID'),
	'UnbAdditionalPageRefs' => array(0),
	'NextTabIndex' => "'tabindex=\"' . ++\$GLOBALS['UnbTplTabIndex'] . '\"'"
);

// Array of source code aliases. Any of these keys can be used in the template
// source code like {=key} and the alias' value will be evaluated in its place.
// You can use literal text or even further template code tags in an alias.
//
$UTE['__aliasTable'] = array(
	'phpver' => '{%PHP_VERSION}',
	'beginjs' => '<script type="text/javascript">//<![CDATA[' . endl,
	'endjs' => '//]]></script>'
);

?>