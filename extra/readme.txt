This directory contains additional files like import scripts or plug-ins. If
you want to use some of these files in your board, you need to copy them to the
following places:

/extra/import/* -> /
	(import scripts go right beside install.php)
/extra/plugins/* -> /unb_lib/plugins/
	(remember that some plug-ins may require templates to be copied into the
	design's template directory "tpl" or CSS stylesheets for the "css" directory)
/extra/smilies/* -> /unb_lib/designs/_smile/*
	(smilie sets are entire directories)

