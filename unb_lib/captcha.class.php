<?php
// UNB Components
// Copyright since 2007 by Yves Goergen
// Website: http://unclassified.de/go/unb2
// Licence: GNU General Public License (GPL) Version 3
//
// This file contains the UnbCaptcha class.
//
// This version was modified for use in UNB 1.x. See <http://newsboard.unclassified.de>.

// Completely Automated Public Turing test to tell Computers and Humans Apart, using text in an image.
//
class UnbCaptcha
{
	// Configuration array. Should always be retrieved through the GetConfiguration method.
	private static $config = null;

	// Private constructor, does nothing but preventing an unwanted instantiation of this class.
	//
	private function __construct()
	{
	}

	// Gets the CAPTCHA configuration array.
	//
	// If no configuration was set yet with SetConfiguration, a default array is returned. This
	// will not be stored in the private member variable.
	//
	public static function GetConfiguration()
	{
		if (isset(self::$config)) return self::$config;

		$C = array();

		$C['imgWidth'] = 172;
		$C['imgHeight'] = 47;

		$C['backRLo'] = 230;
		$C['backRHi'] = 250;
		$C['backGLo'] = 230;
		$C['backGHi'] = 250;
		$C['backBLo'] = 230;
		$C['backBHi'] = 250;

		$C['fontPath'] = 'fonts/';   // Relative to this code file
		$C['knownFonts'] = array(
			'Vera.ttf',
			'VeraBd.ttf',
			);

		$C['sizeLo'] = 19;
		$C['sizeHi'] = 26;
		$C['angleLo'] = -9;
		$C['angleHi'] = 9;
		$C['foreRLo'] = 50;
		$C['foreRHi'] = 80;
		$C['foreGLo'] = 50;
		$C['foreGHi'] = 80;
		$C['foreBLo'] = 70;
		$C['foreBHi'] = 140;

		$C['hLines'] = 12;
		$C['hLineOpacLo'] = 70;
		$C['hLineOpacHi'] = 100;
		$C['vLines'] = 12;
		$C['vLineOpacLo'] = 70;
		$C['vLineOpacHi'] = 100;
		$C['rLines'] = 16;
		$C['rLineOpacLo'] = 70;
		$C['rLineOpacHi'] = 100;

		$C['negative'] = false;

		$C['wordsFileName'] = 'captcha_words_en.txt';   // Relative to this code file

		$C['symbols'] = '"#%*-:;=^_';

		$C['combineLetters'] = array(
			'A' => 'ABCDEFGHI KLMNOPQRSTU WX Z"#%*-:;=^_',
			'B' => 'ABCDEFGHI KLMNOPQRSTUVWXYZ"#%*-:;=^_',
			'C' => 'ABCDEFGH  KLMNOPQRS UVWXYZ #%*    ^ ',
			'D' => 'ABCDEFGHI KLMNOPQRSTUVWXYZ"#%* :;=^_',
			'E' => 'ABCDEFGH  KLMNOPQRS UVWX    %* :; ^ ',
			'F' => 'ABCDEFGH  KLMNOPQRS UVW YZ #        ',
			'G' => 'ABCDEFGH  KLMNOPQRSTUVWXYZ #%  :;  _',
			'H' => 'ABCDEFGH  KLMNOPQRS UVWXYZ"#%* :;=^_',
			'I' => 'A C   G       O Q S    X Z" %       ',
			'J' => '  C   G       O           "  *      ',
			'K' => 'ABCDEFGHI KLMNOPQRSTUVWXYZ" %* :; ^_',
			'L' => 'A                      X   #%      _',
			'M' => 'ABCDEFGH  KLMNOPQRS UVWXYZ"#%*-:;=^_',
			'N' => 'ABCDEFGH  KLMNOPQRS U  XYZ"#%*-:;=^_',
			'O' => 'ABCDEFGHI KLMNOPQRSTUVWXYZ"#%*-:;=^_',
			'P' => 'ABCDEFGH  KLMNOPQRSTUVWXYZ"#%*    ^ ',
			'Q' => 'ABCDEFGHI KLMNOPQRSTUVWXYZ"#%*-  =^ ',
			'R' => 'ABCDEFGH  KLMNOPQRSTUVWXYZ"#%* :;=^ ',
			'S' => 'ABCDEFGHI KLMNOPQRSTUVWXYZ #%* :;=^_',
			'T' => 'A C   G   K   O Q S UVW             ',
			'U' => 'ABCDEFGH  KLMNOPQRS U WXYZ"#%*-:;= _',
			'V' => ' BCDEFGH  K MNOPQRST   XYZ"#%* :;=^ ',
			'W' => ' BCDEFGH  KLMNOPQRS U WXYZ"#%*-:;=^_',
			'X' => 'AB DEF HI KLMN P RSTUVW  Z"#%*   =^ ',
			'Y' => ' B DEF H  KLMN P RSTUVW  Z"#%*    ^ ',
			'Z' => 'ABCDEFGH  KLMNOPQRS UVWXYZ"#%*    ^_',
			'"' => 'ABCDEFGHI KLMNOPQRS UVWXY           ',
			'#' => 'ABCDEFGH  KLMNOPQRS UVWXY           ',
			'%' => 'ABCDEFGHI KLMNOPQRS UVWXYZ          ',
			'*' => 'ABCDEFGHI KLMNOPQRS UVWXYZ          ',
			'-' => 'ABCDEFGH  KLMNOPQR  U W             ',
			':' => 'ABCDEFGH  KLMNOPQRS UVW             ',
			';' => 'ABCDEFGH  KLMNOPQRS UVW             ',
			'=' => 'ABCDEFGHI KLMNOPQRS UVWX            ',
			'^' => 'ABCDEFGH  KLMNOPQRS  VWXYZ          ',
			'_' => 'ABCDEFGH  KLMNOPQRS UVWXYZ          ',
			);
		return $C;
	}

