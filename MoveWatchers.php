<?php
# Alert the user that this is not a valid access point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install MoveWatchers, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/MoveWatchers/MoveWatchers.php" );
EOT;
	exit( 1 );
}
 
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'MoveWatchers',
	'author' => 'TyA <tyler@faceyspacies.com>',
	'url' => 'https://www.mediawiki.org/wiki/Extension:MoveWatchers',
	'descriptionmsg' => 'movewatchers-desc',
	'version' => '0.0.0',
);

$wgGroupPermissions['sysop']['movewatchers'] = true;
$wgGroupPermissions['*']['movewatchers'] = false;
 
$wgAutoloadClasses['SpecialMoveWatchers'] = __DIR__ . '/SpecialMoveWatchers.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgMessagesDirs['MoveWatchers'] = __DIR__ . "/i18n"; # Location of localisation files (Tell MediaWiki to load them)
$wgExtensionMessagesFiles['MoveWatchersAlias'] = __DIR__ . '/MoveWatchers.alias.php'; # Location of an aliases file (Tell MediaWiki to load it)
$wgSpecialPages['MoveWatchers'] = 'SpecialMoveWatchers'; # Tell MediaWiki about the new special page and its class name