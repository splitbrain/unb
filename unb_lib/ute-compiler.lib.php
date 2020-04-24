<?php
// Unclassified Template Engine
// http://unclassified.de
// Copyright 2005 by Yves Goergen
//
// ute-compiler.lib.php
// Template compiler functions


require_once(dirname(__FILE__) . '/ute-compiler.conf.php');

// Compile a source template into its correspondant PHP code
//
// in text = (string) source template code
//
// returns (string) compiled PHP code
// uses $UTE
//
function UteCompile($text)
{
	global $UTE;

	if (!isset($UTE)) $UTE = array();

	if ($UTE['__keyStart'] == '' || $UTE['__keyEnd'] == '')
		require(dirname(__FILE__) . '/ute-compiler.conf.php');
		#return '__error:noconfig__';

	// initialise data
	$keyStart = $UTE['__keyStart'];
	$keyNoStart = $UTE['__keyNoStart'];
	$keyEnd = $UTE['__keyEnd'];
	$prefixEnd = $UTE['__prefixEnd'];
	$maxTagLen = $UTE['__maxTagLen'];
	$skipPHP = $UTE['__skipPHP'];
	$skipText = false;   // ignore any text outside template code tags
	if (!isset($UTE['__registeredFunctions'])) $UTE['__registeredFunctions'] = array();

	$out = '';
	$doProc = true;              // status variable for don't-process-content tags
	$status = array();           // stack of block commands (if, while, foreach)
	if (!isset($UTE['__loops'])) $UTE['__loops'] = array();   // stack of nested loop information
	$UTE['__useVarFunction'] = true;   // use a function to resolve parameters? doesn't work with foreach parameter
	$length = strlen($text);
	$keyStartLen = strlen($keyStart);
	$keyNoStartLen = strlen($keyNoStart);
	$keyEndLen = strlen($keyEnd);
	$pos = 0;

	while ($pos < $length)
	{
		// find next interesting position (that is $keyStart)
		$startPos = $pos;
		$posCurr = $pos;
		$pos = strpos($text, $keyStart, $posCurr);
		if ($pos === false) $pos = $length;

		// skip PHP regions?
		if ($skipPHP)
		{
			$posPHP = strpos($text, '<?', $posCurr);
			if ($posPHP === false) $posPHP = $length;
			if ($posPHP < $pos)
			{
				$pos = $posPHP;

				// output all text until there
				$out .= substr($text, $startPos, $pos - $startPos);

				$endPos = strpos($text, '?>', $pos + 2);
				if ($endPos === false)
				{
					// PHP region is not closed, ignore it
					$out .= '<?';
					$pos += 2;
					continue;
				}
				// PHP region closed, skip it
				$pos = $endPos + 2;
				continue;
			}
		}

		// output all text until there
		if (!$skipText) $out .= substr($text, $startPos, $pos - $startPos);
		if ($pos === $length) break;   // we're finished here

		// see if we should ignore this code
		if (substr($text, $pos, $keyNoStartLen) === $keyNoStart && $doProc)
		{
			// it's not a TCode start tag. so just skip it
			$out .= $keyStart;
			$pos += $keyNoStartLen;
			continue;
		}

		// see if it's a comment
		if (substr($text, $pos, $keyStartLen + 2) === $keyStart . '--' && $doProc)
		{
			// it's a comment. so just skip it
			$endPos = strpos($text, $keyEnd, $pos + $keyStartLen + 2);
			if ($endPos === false)
			{
				// comment is not closed, ignore it
				if (!$skipText) $out .= $keyStart;
				$pos += $keyStartLen;
				continue;
			}
			// comment closed, skip it
			$pos = $endPos + $keyEndLen;
			continue;
		}

		// find the end of the tagname
		$endPos = $pos + $keyStartLen;
		while (substr($text, $endPos, $keyEndLen) !== $keyEnd &&
		       $text{$endPos} !== '(' &&
		       $text{$endPos} !== ' ' &&
		       $endPos - ($pos + $keyStartLen) <= $maxTagLen)
			$endPos++;
		$thisTag = substr($text, $pos + $keyStartLen, $endPos - ($pos + $keyStartLen));
		// if we flew out of the search loop with no end in a reasonable distance, ignore it
		if ($endPos - ($pos + $keyStartLen) > $maxTagLen)
		{
			if (!$skipText) $out .= $keyStart;
			$pos += $keyStartLen;
			continue;
		}
		// now we have the tagname separated

		$innerStart = $pos + $keyStartLen;   // where the tag's inner text begins

		// find entire tag's end to jump to it when we're finished here
		$innerEnd = $endPos;
		$inStr = false;
		while ((substr($text, $innerEnd, $keyEndLen) !== $keyEnd || $inStr) && $innerEnd < $length)
		{
			if ($text{$innerEnd} === '\\')
				$innerEnd++;
			elseif ($text{$innerEnd} === '"')
				$inStr = !$inStr;
			elseif (($text{$innerEnd} === '(' ||
			         $text{$innerEnd} === ')' ||
			         $text{$innerEnd} === ',') &&
			        !$inStr)
				$text{$innerEnd} = ' ';
			$innerEnd++;
		}

		$outerEnd = $innerEnd + $keyEndLen - 1;   // where the tag's outer text ends
		$paramStart = $innerStart + strlen($thisTag);

		// process this tag, let's see what it is
		if ($thisTag === 'if' && $doProc)
		{
			$expr = substr($text, $paramStart, $innerEnd - $paramStart);
			$parts = UteExplodeQuoted(' ', $expr, true, true);
			$expr = UteParseExpression($parts);
			if (substr($expr, 0, 7) === '__error')
			{
				$out .= '__error:if.expr' . $expr . '__';
			}
			else
			{
				$status[] = 'if';   // remember we're in a condition now and process the following expression
				$out .= '<?php if (' . $expr . ') { ?>';
			}
		}
		elseif ($thisTag === 'elseif' && $doProc)
		{
			$expr = substr($text, $paramStart, $innerEnd - $paramStart);
			$parts = UteExplodeQuoted(' ', $expr, true, true);
			$expr = UteParseExpression($parts);
			$prev = array_pop($status);   // is this allowed?
			if (substr($expr, 0, 7) === '__error')
			{
				$out .= '__error:elseif.expr' . $expr . '__';
			}
			if ($prev !== 'if')
			{
				$out .= '__error:elseif.nesting__';
			}
			else
			{
				// remember we're in a condition now and process the following expression
				$status[] = 'if';
				$out .= '<?php } elseif (' . $expr . ') { ?>';
			}
		}
		elseif ($thisTag === 'else' && $doProc)
		{
			$prev = array_pop($status);   // is this allowed?
			if ($prev !== 'if')
			{
				$out .= '__error:else.nesting__';
			}
			else
			{
				// remember we're in a condition now and process the following expression
				$status[] = 'else';
				$out .= '<?php } else { ?>';
			}
		}
		elseif ($thisTag === $prefixEnd . 'if' && $doProc)
		{
			$prev = array_pop($status);   // is this allowed?
			if ($prev !== 'if' && $prev !== 'else')
			{
				$out .= '__error:endif.nesting__';
			}
			else
			{
				$out .= '<?php } ?>';
			}
		}
		elseif ($thisTag === 'while' && $doProc)
		{
			$expr = substr($text, $paramStart, $innerEnd - $paramStart);
			$parts = UteExplodeQuoted(' ', $expr, true, true);
			$expr = UteParseExpression($parts);
			if (substr($expr, 0, 7) === '__error')
			{
				$out .= '__error:while.expr' . $expr . '__';
			}
			else
			{
				// remember we're in a condition now and process the following expression
				$status[] = 'while';
				$out .= '<?php while (' . $expr . ') { ?>';
			}
		}
		elseif ($thisTag === $prefixEnd . 'while' && $doProc)
		{
			$prev = array_pop($status);   // is this allowed?
			if ($prev !== 'while')
			{
				$out .= '__error:endwhile.nesting__';
			}
			else
			{
				$out .= '<?php } ?>';
			}
		}
		elseif ($thisTag === 'exit' && $doProc)
		{
			$prev = array_pop($status);   // is this allowed?
			if ($prev !== 'while' && $prev !== 'foreach')
			{
				$out .= '__error:exit.nesting__';
				// TODO: this disallows 'exit' not directly under loops, i.e. inside 'if's
				// -> look through entire $status array
			}
			else
			{
				if ($prev === 'foreach')
					$rtLoops = 'array_pop($UTE[\'__loops_rt\']); ';
				else
					$rtLoops = '';
				$out .= '<?php break; ' . $rtLoops . '?>';
			}
		}
		elseif ($thisTag === 'next' && $doProc)
		{
			$prev = array_pop($status);   // is this allowed?
			if ($prev !== 'while' && $prev !== 'foreach')
			{
				$out .= '__error:next.nesting__';
				// TODO: this disallows 'next' not directly under loops, i.e. inside 'if's
				// -> look through entire $status array
			}
			else
			{
				$out .= '<?php continue; ?>';
			}
		}
		elseif ($thisTag === 'foreach' && $doProc)
		{
			// remember we're in a condition now and process the following expression
			$status[] = 'foreach';

			$expr = substr($text, $paramStart, $innerEnd - $paramStart);
			$parts = UteExplodeQuoted(' ', $expr, true, true);

			// split expression in its components and check if there's an "in" after the first identifier
			$localVar = '';
			$localExpr = '';
			foreach ($parts as $index => $p)
			{
				if ($p !== '' && $localVar === '')
				{
					if ($p{0} !== '$') $p = '$' . $p;
					$localVar = $p;   // first part is the name
				}
				elseif ($p !== '' && $localVar !== '')
				{
					if ($p === 'in')
					{
						// we have a local variable name
						$UTE['__useVarFunction'] = false;
						$localExpr = UteParseExpression(array($localVar));
						$UTE['__useVarFunction'] = true;

						$parts = array_slice($parts, $index + 1);
					}
					break;
				}
			}

			$expr = UteParseExpression($parts);
			if (substr($expr, 0, 7) === '__error')
			{
				$out .= '__error:foreach.expr' . $expr . '__';
			}
			else
			{
				// remember we're in a loop. put the last localExpr on the stack because
				// we need to handle following variables differently depending on whether
				// we have a localVar name or not
				$UTE['__loops'][] = $localExpr;
				// build the internal localVar for the loop
				$loopCount = sizeof($UTE['__loops']);
				$loopName = '__UTE_LOOPVAR' . $loopCount;
				// generate the actual PHP foreach code
				$out .= '<?php if (is_array(' . $expr . ')) { ';
				$out .= '$UTE[\'__loop_count\'] = 0; ';
				$out .= 'foreach (' . $expr . ' as $' . $loopName . ') { ';
				// if we have a template-provided localVar, assign the internal value to it
				if ($localExpr !== '') $out .= $localExpr . ' = $' . $loopName . '; ';
				// otherwise make the internal localVar globally available (for UteVariable())
				else $out .= '$GLOBALS[\'' . $loopName . '\'] = $' . $loopName . '; ';
				// also remember the loop level at runtime
				$out .= '$UTE[\'__loops_rt\'][] = ' . ($localExpr !== '' ? 1 : 0) . '; ';
				$out .= '?>';

				// NOTE: the __loops_rt stack must be defined AFTER the foreach array is
				//       evaluated because otherwise UteVariable() would already read the
				//       data from the yet invalid local loop variable at runtime.
				//       When anonymous loop variables are disabled or the UteVariable()
				//       function is no longer used, this __loops_rt stack also becomes
				//       unnecessary.
			}
		}
		elseif ($thisTag === $prefixEnd . 'foreach' && $doProc)
		{
			$prev = array_pop($status);   // is this allowed?
			if ($prev !== 'foreach')
			{
				$out .= '__error:endforeach.nesting__';
			}
			else
			{
				// forget the loop for later variables expansion (at compile time & runtime)
				array_pop($UTE['__loops']);
				$out .= '<?php array_pop($UTE[\'__loops_rt\']); ';
				$out .= '$UTE[\'__loop_count\']++; ';
				$out .= '} } ?>';
			}
		}
		elseif ($thisTag === 'for' && $doProc)
		{
			// remember we're in a condition now and process the following expression
			$status[] = 'for';

			$expr = substr($text, $paramStart, $innerEnd - $paramStart);
			$parts = UteExplodeQuoted(' ', $expr, true, true);

			// split expression in its components
			$localVar = '';
			$localExpr = '';
			$fromExpr = '';
			$toExpr = '';
			foreach ($parts as $index => $p)
			{
				if ($p !== '' && $localVar === '')
				{
					if ($p{0} !== '$') $p = '$' . $p;
					$localVar = $p;   // first part is the name

					// we have a local variable name
					$UTE['__useVarFunction'] = false;
					$localExpr = UteParseExpression(array($localVar));
					$UTE['__useVarFunction'] = true;

					$parts = array_slice($parts, $index + 1);
				}
				elseif ($p !== '')
				{
					$pos = -1;
					$fromExpr = UteParseExpressionRec($parts, $pos);
					$toExpr = UteParseExpressionRec($parts, $pos);
					break;
				}
			}

			if (substr($fromExpr, 0, 7) === '__error')
			{
				$out .= '__error:for.from-expr' . $fromExpr . '__';
			}
			elseif (substr($toExpr, 0, 7) === '__error')
			{
				$out .= '__error:for.to-expr' . $toExpr . '__';
			}
			else
			{
				// remember we're in a loop. put the last localExpr on the stack because
				// we need to handle following variables differently depending on whether
				// we have a localVar name or not
				$UTE['__loops'][] = $localExpr;
				// build the internal localVar for the loop
				$loopCount = sizeof($UTE['__loops']);
				$loopName = '__UTE_LOOPVAR' . $loopCount;
				// generate the actual PHP for code
				$out .= '<?php ';
				$out .= '$UTE[\'__loop_count\'] = 0; ';
				$out .= 'for ($' . $loopName . ' = ' . $fromExpr . '; $' . $loopName . ' <= ' . $toExpr . '; $' . $loopName . '++) { ';
				// if we have a template-provided localVar, assign the internal value to it
				$out .= $localExpr . ' = $' . $loopName . '; ';
				// also remember the loop level at runtime
				$out .= '$UTE[\'__loops_rt\'][] = 1; ';
				$out .= '?>';
			}
		}
		elseif ($thisTag === $prefixEnd . 'for' && $doProc)
		{
			$prev = array_pop($status);   // is this allowed?
			if ($prev !== 'for')
			{
				$out .= '__error:endfor.nesting__';
			}
			else
			{
				// forget the loop for later variables expansion (at compile time & runtime)
				array_pop($UTE['__loops']);
				$out .= '<?php array_pop($UTE[\'__loops_rt\']); ';
				$out .= '$UTE[\'__loop_count\']++; ';
				$out .= '} ?>';
			}
		}
		elseif ($thisTag === 'literal' && $doProc)
		{
			$doProc = false;
		}
		elseif ($thisTag === $prefixEnd . 'literal' && !$doProc)
		{
			$doProc = true;
		}
		elseif ($thisTag === 'skiptext' && $doProc)
		{
			$skipText = true;
		}
		elseif ($thisTag{0} === '=' && $doProc)
		{
			$alias = $UTE['__aliasTable'][substr($thisTag, 1)];
			if (!isset($alias))
			{
				$out .= '__error:alias.undef__';
			}
			else
			{
				$out .= UteCompile($alias);
			}
		}
		elseif ($thisTag === 'set' && $doProc)
		{
			$expr = substr($text, $paramStart, $innerEnd - $paramStart);
			$parts = UteExplodeQuoted(' ', $expr, true, true);

			$localExpr = '';
			foreach ($parts as $index => $p)
			{
				if ($p !== '')
				{
					$UTE['__useVarFunction'] = false;
					if ($p{0} !== '$') $p = '$' . $p;
					$localExpr = UteParseExpression(array($p));
					$UTE['__useVarFunction'] = true;
					$parts = array_slice($parts, $index + 1);
					break;
				}
			}

			$expr = UteParseExpression($parts);
			if (substr($expr, 0, 7) === '__error')
			{
				$out .= '__error:set.expr' . $expr . '__';
			}
			else
			{
				// remember we're in a condition now and process the following expression
				$out .= '<?php ' . $localExpr . ' = ' . $expr . ' ?>';
			}
		}
		elseif ($doProc)
		{
			// try to evaluate the expression
			$expr = substr($text, $innerStart, $innerEnd - $innerStart);
			$parts = UteExplodeQuoted(' ', $expr, true, true);
			$expr = UteParseExpression($parts);

			if (substr($expr, 0, 7) === '__error')
			{
				$out .= '__error:proc.expr' . $expr . '__';
			}
			else
			{
				$out .= '<?php echo ' . $expr . ' ?>';
			}
		}

		// TODO: rename "suspend" to "literal"?
		if ($doProc || $thisTag === 'suspend') $pos = $outerEnd;
		if (!$doProc && !$skipText && $thisTag !== 'suspend') $out .= $keyStart;
		$pos++;
	}

	if (sizeof($status))
	{
		// only find all error messages and add the final 'incomplete' error
		$err = '';
		while (preg_match('/(__error.*?__(?!error))(.*)$/s', $out, $m))
		{
			$err .= $m[1] . ' ';
			$out = $m[2];
		}
		$err .= '__error:incomplete:' . join(',', $status) . '__';
		// return the error report
		return $err;
	}

	return $out;
} // function UteCompile