	// Sets a new CAPTCHA configuration array.
	//
	// config = (array) New configuration data
	//
	public static function SetConfiguration($config)
	{
		if (!is_array($config))
			throw new InvalidArgumentException('Invalid argument type: $config');

		self::$config = $config;
	}

	// Generates the CAPTCHA image, sends it to the browser and terminates the script execution.
	//
	// The correct word displayed in the image is stored in a session variable and can later be
	// retrieved with the GetWord() function.
	//
	public static function GenerateImage()
	{
		$C = self::GetConfiguration();

		// Read words file
		$wordsFileName = $C['wordsFileName'];
		if ($wordsFileName{0} !== '/')
			$wordsFileName = dirname(__FILE__) . '/' . $wordsFileName;
		$wordsStr = trim(preg_replace('_#.*?\\n_', '', file_get_contents($wordsFileName)));

		// Convert the long string into an array of uppercase words
		$wordsStr = preg_replace('_[ \t\r\n,;]+_', ' ', $wordsStr);
		$words = explode(' ', $wordsStr);
		$wordsStr = null;   // free memory
		$words = array_map('trim', $words);
		$words = array_map('strtoupper', $words);

		$text = self::GetWord();
		if (!isset($text) || !strlen($text))
		{
			// Select one word randomly
			$text = $words[mt_rand(0, count($words) - 1)];

			// Put some symbols in between
			$addLength = min(2, mt_rand(6, 8) - strlen($text));   // Add no more than 2 symbols, have at least 6 chars in the end
			if ($addLength < 1) $addLength = 1;
			$wordStart = 0;
			$wordEnd = strlen($text) - 1;
			for ($i = $addLength; $i > 0; $i--)
			{
				while (true)
				{
					$pos = mt_rand(0, strlen($text));
					// Don't allow two symbols right next to each other inside the word (outside would be okay)
					if ($pos <= $wordStart || $pos > $wordEnd ||
						ctype_alpha($text{$pos - 1}) && ctype_alpha($text{$pos})) break;
				}
				$a = substr($text, 0, $pos);
				$b = substr($text, $pos);

				$text = $a . $C['symbols']{mt_rand(0, strlen($C['symbols']) - 1)} . $b;
				if ($pos <= $wordStart) $wordStart++;
				if ($pos <= $wordEnd) $wordEnd++;
			}

			$_SESSION['veriword'] = $text;
		}

		// Create image with solid background colour
		$imgWidth = $C['imgWidth'];
		$imgHeight = $C['imgHeight'];
		$im = imagecreatetruecolor($imgWidth, $imgHeight);
		$backColor = array(mt_rand($C['backRLo'], $C['backRHi']), mt_rand($C['backGLo'], $C['backGHi']), mt_rand($C['backBLo'], $C['backBHi']));
		imagefilledrectangle($im, 0, 0, $imgWidth, $imgHeight, imagecolorallocate($im, $backColor[0], $backColor[1], $backColor[2]));

		// Find available fonts
		$fontPath = $C['fontPath'];
		if ($fontPath{0} !== '/')
			$fontPath = dirname(__FILE__) . '/' . $fontPath;
		$fonts = array();
		foreach ($C['knownFonts'] as $knownFont)
		{
			if (file_exists($fontPath . '/' . $knownFont))
				$fonts[] = $fontPath . '/' . $knownFont;
		}
		if (!count($fonts))
			$fonts = glob($fontPath . '/*.ttf');

		// Draw text characters
		$color = array(mt_rand($C['foreRLo'], $C['foreRHi']), mt_rand($C['foreGLo'], $C['foreGHi']), mt_rand($C['foreBLo'], $C['foreBHi']));
		$x = 5 + max(0, 8 - strlen($text)) * mt_rand(5, 15);
		$prevAngle = 0;
		$bbox = array(0, 0, 0);
		for ($i = 0; $i < strlen($text); $i++)
		{
			$fontfile = $fonts[mt_rand(0, count($fonts) - 1)];
			$size = mt_rand($C['sizeLo'], $C['sizeHi']);
			$angle = (mt_rand($C['angleLo'], $C['angleHi']) + $prevAngle) / 2;

			// Make sure random borders are in the right order
			$min = -($imgHeight / 2 - $size);
			$max = $imgHeight / 2 - $size * 1.3;
			if ($min > $max) list($min, $max) = array($max, $min);

			$x += round(($bbox[2] - $bbox[0]) * 0.8 - ($angle - $prevAngle) / 20 * 0.2) + 2;
			$y =
				/* middle */ $imgHeight / 2 + $size / 2 + 3 +
				/* deviation */ mt_rand($min, $max);
			$ch = $text{$i};

			$bbox = imagettfbbox($size, $angle, $fontfile, $ch);

			// Move back to the left until the new character overlaps any other character
			if ($i == 0 || $i > 0 && strpos($C['combineLetters'][$text{$i - 1}], $text{$i}) === false)
			{
				// Invalid character sequences are not glues together for better readability.
				$x += 2;
			}
			else
			{
				$overlaps = false;
				$im2 = imagecreatetruecolor($imgWidth, $imgHeight);
				$startX = $x;
				while (!$overlaps && $x > 0 && $x > $startX - 10)
				{
					$x--;

					imagefill($im2, 0, 0, imagecolorallocate($im2, 255, 255, 255));
					imagettftext($im2, $size, $angle, $x, $y,
						imagecolorallocate($im2, 0, 0, 0),
						$fontfile, $ch);

					$o_count = 0;
					$max_o_x = min($imgWidth, $x + 40);
					$max_o_y = $imgHeight - 1;
					for ($o_y = 0; $o_y < $max_o_y; $o_y++)
					{
						for ($o_x = $x - 10; $o_x < $max_o_x; $o_x++)
						{
							$c1 = imagecolorat($im, $o_x, $o_y);
							$c2 = imagecolorat($im2, $o_x, $o_y);

							$c1 = (($c1 >> 16) & 0xFF) + (($c1 >> 8) & 0xFF) + ($c1 & 0xFF);
							$c2 = (($c2 >> 16) & 0xFF) + (($c2 >> 8) & 0xFF) + ($c2 & 0xFF);
							if ($c1 + $c2 < $C['foreRHi'] + $C['foreGHi'] + $C['foreBHi'] + 20 * 3)
								$o_count++;

							if ($o_count >= 2)
							{
								$overlaps = true;
								$o_y = $imgHeight;
								$o_x = $imgWidth;
							}
						}
					}
				}
				imagedestroy($im2);
			}
			$x++;

			imagettftext($im, $size, $angle, $x, $y,
				imagecolorallocate($im, $color[0], $color[1], $color[2]),
				$fontfile, $ch);

			$prevAngle = $angle;
		}

		// Translate some regions vertically
		for ($i = 0; $i < 8; $i++)
		{
			$x1 = mt_rand(0, $imgWidth - 5);
			$x2 = mt_rand($x1 + 1, $imgWidth - 1);

			$imBak = imagecreatetruecolor($imgWidth, $imgHeight);
			imagecopy($imBak, $im, 0, 0, 0, 0, $imgWidth, $imgHeight);
			if (mt_rand(0, 9) >= 5)
			{
				imagecopy($im, $imBak, $x1, 0, $x1, 1, $x2 - $x1, $imgHeight - 1);
			}
			else
			{
				imagecopy($im, $imBak, $x1, 1, $x1, 0, $x2 - $x1, $imgHeight - 1);
			}
			imagedestroy($imBak);
		}

		// Unsharpen image with text again
		$matrix = array(
			array(0, 1, 0),
			array(1, 3, 1),
			array(0, 1, 0));
		$div = 7;
		if (function_exists('imageconvolution'))
			imageconvolution($im, $matrix, $div, 0);
		else
			self::php_imageconvolution($im, $matrix, $div, 0);

		// Add pixel noise
		for ($i = 0; $i < 100; $i++)
		{
			$x = mt_rand(0, $imgWidth - 1);
			$y = mt_rand(0, $imgHeight - 1);

			imagesetpixel($im, $x, $y,
				imagecolorallocate($im, $color[0], $color[1], $color[2]));
		}

		// Add horizontal lines noise
		for ($i = 0; $i < $C['hLines']; $i++)
		{
			$x1 = mt_rand(0, $imgWidth / 2);
			$y1 = mt_rand(0, $imgHeight - 1);
			$x2 = mt_rand($imgWidth / 2, $imgWidth - 1);
			$y2 = $y1;

			imageline($im, $x1, $y1, $x2, $y2,
				imagecolorallocatealpha($im, $backColor[0], $backColor[1], $backColor[2], mt_rand($C['hLineOpacLo'], $C['hLineOpacHi'])));
		}

		// Add vertical lines noise
		for ($i = 0; $i < $C['vLines']; $i++)
		{
			$x1 = mt_rand(0, $imgWidth - 1);
			$y1 = 0;
			$x2 = $x1;
			$y2 = $imgHeight;

			imageline($im, $x1, $y1, $x2, $y2,
				imagecolorallocatealpha($im, $backColor[0], $backColor[1], $backColor[2], mt_rand($C['vLineOpacLo'], $C['vLineOpacHi'])));
		}

		// Add random lines noise
		for ($i = 0; $i < $C['rLines']; $i++)
		{
			$x1 = mt_rand(0, $imgWidth - 1);
			$y1 = 0;
			$x2 = mt_rand(0, $imgWidth - 1);
			$y2 = $imgHeight;

			imageline($im, $x1, $y1, $x2, $y2,
				imagecolorallocatealpha($im, $backColor[0], $backColor[1], $backColor[2], mt_rand($C['rLineOpacLo'], $C['rLineOpacHi'])));
		}

		if ($C['negative'])
		{
			imagefilter($im, IMG_FILTER_NEGATE);
		}

		// Output image
		header('Pragma: no-cache');
		header('Cache-Control: no-cache');
		header('Expires: 0');
		header('Content-Type: image/jpeg');
		imagejpeg($im, null, 90);
		exit();
	}

