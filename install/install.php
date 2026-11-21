<?php

/**
 * Forum Intelligence Dashboard - installation script.
 *
 * Creates the {db_prefix}fid_alerts table and registers the mod settings.
 * The script is idempotent: running it again (for example on a failed then
 * retried install, or on a re-installation over a previous 1.0.0 copy) will
 * not duplicate the table, the indexes or any setting value that the
 * administrator has already customized.
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
		die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
}

global $smcFunc, $sourcedir;

// The package database functions (db_create_table, db_add_column, ...) are
// only loaded on demand.
db_extend('packages');

// The <require-file> actions run before this script, so the data layer
// should be in place. Fall back to a manual check with a clear message.
if (file_exists($sourcedir . '/ForumIntelligenceDashboard.subs.php'))
	require_once($sourcedir . '/ForumIntelligenceDashboard.subs.php');
elseif (!function_exists('fid_settings_defaults'))
	die('<b>Error:</b> ForumIntelligenceDashboard.subs.php was not found in the Sources directory. Verify that the package files were copied correctly.');

// ---------------------------------------------------------------------------
// 1. Alerts table.
// ---------------------------------------------------------------------------

$fid_columns = array(
	array(
		'name' => 'id_alert',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
		'auto' => true,
		'not_null' => true,
	),
	array(
		'name' => 'alert_key',
		'type' => 'varchar',
		'size' => 64,
		'not_null' => true,
		'default' => '',
	),
	array(
		'name' => 'severity',
		'type' => 'varchar',
		'size' => 20,
		'not_null' => true,
		'default' => 'medium',
	),
	array(
		'name' => 'title',
		'type' => 'varchar',
		'size' => 255,
		'not_null' => true,
		'default' => '',
	),
	array(
		'name' => 'message',
		'type' => 'text',
		'not_null' => true,
	),
	array(
		'name' => 'stats',
		'type' => 'text',
		'not_null' => true,
	),
	array(
		'name' => 'first_seen',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
		'not_null' => true,
		'default' => 0,
	),
	array(
		'name' => 'last_seen',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
		'not_null' => true,
		'default' => 0,
	),
	array(
		'name' => 'occurrences',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
		'not_null' => true,
		'default' => 1,
	),
	array(
		'name' => 'status',
		'type' => 'varchar',
		'size' => 10,
		'not_null' => true,
		'default' => 'new',
	),
	array(
		'name' => 'ack_by',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
		'not_null' => true,
		'default' => 0,
	),
	array(
		'name' => 'ack_time',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
		'not_null' => true,
		'default' => 0,
	),
);

$fid_indexes = array(
	array(
		'type' => 'primary',
		'columns' => array('id_alert'),
	),
	array(
		'type' => 'index',
		'name' => 'idx_fid_alert_key',
		'columns' => array('alert_key'),
	),
	array(
		'type' => 'index',
		'name' => 'idx_fid_alert_status',
		'columns' => array('status'),
	),
	array(
		'type' => 'index',
		'name' => 'idx_fid_alert_last_seen',
		'columns' => array('last_seen'),
	),
);

// 'ignore': if the table already exists (re-install) nothing is changed.
$smcFunc['db_create_table']('{db_prefix}fid_alerts', $fid_columns, $fid_indexes, array(), 'ignore');

// ---------------------------------------------------------------------------
// 2. Settings (defaults only - never overwrite customized values).
// ---------------------------------------------------------------------------

$fid_existing = array();
$fid_request = $smcFunc['db_query']('', '
	SELECT variable
	FROM {db_prefix}settings
	WHERE variable LIKE {string:pattern}',
	array(
		'pattern' => 'fid\_%',
	)
);
while ($fid_row = $smcFunc['db_fetch_assoc']($fid_request))
	$fid_existing[$fid_row['variable']] = true;
$smcFunc['db_free_result']($fid_request);

$fid_missing = array();
if (function_exists('fid_settings_defaults'))
	$fid_defaults = fid_settings_defaults();
else
	$fid_defaults = array();

foreach ($fid_defaults as $fid_name => $fid_value)
	if (!isset($fid_existing[$fid_name]))
		$fid_missing[$fid_name] = $fid_value;

if (!empty($fid_missing))
	updateSettings($fid_missing);

// ---------------------------------------------------------------------------
// 3. Done.
// ---------------------------------------------------------------------------

if (SMF === 'SSI')
	echo 'Forum Intelligence Dashboard installation completed.';

?>
