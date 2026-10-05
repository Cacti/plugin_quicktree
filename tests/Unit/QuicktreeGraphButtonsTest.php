<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
*/

/*
 * Coverage for quicktree_graph_buttons(): the 'graph_buttons' hook prints an
 * "Add this graph to QuickTree" glyph for the current graph, but only when
 * the user is authorized for the quicktree realm.
 */

beforeAll(function () {
	require_once __DIR__ . '/../../setup.php';
});

beforeEach(function () {
	$GLOBALS['__test_realm_auth'] = true;
});

it('prints an Add-to-QuickTree glyph for the current graph when authorized', function () {
	ob_start();
	quicktree_graph_buttons(array(1 => array('local_graph_id' => 42, 'rra' => 7)));
	$output = ob_get_clean();

	expect($output)->toContain('quicktreeAdd');
	expect($output)->toContain('fa-plus-circle');
	expect($output)->toContain('addQuickTree(42, 7)');
});

it('prints nothing when the user is not authorized for the quicktree realm', function () {
	$GLOBALS['__test_realm_auth'] = false;

	ob_start();
	quicktree_graph_buttons(array(1 => array('local_graph_id' => 42, 'rra' => 7)));
	$output = ob_get_clean();

	expect($output)->toBe('');
});