// Parse a prefix expression from an array
//
// See UteParseExpressionRec function for details
//
function UteParseExpression($parts)
{
	$epos = -1;
	return UteParseExpressionRec($parts, $epos);
}

// Fill parameters into an expression template
//
// in/out parts = (array) all expression parts
// in/out pos = (int) current part's position
// in template = (string) operator template, with \1,\2,\3... for each parameter
//
// returns (string) PHP expression code, or "__error__" on error
//
function UteExpressionTemplate(&$parts, &$pos, $template)
{
	$n = 1;
	while (strpos($template, chr($n)) !== false)
	{
		$operand = UteParseExpressionRec($parts, $pos);
		if (substr($operand, 0, 7) === '__error') return $operand;
		$template = str_replace(chr($n), $operand, $template);
		$n++;
	}
	return $template;
}

function cb_specials($matches) {
	$specials = [
		'_\\n_' => "\n",
		'_\\r_' => "\r",
		'_\\t_' => "\t",
		'_\\\\_' => '\\',
		'_"_' => '\\"',
	];

	return $specials[$matches[0]];
}

function cb_hexdec($matches) {
	return UteCodeUTF(hexdec($matches[1]));
}


// Recursively parse a prefix expression from an array
//
// This function is required for the Template Compiler only.
//
// in/out parts = (array) all expression parts
// in/out pos = (int) current part's position
//
// returns (string) parsed PHP code
// uses $UTE
//
function UteParseExpressionRec(&$parts, &$pos)
{
	global $UTE;

	// remove empty parts
	$count = sizeof($parts);
	do
	{
		$pos++;
		$p = $parts[$pos];
	}
	while (trim($p) === '' && $pos < $count);
	if ($pos >= $count) return null;   // no more expressions

	// numeric_data
	if (is_numeric($p))
		return $p;

	// string_data
	if (preg_match('/^"(.*)"$/', $p, $m))
	{
		return '"' .
			preg_replace_callback_array(
				[
					'_\\n_' => 'cb_specials',
					'_\\r_' => 'cb_specials',
					'_\\t_' => 'cb_specials',
					'_\\\\_' => 'cb_specials',
					'_"_' => 'cb_specials',
					'_\\\\x([0-9a-f]{2})_i' => 'cb_hexdec',
					'_\\\\u([0-9a-f]{4})_i' => 'cb_hexdec'
				],
				$m[1]
			) . '"';
	}

	// variable
	if (preg_match('/^\$([a-z_][a-z0-9_]*)?(?:\.([a-z0-9_]+)(?:\.([a-z0-9_]+))?)?$/i', $p, $m))   // $var.a.b
	{
		// NOTE: Even all simple variable requests must go through UteVariable() to allow using
		//       anonymous local loop variables in foreach loops through includes of other templates.
		if (!$UTE['__useVarFunction'])
		{
			return '$UTE[\'' . $m[1] . '\']' .
				(isset($m[2]) ? ('[\'' . $m[2] . '\']') : '');
		}
		else
		{
			return 'UteVariable(\'' . $m[1] . '\'' .
				(isset($m[2]) ? ', \'' . $m[2] . '\'' : '') .
				(isset($m[3]) ? ', \'' . $m[3] . '\'' : '') .
				')';
		}
	}

	// const
	if (preg_match('/^%([a-z_][a-z0-9_]*)$/i', $p, $m))
	{
		return '(defined(\'' . $m[1] . '\') ? constant(\'' . $m[1] . '\') : \'\')';
	}

	// internal_function_name
	if ($p === 'include' && $pos === 0)
	{
		// use runtime API function to include (and first compile) a template file
		$e = UteParseExpressionRec($parts, $pos);
		if (substr($e, 0, 7) === '__error') return '__error:include' . $e . '__';
		$saveEnv = UteParseExpressionRec($parts, $pos);
		if (isset($saveEnv)) $saveEnv = ', ' . $saveEnv;
		return 'UteInclude(' . $e . $saveEnv . ')';
	}
	if ($p === 'tr')
	{
		$exps = array();
		while (true)
		{
			$e = UteParseExpressionRec($parts, $pos);
			if (substr($e, 0, 7) === '__error') return '__error:tr' . $e . '__';
			if ($e === null || $e === '""') break;
			$exps[] = $e;
		}
		return 'UteTranslate(' . join(', ', $exps) . ')';
	}
	if ($p === 'trnum')
	{
		$exps = array();
		while (true)
		{
			$e = UteParseExpressionRec($parts, $pos);
			if (substr($e, 0, 7) === '__error') return '__error:tr' . $e . '__';
			if ($e === null || $e === '""') break;
			$exps[] = $e;
		}
		return 'UteTranslateNum(' . join(', ', $exps) . ')';
	}

	if (array_key_exists($p, $UTE['__registeredFunctions']))
	{
		// process function call
		$func = $UTE['__registeredFunctions'][$p];
		if (is_string($func))
		{
			$template = $func;
		}
		else
		{
			// create default template
			if (!isset($func[1])) $func[1] = $p;
			$template = $func[1] . '(';
			for ($n = 1; $n <= $func[0]; $n++)
				$template .=
					($n === 1 ? '' : ', ') .
					chr($n);
			$template .= ')';
		}
		return UteExpressionTemplate($parts, $pos, $template);
	}

	return '__error:expr__';
} // function UteParseExpressionRec


