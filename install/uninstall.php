<?php

/**
 * Forum Intelligence Dashboard - uninstallation script.
 *
 * Removes every fid_* setting from the settings table and drops the
 * {db_prefix}fid_alerts table. Nothing else is touched: hook removals and
 * packaged file deletions are performed by the Package Manager through the
 * reverse actions declared in package.xml, and db_create_table() has already
 * registered the table in the package database log for the same purpose.
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
		die('<b>Error:</b> Cannot uninstall - please verify you put this in the same place as SMF\'s index.php.');
}

global $smcFunc;

// Needed for db_drop_table().
db_extend('packages');

// ---------------------------------------------------------------------------
// 1. Remove all fid_* settings (exact prefix match, not a bare LIKE).
// ---------------------------------------------------------------------------

$fid_variables = array();
$fid_request = $smcFunc['db_query']('', '
	SELECT variable
	FROM {db_prefix}settings
	WHERE variable LIKE {string:pattern}',
	array(
		'pattern' => 'fid\_%',
	)
);
while ($fid_row = $smcFunc['db_fetch_assoc']($fid_request))
	$fid_variables[] = $fid_row['variable'];
$smcFunc['db_free_result']($fid_request);

if (!empty($fid_variables))
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}settings
		WHERE variable IN ({array_string:variables})',
		array(
			'variables' => $fid_variables,
		)
	);

// Avoid stale cached values for the removed settings.
if (function_exists('cache_put_data'))
	cache_put_data('fid_unseen_alerts', null, 120);

// ---------------------------------------------------------------------------
// 2. Drop the alerts table (idempotent: returns false quietly when absent).
// ---------------------------------------------------------------------------

$smcFunc['db_drop_table']('{db_prefix}fid_alerts');

if (SMF === 'SSI')
	echo 'Forum Intelligence Dashboard uninstallation completed.';

?>
