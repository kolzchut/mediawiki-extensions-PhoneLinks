<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'WikiRights/PhoneLinks' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PhoneLinks'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for PhoneLinks extension. Please use wfLoadExtension ' .
		'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'This version of the PhoneLinks extension requires MediaWiki 1.25+' );
}
