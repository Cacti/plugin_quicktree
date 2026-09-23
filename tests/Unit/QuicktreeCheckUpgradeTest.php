<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
*/

/*
 * Unit coverage for quicktree_check_upgrade() in setup.php: the page-guard
 * (only certain entry points run the check) and the version-drift branch
 * (re-enables the page_head hook, re-runs the table setup, and updates
 * plugin_config).
 */

beforeAll(function () {
	require_once __DIR__ . '/../../setup.php';
});

beforeEach(function () {
	$GLOBALS['__test_db_calls']                     = array();
	$GLOBALS['__test_registered_hooks']             = array();
	$GLOBALS['__test_db_fetch_cell_prepared_return'] = '';
	unset($_SERVER['PHP_SELF']);
});

it('does nothing when the current page is not in the allowed list', function () {
	$_SERVER['PHP_SELF'] = '/cacti/some_other_page.php';

	quicktree_check_upgrade();

	expect($GLOBALS['__test_db_calls'])->toBeEmpty();
	expect($GLOBALS['__test_registered_hooks'])->toBeEmpty();
});

it('runs the version check when PHP_SELF is not set at all (e.g. CLI)', function () {
	quicktree_check_upgrade();

	expect($GLOBALS['__test_db_calls'])->not->toBeEmpty();
});

it('does nothing further when the stored version already matches', function () {
	$_SERVER['PHP_SELF'] = '/cacti/plugins.php';

	$info = plugin_quicktree_version();
	quicktree_test_set_db_fetch_cell_prepared_return($info['version']);

	quicktree_check_upgrade();

	expect($GLOBALS['__test_registered_hooks'])->toBeEmpty();

	$updates = array_filter($GLOBALS['__test_db_calls'], function ($call) {
		return $call['fn'] === 'db_execute_prepared';
	});

	expect($updates)->toBeEmpty();
});

it('re-enables the page_head hook and updates plugin_config when the version drifts', function () {
	$_SERVER['PHP_SELF'] = '/cacti/quicktree.php';

	quicktree_test_set_db_fetch_cell_prepared_return('0.0.0');

	quicktree_check_upgrade();

	$pageHeadHooks = array_filter($GLOBALS['__test_registered_hooks'], function ($hook) {
		return $hook['hook'] === 'page_head' && $hook['enabled'] === 1;
	});

	expect($pageHeadHooks)->not->toBeEmpty();

	$updates = array_values(array_filter($GLOBALS['__test_db_calls'], function ($call) {
		return $call['fn'] === 'db_execute_prepared' && stripos($call['sql'], 'UPDATE plugin_config') !== false;
	}));

	expect($updates)->toHaveCount(1);
	expect($updates[0]['params'])->toBe(array(
		plugin_quicktree_version()['version'],
		plugin_quicktree_version()['longname'],
		plugin_quicktree_version()['author'],
		plugin_quicktree_version()['homepage'],
		plugin_quicktree_version()['name'],
	));
});
