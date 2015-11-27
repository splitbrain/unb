<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php
if(!isset($_GET['url'])) $_GET['url'] = 'http://www.google.com';
?>

<head>
	<title>Redirect</title>

	<base href="https://forum.dokuwiki.org/"/>
	<style type="text/css">/*<![CDATA[*/
		@import url(unb_lib/abbc.css.php);
		/*]]>*/</style>
	<link rel="stylesheet" href="unb_lib/designs/modern/css/common.css.php" type="text/css"/>
	<link rel="shortcut icon" href="unb_lib/designs/modern/img/favicon.ico" type="image/x-icon"/>

	<meta name="robots" content="noindex, follow"/>
</head>

<body class="simple">
<div class="forward_page" style="text-align: center; margin-top: 3em">

	<p>You're now leaving the DokuWiki user forums. Please follow this link if you want to continue:</p>


	<p><a href="<?php echo htmlspecialchars($_GET['url'])?>" style="font-size: 120%"><?php echo htmlspecialchars($_GET['url'])?></a>
	</p>


	<p style="margin-top: 4em">Use your browser's back button if you did not intend to visit the link shown above.</p>

	<p style="margin-top: 4em"><b>WARNING:</b><br /> When you're not coming from the DokuWiki forums, someone is probably trying to trick you.<br />You should close this browser window!</p>
</div>
</body>
</html>

