<?php
/**
 * This extension allows pages to add a popup guided tour to help new users
 * It uses the Guiders JavaScript library from Optimizely.
 * There are some local changes to Guiders at
 * https://gerrit.wikimedia.org/r/#/admin/projects/mediawiki/extensions/GuidedTour/guiders
 *
 * @file
 * @author Terry Chay tchay@wikimedia.org
 * @author Matthew Flaschen mflaschen@wikimedia.org
 * @author Luke Welling lwelling@wikimedia.org
 *
 */

/**
 * Prevent a user from accessing this file directly and provide a helpful
 * message explaining how to install this extension.
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install the Guided Tour extension, put the following line in your
LocalSettings.php file:
require_once( "\$IP/extensions/GuidedTour/GuidedTour.php" );
EOT;
	exit( 1 );
}

// Find the full directory path of this extension
$dir = __DIR__ . DIRECTORY_SEPARATOR;
$wgAutoloadClasses += array(
	'GuidedTourHooks' => $dir . 'GuidedTourHooks.php',
	'ResourceLoaderGuidedTourSiteStylesModule' =>
	$dir . 'includes/ResourceLoaderGuidedTourSiteStylesModule.php',
);

$wgHooks['BeforePageDisplay'][] = 'GuidedTourHooks::onBeforePageDisplay';
$wgHooks['MakeGlobalVariablesScript'][] = 'GuidedTourHooks::onMakeGlobalVariablesScript';
$wgHooks['ResourceLoaderTestModules'][] = 'GuidedTourHooks::onResourceLoaderTestModules';

// Extension credits that will show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'GuidedTour',
	'author' => array('Terry Chay', 'Matthew Flaschen', 'Luke Welling',),
	'url' => 'https://www.mediawiki.org/wiki/Extension:GuidedTour',
	'descriptionmsg' => 'guidedtour-desc',
	'version'  => 1.0,
);

$guidersPath = 'modules/externals/mediawiki.libs.guiders';

// Modules
$wgResourceModules['schema.GuidedTour'] = array(
	'class' => 'ResourceLoaderSchemaModule',
	'schema' => 'GuidedTour',
	'revision' => 5222838,
);

$wgResourceModules['mediawiki.libs.guiders'] = array(
	'styles' => 'mediawiki.libs.guiders.submodule/guiders-1.2.8.css',
	'scripts' => array(
		'mediawiki.libs.guiders.submodule/guiders-1.2.8.js',
		'mediawiki.libs.guiders.exposeGuiders.js',
	),
	'localBasePath' => $dir . $guidersPath,
	'remoteExtPath' => "GuidedTour/$guidersPath",
);

$wgResourceModules['ext.guidedTour.styles'] = array(
	'styles' => 'ext.guidedTour.css',
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'GuidedTour/modules',
	'dependencies' => 'mediawiki.libs.guiders',
);

// Depends on ext.guidedTour.styles
$wgResourceModules['ext.guidedTour.siteStyles'] = array(
	'class' => 'ResourceLoaderGuidedTourSiteStylesModule',
);

// The next two are separate to ease testability
// This provides all the real functionality.
$wgResourceModules['ext.guidedTour.lib'] = array(
	'scripts' => 'ext.guidedTour.lib.js',
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'GuidedTour/modules',
	'dependencies' => array(
		'jquery.cookie',
		'mediawiki.jqueryMsg',
		'mediawiki.libs.guiders',
		'mediawiki.util',
		'schema.GuidedTour',
		'ext.postEdit',
		'ext.guidedTour.siteStyles',
	),
	'messages' => array(
		'guidedtour-end-tour',
		'guidedtour-okay-button',
	),
);

// This calls code in guidedTour.lib to attempt to launch a tour, based on the environment
// (currently query string and a cookie)
$wgResourceModules['ext.guidedTour'] = array(
	'scripts' => 'ext.guidedTour.js',
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'GuidedTour/modules',
	'dependencies' => 'ext.guidedTour.lib',
);


// Tour modules

// Test tour as demonstration
$wgResourceModules['ext.guidedTour.tour.test'] = array(
	'scripts' => 'test.js',
	'localBasePath' => $dir . 'modules/tours',
	'remoteExtPath' => 'GuidedTour/modules/tours',
	'dependencies' => 'ext.guidedTour',
	'messages' => array(
		'portal',
		'guidedtour-help-url',
		'guidedtour-tour-test-testing',
		'guidedtour-tour-test-test-description',
		'guidedtour-tour-test-callouts',
		'guidedtour-tour-test-portal-description',
		'guidedtour-tour-test-mediawiki-parse',
		'guidedtour-tour-test-wikitext-description',
		'guidedtour-tour-test-description-page',
		'guidedtour-tour-test-go-description-page',
		'guidedtour-tour-test-launch-tour',
		'guidedtour-tour-test-launch-tour-description',
		'guidedtour-tour-test-launch-using-tours',
	),
);

// Tour designed to help new users edit.
// Can be used with Special:GettingStarted or independently.
$wgResourceModules['ext.guidedTour.tour.gettingstarted'] = array(
	'scripts' => 'gettingstarted.js',
	'localBasePath' => $dir . 'modules/tours',
	'remoteExtPath' => 'GuidedTour/modules/tours',
	'dependencies' => 'ext.guidedTour',
	'messages' => array(
		'savearticle',
		'showpreview',
		'vector-view-edit',
		'guidedtour-tour-gettingstarted-start-title',
		'guidedtour-tour-gettingstarted-start-description',
		'guidedtour-tour-gettingstarted-click-edit-title',
		'guidedtour-tour-gettingstarted-click-edit-description',
		'guidedtour-tour-gettingstarted-click-preview-title',
		'guidedtour-tour-gettingstarted-click-preview-description',
		'guidedtour-tour-gettingstarted-click-save-title',
		'guidedtour-tour-gettingstarted-click-save-description',
		'guidedtour-tour-gettingstarted-end-title',
		'guidedtour-tour-gettingstarted-end-description',
	),
);


// Messages
$wgExtensionMessagesFiles += array(
	'GuidedTour' => $dir . 'GuidedTour.i18n.php',
);
