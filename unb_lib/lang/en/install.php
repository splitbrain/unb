<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : English (en)
// Part     : install
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20051028
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

// Installation
$UNB_T['installation'] = 'Installation';
$UNB_T['inst.db server'] = 'Database server';
$UNB_T['inst.db user'] = 'Username';
$UNB_T['inst.db pass'] = 'Password';
$UNB_T['inst.db name'] = 'Database name';
$UNB_T['inst.db prefix'] = 'Table name prefix';
$UNB_T['inst.db prefix~'] = 'This key is added to the standard table names. Use different keys to store more than one forum in a single database.';

$UNB_T['inst.welcome'] = 'Welcome to the UNB installation!';
$UNB_T['inst.assistant'] = 'This assistent will install the Unclassified NewsBoard version ' . $UNB['Version'] . ' to the current location. This primarily means initialising the database, obviously the files are already unpacked. Please select the correct language now or you will have to rename the default user groups later.';
$UNB_T['inst.warn nowrite'] = 'Warning: Writing to the configuration file is not possible.<br />You should solve the problem by changing the following directories’ access rights with your FTP programme (CHMOD):<br />Board’s main directory, board.conf.php (if present), unb_lib/logs/, unb_lib/upload/, unb_lib/rsscache/, unb_lib/designs/*/tpl/cache/.';
$UNB_T['inst.type'] = 'Type of installation';
$UNB_T['inst.type.new'] = 'New installation';
$UNB_T['inst.type.new~'] = 'At a new installation, you create a blank set of tables in the database. No old data or files is converted or saved. All information stored in tables with the same name will be lost, if they already exist.';
$UNB_T['inst.type.update'] = 'Update from older UNB version';
$UNB_T['inst.type.update~'] = 'Updating means converting the database from a previous version of the Unclassified NewsBoard. Installation must be performed on the same database and table prefix as the previous installation operated on. All necessary changes to the database will be performed automatically.';
$UNB_T['inst.type.import'] = 'Import from another newsboard';
$UNB_T['inst.type.import~'] = 'Importing copies all compatible data from another newsboard’s data structure into a new UNB installation. Depending on the newsboard, not all information may be copied correctly. You most probably have to check all access rights first.';
$UNB_T['inst.type.uninstall'] = 'Uninstall the board';
$UNB_T['inst.type.uninstall~'] = 'Remove all database tables of the currently installed UNB. All stored information will be lost. You have to remove the board files (code, designs, user uploads) yourself afterwards.';
$UNB_T['inst.board title'] = 'Forum title';
$UNB_T['inst.board title~'] = 'This title will be used in the page title and as sender of e-mails.';
$UNB_T['inst.continue'] = 'Continue installation';

$UNB_T['inst.create tables'] = 'Creating database tables...';
$UNB_T['inst.grp guests'] = 'Guests';
$UNB_T['inst.grp users'] = 'Members';
$UNB_T['inst.grp gmods'] = 'Global Moderators';
$UNB_T['inst.grp admins'] = 'Administrators';
$UNB_T['inst.login'] = 'Logging in as user "Admin" with password "admin"...';
$UNB_T['inst.done'] = 'Done.';
$UNB_T['inst.db complete'] = 'Database setup complete.';
$UNB_T['inst.complete~'] = 'You should first change your username and password in your user profile. To complete the installation, go to the control panel and adjust the board settings to your needs. Then, create the forums you wish to offer to your users and set all access rights accordingly. At the end, turn off the administrative board lock in the control panel.';
$UNB_T['inst.upgrade~'] = 'Please check the database from your Admin Control Panel and repair any errors as necessary to be sure the database is in a consistent state.';
$UNB_T['inst.lock'] = 'The installation programs are now locked and cannot be started as long as the file “lock.conf” exists in this directory. Nevertheless, you should also remove the setup scripts (“install.php” and “import_*.php”) from the board’s directory to prevent any misuse.';
$UNB_T['inst.go cp'] = 'Go on to the control panel';
$UNB_T['inst.go overview'] = 'Go on to the forums overview page';

$UNB_T['inst.check db layout'] = 'Checking current database layout...';
$UNB_T['inst.check db integrity'] = 'Checking database integrity...';

$UNB_T['inst.import~'] = 'Select the board software you want to convert from. To add converters to this list, they must be named “import_*.php” and located in the same directory as “install.php”.';
$UNB_T['inst.import.not found'] = 'No import modules found.';

$UNB_T['inst.error.upgrade fatal error'] = 'A fatal error occured while updating the database and the update script cannot be continued. The database is now probably inconsistent and should be replaced by a backup copy. Please note down the following error message and send it to the UNB developers. A more detailled error report can be found in the board’s error log.';

// Uninstallation
$UNB_T['uninstallation'] = 'Uninstallation';
$UNB_T['uninst.sure'] = 'Sure to delete all tables from the database!';
$UNB_T['uninst~'] = 'This will only affect data stored in the database. No files will be deleted.';
$UNB_T['uninst.summary'] = 'Removing these database tables:';
$UNB_T['uninst.remove table'] = 'Removing table';
$UNB_T['uninst.complete'] = 'Uninstall complete.';

?>