<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
*/

/*
 * Unit coverage for quicktree_setup_table() in setup.php.
 */

beforeAll(function () {
	require_once __DIR__ . '/../../setup.php';
});

beforeEach(function () {
	$GLOBALS['__test_config_options'] = array();
});

it('defaults the page style to Tab when unset', function () {
	quicktree_setup_table();

	expect(read_config_option('quicktree_pagestyle'))->toBe('0');
});

it('defaults the page style to Tab when the stored value is out of range', function () {
	$GLOBALS['__test_config_options']['quicktree_pagestyle'] = '5';

	quicktree_setup_table();

	expect(read_config_option('quicktree_pagestyle'))->toBe('0');
});

it('leaves a valid stored page style untouched', function () {
	$GLOBALS['__test_config_options']['quicktree_pagestyle'] = '2';

	quicktree_setup_table();

	expect(read_config_option('quicktree_pagestyle'))->toBe('2');
});
