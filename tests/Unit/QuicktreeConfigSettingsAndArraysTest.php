<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
*/

/*
 * Unit coverage for quicktree_config_settings() and quicktree_config_arrays()
 * in setup.php.
 */

beforeAll(function () {
	require_once __DIR__ . '/../../setup.php';
});

beforeEach(function () {
	$GLOBALS['__test_config_options'] = array();
	unset($_SERVER['PHP_SELF']);

	// Sandbox base_path so quicktree_config_arrays()->quicktree_check_upgrade()'s
	// version-drift prune runs against a throwaway tree with no manifest.json
	// (prune no-ops), never the real checkout.
	$GLOBALS['__quicktree_base_restore'] = $GLOBALS['config']['base_path'];
	$base = sys_get_temp_dir() . '/quicktree-test-' . uniqid();
	mkdir($base . '/plugins/quicktree', 0777, true);
	copy(__DIR__ . '/../../INFO', $base . '/plugins/quicktree/INFO');
	$GLOBALS['config']['base_path'] = $base;
});

afterEach(function () {
	if (isset($GLOBALS['__quicktree_base_restore'])) {
		$GLOBALS['config']['base_path'] = $GLOBALS['__quicktree_base_restore'];
	}
});

it('adds the misc tab and quicktree settings when no settings exist yet', function () {
	global $tabs, $settings;

	$tabs     = array();
	$settings = array();

	quicktree_config_settings();

	expect($tabs['misc'])->toBe('Misc');
	expect($settings['misc'])->toHaveKey('quicktree_pagestyle');
	expect($settings['misc']['quicktree_pagestyle']['method'])->toBe('drop_array');
});

it('merges into an existing misc settings array without clobbering it', function () {
	global $tabs, $settings;

	$tabs     = array();
	$settings = array('misc' => array('other_setting' => array('friendly_name' => 'Other')));

	quicktree_config_settings();

	expect($settings['misc'])->toHaveKey('other_setting');
	expect($settings['misc'])->toHaveKey('quicktree_pagestyle');
});

it('adds the Management menu entry only when the page style enables the console view', function () {
	global $menu;

	$menu = array(__('Management') => array());

	$GLOBALS['__test_config_options']['quicktree_pagestyle'] = '0';
	quicktree_config_arrays();
	expect($menu[__('Management')])->not->toHaveKey('plugins/quicktree/quicktree.php?location=console');

	$menu = array(__('Management') => array());

	$GLOBALS['__test_config_options']['quicktree_pagestyle'] = '1';
	quicktree_config_arrays();
	expect($menu[__('Management')])->toHaveKey('plugins/quicktree/quicktree.php?location=console');
});
