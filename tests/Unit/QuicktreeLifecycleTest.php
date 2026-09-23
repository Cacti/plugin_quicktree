<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
*/

/*
 * Unit coverage for the plugin lifecycle contract functions in setup.php:
 * plugin_quicktree_uninstall(), plugin_quicktree_check_config(), and
 * plugin_quicktree_upgrade().
 */

beforeAll(function () {
	require_once __DIR__ . '/../../setup.php';
});

beforeEach(function () {
	$GLOBALS['__test_db_calls']                      = array();
	$GLOBALS['__test_db_fetch_cell_prepared_return']  = '';
	unset($_SERVER['PHP_SELF']);
});

it('does not error on uninstall', function () {
	expect(fn () => plugin_quicktree_uninstall())->not->toThrow(Throwable::class);
});

it('reports the config as always valid', function () {
	expect(plugin_quicktree_check_config())->toBeTrue();
});

it('runs the upgrade check and reports false', function () {
	$_SERVER['PHP_SELF'] = '/cacti/plugins.php';

	$info = plugin_quicktree_version();
	quicktree_test_set_db_fetch_cell_prepared_return($info['version']);

	// version matches => quicktree_check_upgrade() takes the no-drift path.
	expect(plugin_quicktree_upgrade())->toBeFalse();
});
