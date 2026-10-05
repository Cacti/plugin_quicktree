<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
*/

/*
 * Coverage for quicktree_page_head(): the 'page_head' hook includes the
 * plugin JavaScript and base stylesheet on every page, and additionally
 * includes a theme-specific stylesheet when one exists for the selected
 * theme.
 */

beforeAll(function () {
	require_once __DIR__ . '/../../setup.php';
});

beforeEach(function () {
	$GLOBALS['__qt_orig_base']        = $GLOBALS['config']['base_path'];
	$GLOBALS['__test_selected_theme'] = 'modern';
});

afterEach(function () {
	$GLOBALS['config']['base_path'] = $GLOBALS['__qt_orig_base'];

	if (!empty($GLOBALS['__qt_tmp_base']) && is_dir($GLOBALS['__qt_tmp_base'])) {
		@unlink($GLOBALS['__qt_tmp_base'] . '/plugins/quicktree/css/modern.css');
		@rmdir($GLOBALS['__qt_tmp_base'] . '/plugins/quicktree/css');
		@rmdir($GLOBALS['__qt_tmp_base'] . '/plugins/quicktree');
		@rmdir($GLOBALS['__qt_tmp_base'] . '/plugins');
		@rmdir($GLOBALS['__qt_tmp_base']);
	}

	$GLOBALS['__qt_tmp_base'] = null;
});

it('includes the plugin JavaScript and base stylesheet', function () {
	ob_start();
	quicktree_page_head();
	$output = ob_get_clean();

	expect($output)->toContain('plugins/quicktree/js/quicktree.js');
	expect($output)->toContain('plugins/quicktree/css/quicktree.css');
});

it('includes the theme stylesheet when a matching theme file exists', function () {
	$base = sys_get_temp_dir() . '/qt_theme_' . uniqid();
	mkdir($base . '/plugins/quicktree/css', 0777, true);
	file_put_contents($base . '/plugins/quicktree/css/modern.css', '/* test */');

	$GLOBALS['__qt_tmp_base']          = $base;
	$GLOBALS['config']['base_path']    = $base;
	$GLOBALS['__test_selected_theme']  = 'modern';

	ob_start();
	quicktree_page_head();
	$output = ob_get_clean();

	expect($output)->toContain('plugins/quicktree/css/modern.css');
});

it('omits the theme stylesheet when no matching theme file exists', function () {
	$base = sys_get_temp_dir() . '/qt_theme_' . uniqid();
	mkdir($base . '/plugins/quicktree/css', 0777, true);

	$GLOBALS['__qt_tmp_base']          = $base;
	$GLOBALS['config']['base_path']    = $base;
	$GLOBALS['__test_selected_theme']  = 'no-such-theme';

	ob_start();
	quicktree_page_head();
	$output = ob_get_clean();

	expect($output)->not->toContain('no-such-theme');
	expect($output)->toContain('plugins/quicktree/css/quicktree.css');
});