// Same as PHP function explode(), but also groups by quotes
//
// Attention: Highly optimised code ahead!
//
// in sep = (string) separator character, see PHP explode()
// in str = (string) string to split, see PHP explode()
// in mask_bs = (bool) take case of \ characters, and ignore \" f.ex. [currently ignored to true]
// in keep = (bool) keep the quotes around quoted parts
//
// returns (array) Array of parts
//
function UteExplodeQuoted($sep, $str, $mask_bs = true, $keep = false)
{
	$out = array();
	$len = strlen($str);
	$instr = false;   // are we currently inside a string?
	$pos = 0;         // current starting position
	$startpos = 0;    // remember last part beginning
	$pos_s = -1;      // position of ' ' (space) / separator
	$pos_q = -1;      // position of '"' (quote)

	$keep = $keep ? 1 : 0;   // keep " quotes

	while ($pos < $len)
	{
		if ($pos_s < $pos)   // only search if last result is before current position
			if (($pos_s = strpos($str, $sep, $pos)) === false) $pos_s = $len;
				// set pointer to end of string, if symbol was not found
		if ($pos_q < $pos)
			if (($pos_q = strpos($str, '"', $pos)) === false) $pos_q = $len;

		$minpos = min($pos_s, $pos_q);   // find the nearest interesting symbol

		if ($instr === false && $minpos === $pos_s)   // found a space/separator first (not inside a string)
		{
			$out[] = str_replace(
				'\\"',
				'"',
				substr($str, $startpos, $minpos - $startpos));
			$startpos = $pos = $minpos + 1;
		}
		elseif ($minpos === $pos_q)   // found a quote first
		{
			if ($minpos === $startpos && $instr === false)   // first symbol of this part AND not inside a string (must be so)
			{
				$instr = true;
				$startpos = $pos = $minpos + 1;   // jump over beginning "
				$startpos -= $keep;
			}
			elseif ($instr === true && $str{$minpos - 1} !== '\\' && ($str{$minpos + 1} === $sep
			                                                          || $minpos === $len - 1))
				// inside a string AND previous symbol is not \ AND next symbol is space/separator
				//                                                  OR no more characters (last symbol)
			{
				$instr = false;
				$out[] = str_replace(
					'\\"',
					'"',
					substr($str, $startpos, $minpos - $startpos + $keep));
				$startpos = $pos = $minpos + 2;   // jump over quote + separator
			}
			/*
			elseif ($instr === false && $str{$minpos - 1} === '\\')
				// not inside a string AND previous symbol is \
			*/
			else   // found something of no interest
			{
				$pos = $minpos + 1;
			}
		}
		else   // found something of no interest
		{
			$pos = $minpos + 1;
		}
	}

	// Something left?
	if ($startpos < $pos)
	{
		$out[] = str_replace(
			'\\"',
			'"',
			substr($str, $startpos, $pos - $startpos));
	}

	return $out;
}

?>
