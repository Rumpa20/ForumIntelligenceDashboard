<?php

/**
 * Forum Intelligence Dashboard - upgrade script.
 *
 * Version 1.0.0 is the first released version, so there is no earlier build
 * to migrate from. The script still performs the same idempotent work as the
 * installer (ensure table, ensure missing columns, ensure settings defaults)
 * so it can be run against any intermediate or unfinished 1.0.x installation
 * without duplicating data or resetting customized settings.
 *
 * Simple Machines Forum (SMF)
 *
 * @package ForumIntelligenceDashboard
 * @author rumpa
 * @copyright 2026 rumpa
 * @license see the included LICENSE.txt
 *
 * @version 1.0.0
 */

if (!defined('SMF'))
{
	if (file_exists(dirname(__FILE__) . '/../../SSI.php'))
		require_once(dirname(__FILE__) . '/../../SSI.php');
	elseif (file_exists(dirname(__FILE__) . '/../../../SSI.php'))
		require_once(dirname(__FILE__) . '/../../../SSI.php');
	else
		die('<b>Error:</b> Cannot upgrade - please verify you put this in the same place as SMF\'s index.php.');
}

global $smcFunc, $sourcedir;

// The package database functions and the data layer are both needed here.
db_extend('packages');

if (file_exists($sourcedir . '/ForumIntelligenceDashboard.subs.php'))
	require_once($sourcedir . '/ForumIntelligenceDashboard.subs.php');

// ---------------------------------------------------------------------------
// 1. Ensure the alerts table and the setting defaults exist. The installer is
//    idempotent, so requiring it here is safe and keeps one single schema
//    definition for the mod.
// ---------------------------------------------------------------------------

require(dirname(__FILE__) . '/install.php');

// ---------------------------------------------------------------------------
// 2. Ensure every alert table column exists (guards intermediate 1.0.x builds
//    that may have been created without the acknowledgement columns).
// ---------------------------------------------------------------------------

$fid_columns_expected = array(
	'ack_by' => array(
		'name' => 'ack_by',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
		'not_null' => true,
		'default' => 0,
	),
	'ack_time' => array(
		'name' => 'ack_time',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
		'not_null' => true,
		'default' => 0,
	),
);

$fid_columns_present = $smcFunc['db_list_columns']('{db_prefix}fid_alerts', false);
$fid_columns_present = array_flip($fid_columns_present);

foreach ($fid_columns_expected as $fid_column_name => $fid_column_info)
	if (!isset($fid_columns_present[$fid_column_name]))
		$smcFunc['db_add_column']('{db_prefix}fid_alerts', $fid_column_info, array(), 'ignore');

if (SMF === 'SSI')
	echo 'Forum Intelligence Dashboard upgrade completed.';

?>
