<?php
/**
 * UiFeedback Extension for MediaWiki.
 *
 * @file
 * @ingroup Extensions
 *
 * @license GPL v2 or later
 * @version 0.2
 */

$wgExtensionCredits['other'][] = array(
    'path' => __FILE__,
    'name' => 'UiFeedback',
    'version' => '0.00001',
    'url' => 'TODO',
    'author' => array('LB',),
    'descriptionmsg' => 'ui-feedback-desc'
);

/* Setup */

// Register files
$wgExtensionMessagesFiles['UiFeedback'] = __DIR__ . '/UiFeedback.i18n.php';

// add permissions and groups
//$wgGroupPermissions['user']['userrights'] = true;
//$wgGroupPermissions['user']['read_uifeedback'] = true;
$wgGroupPermissions['*']['read_uifeedback'] = true;
$wgGroupPermissions['UIFeedback_Administator']['write_uifeedback'] = true;

// Register modules
$wgResourceModules['ext.uiFeedback'] = array(
    'scripts' => array('resources/ext.uiFeedback.js', 'resources/ext.jquery.htmlfeedback.js', 'resources/ext.html2canvas.js'),
    'styles' => array('resources/ext.uiFeedback.css', 'resources/ext.htmlfeedback.css'),
    'dependencies' => array(
        'jquery.cookie',
        'jquery.ui.draggable',
    ),
    'messages' => array(
        'ui-feedback-headline',
        'ui-feedback-scr-headline',
        'ui-feedback-task-label',
        'ui-feedback-task-1', // search
        'ui-feedback-task-2', // item
        'ui-feedback-task-3', // label
        'ui-feedback-task-4', // description
        'ui-feedback-task-5', // alias
        'ui-feedback-task-6', // links
        'ui-feedback-task-7', // other

        'ui-feedback-done-label',

        'ui-feedback-good-label',
        'ui-feedback-bad-label',
        'ui-feedback-comment-label',

        'ui-feedback-happened-label',
        'ui-feedback-happened-1',
        'ui-feedback-happened-2',
        'ui-feedback-happened-3',
        'ui-feedback-happened-4',

        'ui-feedback-importance-label',
        'ui-feedback-importance-1',
        'ui-feedback-importance-5',

        'ui-feedback-anonym-label',
        'ui-feedback-anonym-help',
        'ui-feedback-notify-label',
        'ui-feedback-notify-help',

        'ui-feedback-problem-send',
        'ui-feedback-problem-reset',
        'ui-feedback-problem-close',
        'ui-feedback-problem-cancel',

        'ui-feedback-yes',
        'ui-feedback-no',

        'ui-feedback-highlight-label',
        'ui-feedback-yellow',
        'ui-feedback-black',
        'ui-feedback-help-text',

        'ui-feedback-prerender-text1',
        'ui-feedback-prerender-text2',
    ),
    'position' => 'top',
    'localBasePath' => __DIR__,
    'remoteExtPath' => 'UIFeedback',
);

# Schema updates for update.php
$wgHooks['LoadExtensionSchemaUpdates'][] = 'createUIFeedbackTable';
function createUIFeedbackTable(DatabaseUpdater $updater) {
    $updater->addExtensionTable('uifeedback',
        dirname(__FILE__) . '/table.sql', true);
    return true;
}

// Register hooks
$wgHooks['BeforePageDisplay'][] = 'uifeedbackBeforePageDisplay';
function uifeedbackBeforePageDisplay(&$out) {
    if( $out->getUser()->isAllowed( 'read_uifeedback' )){
        $out->addModules('ext.uiFeedback');
        $out->addModules('jquery.ui.draggable');
        return true;
    }
    return false;
}

// Register SpecialPage
$wgAutoloadClasses['SpecialUiFeedback'] = __DIR__ . '/SpecialUiFeedback.php'; # Location of the SpecialUIFeedback class (Tell MediaWiki to load this file)
$wgAutoloadClasses['SpecialUiFeedback_api'] = __DIR__ . '/SpecialUiFeedback_api.php'; # Location of the SpecialUIFeedback class (Tell MediaWiki to load this file)

$wgExtensionMessagesFiles['UiFeedback'] = __DIR__ . '/UiFeedback.i18n.php'; # Location of a messages file (Tell MediaWiki to load this file)
$wgExtensionMessagesFiles['UiFeedbackAlias'] = __DIR__ . '/UiFeedback.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)

$wgSpecialPages['UiFeedback'] = 'SpecialUiFeedback'; # Tell MediaWiki about the new special page and its class name
$wgSpecialPages['UiFeedback_api'] = 'SpecialUiFeedback_api'; # Tell MediaWiki about the new special page and its class name


$wgHooks['GetPreferences'][] = 'wfPrefHook';
function wfPrefHook( $user, &$preferences ) {
    // A checkbox
    $preferences['show_wb_postedit_notification'] = array(
        'type' => 'toggle',
        'label-message' => 'show the WikiData postedit notification for Feedback', // a system message
        'section' => 'misc',
    );

    // Required return value of a hook function.
    return true;
}