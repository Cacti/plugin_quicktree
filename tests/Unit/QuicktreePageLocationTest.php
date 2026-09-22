<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
*/

/*
 * Unit coverage for quicktree_page_location() in setup.php.
 */

beforeAll(function () {
	require_once __DIR__ . '/../../setup.php';
});

beforeEach(function () {
	$GLOBALS['__test_config_options'] = array();
});

it('always returns the preferred location when both tab and console are enabled', function () {
	$GLOBALS['__test_config_options']['quicktree_pagestyle'] = '2';

	expect(quicktree_page_location('tab'))->toBe('tab');
	expect(quicktree_page_location('console'))->toBe('console');
});

it('returns tab when only the tab is enabled and tab is preferred', function () {
	$GLOBALS['__test_config_options']['quicktree_pagestyle'] = '0';

	expect(quicktree_page_location('tab'))->toBe('tab');
});

it('falls back to console when only the console menu is enabled but tab is preferred', function () {
	$GLOBALS['__test_config_options']['quicktree_pagestyle'] = '1';

	expect(quicktree_page_location('tab'))->toBe('console');
});

it('returns console when preferred is console and only console is enabled', function () {
	$GLOBALS['__test_config_options']['quicktree_pagestyle'] = '1';

	expect(quicktree_page_location('console'))->toBe('console');
});

it('falls back to tab when preferred is console but only the tab is enabled', function () {
	$GLOBALS['__test_config_options']['quicktree_pagestyle'] = '0';

	expect(quicktree_page_location('console'))->toBe('tab');
});
