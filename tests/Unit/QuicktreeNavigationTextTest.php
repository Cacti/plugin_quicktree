<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
*/

/*
 * The breadcrumb URL is rendered into the page, so the request's location
 * value must never reach it unnormalized.
 */

beforeAll(function () {
	require_once __DIR__ . '/../../setup.php';
});

afterEach(function () {
	$GLOBALS['__test_request_vars'] = array();
});

it('builds the breadcrumb from a normalized location', function ($location, $mapping, $url) {
	$GLOBALS['__test_request_vars']['location'] = $location;

	$nav = quicktree_draw_navigation_text(array());

	expect($nav['quicktree.php:']['mapping'])->toBe($mapping);
	expect($nav['quicktree.php:']['url'])->toBe($url);
})->with([
	'console'  => ['console', 'index.php:', 'quicktree.php?location=console'],
	'tab'      => ['tab', '', 'quicktree.php?location=tab'],
	'hostile'  => ['"><script>alert(1)</script>&action=clear', '', 'quicktree.php?location=tab'],
]);