	// Gets the word used in the last displayed image in this session.
	//
	// Returns: (string) CAPTCHA word
	//
	public static function GetWord()
	{
		return $_SESSION['veriword'];
	}

	// Normalises a word, removes non-letters, converts to upper case.
	//
	// text = (string) Input text
	//
	// Returns (string) Normalised text
	//
	private static function NormaliseWord($text)
	{
		$out = '';
		for ($i = 0; $i < strlen($text); $i++)
		{
			$ch = $text{$i};
			if (ctype_alpha($ch))
				$out .= $ch;
		}
		return strtoupper($out);
	}

	// Checks the specified user input to determine whether it is correct or not.
	//
	// tryWord = (string) Input word
	//
	// Returns (bool) {{true}} if it is correct, {{false}} otherwise
	//
	public static function CheckWord($tryWord)
	{
		$tryword = strtoupper(trim($tryWord));

		// Don't do anything if no word has been entered
		if (!strlen($tryword))
			return false;

		$word = self::NormaliseWord(self::GetWord());
		if ($tryword == $word)
			return true;

		// Always clear the word when it was entered incorrectly
		self::ClearWord();
		return false;
	}

	// Clears the last word.
	//
	// Normally, a word is determined at dice and kept as long as it is entered incorrectly or
	// not required anymore, i.e. when the secured transaction has completed successfully. Be sure
	// to invoke this method every time you don't need a word anymore.
	//
	public static function ClearWord()
	{
		$_SESSION['veriword'] = null;
	}

