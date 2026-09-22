<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
*/

/*
 * Integration coverage for plugin_quicktree_install(): verifies every hook
 * and the realm the plugin depends on at runtime are actually registered,
 * together with its table/config-option provisioning, in a single
 * end-to-end pass.
 */

beforeAll(function () {
	require_once __DIR__ . '/../../setup.php';
});

beforeEach(function () {
	$GLOBALS['__test_registered_hooks']  = array();
	$GLOBALS['__test_registered_realms'] = array();
	$GLOBALS['__test_config_options']    = array();
});

it('registers every hook quicktree depends on, its realm, and provisions its table/config option', function () {
	expect(plugin_quicktree_install())->toBeTrue();

	$hooks = array();
	foreach ($GLOBALS['__test_registered_hooks'] as $registered) {
		$hooks[$registered['hook']] = $registered;
	}

	foreach (array(
		'top_header_tabs',
		'top_graph_header_tabs',
		'config_arrays',
		'config_settings',
		'draw_navigation_text',
		'graph_buttons',
		'graph_buttons_thumbnails',
		'page_head',
	) as $expected) {
		expect($hooks)->toHaveKey($expected);
		expect($hooks[$expected]['name'])->toBe('quicktree');
		expect($hooks[$expected]['file'])->toBe('setup.php');
	}

	expect($GLOBALS['__test_registered_realms'])->toHaveCount(1);
	expect($GLOBALS['__test_registered_realms'][0]['file'])->toBe('quicktree.php');

	expect(read_config_option('quicktree_pagestyle'))->toBe('0');
});