	// Compatibility function for GD libraries not bundled with PHP.
	//
	// Note: This function was copied unmodified from an external source. It may not meet the
	// common source code standards.
	//
	// include this file whenever you have to use imageconvolution...
	// you can use in your project, but keep the comment below :)
	// great for any image manipulation library
	// Made by Chao Xu(Mgccl) 2/28/07
	// www.webdevlogs.com
	// V 1.0
	private static function php_imageconvolution($src, $filter, $filter_div, $offset)
	{
		if ($src==NULL) {
			return 0;
		}

		$sx = imagesx($src);
		$sy = imagesy($src);
		$srcback = ImageCreateTrueColor ($sx, $sy);
		ImageCopy($srcback, $src,0,0,0,0,$sx,$sy);

		if($srcback==NULL){
			return 0;
		}

		$pxl = array(1,1);

		for ($y=0; $y<$sy; ++$y){
			for($x=0; $x<$sx; ++$x){
				$new_r = $new_g = $new_b = 0;
				$alpha = imagecolorat($srcback, $pxl[0], $pxl[1]);
				$new_a = $alpha >> 24;

				for ($j=0; $j<3; ++$j) {
					$yv = min(max($y - 1 + $j, 0), $sy - 1);
					for ($i=0; $i<3; ++$i) {
							$pxl = array(min(max($x - 1 + $i, 0), $sx - 1), $yv);
						$rgb = imagecolorat($srcback, $pxl[0], $pxl[1]);
						$new_r += (($rgb >> 16) & 0xFF) * $filter[$j][$i];
						$new_g += (($rgb >> 8) & 0xFF) * $filter[$j][$i];
						$new_b += ($rgb & 0xFF) * $filter[$j][$i];
					}
				}

				$new_r = ($new_r/$filter_div)+$offset;
				$new_g = ($new_g/$filter_div)+$offset;
				$new_b = ($new_b/$filter_div)+$offset;

				$new_r = ($new_r > 255)? 255 : (($new_r < 0)? 0:$new_r);
				$new_g = ($new_g > 255)? 255 : (($new_g < 0)? 0:$new_g);
				$new_b = ($new_b > 255)? 255 : (($new_b < 0)? 0:$new_b);

				$new_pxl = ImageColorAllocateAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
				if ($new_pxl == -1) {
					$new_pxl = ImageColorClosestAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
				}
				if (($y >= 0) && ($y < $sy)) {
					imagesetpixel($src, $x, $y, $new_pxl);
				}
			}
		}
		imagedestroy($srcback);
		return 1;
	}

} // class UnbCaptcha

?>
