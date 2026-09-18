<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2010-2022 Howard Jones                                    |
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
*/

describe('prepared DB helper migration in quicktree', function () {
	$quicktree_contents = file_get_contents(realpath(__DIR__ . '/../../quicktree.php'));
	$setup_contents     = file_get_contents(realpath(__DIR__ . '/../../setup.php'));

	it('quicktree.php and setup.php are readable', function () use ($quicktree_contents, $setup_contents) {
		expect($quicktree_contents)->not->toBeFalse();
		expect($setup_contents)->not->toBeFalse();
	});

	it('uses prepared graph tree list query', function () use ($quicktree_contents) {
		expect(preg_match('/db_fetch_assoc_prepared\s*\(/', $quicktree_contents))->toBe(1);
		expect(preg_match('/\bgraph_tree\b/', $quicktree_contents))->toBe(1);
	});

	it('uses prepared max sequence query', function () use ($quicktree_contents) {
		expect(preg_match('/db_fetch_cell_prepared\s*\(/', $quicktree_contents))->toBe(1);
		expect(preg_match('/MAX\s*\(\s*sequence\s*\)/i', $quicktree_contents))->toBe(1);
		expect(preg_match('/\bgraph_tree\b/', $quicktree_contents))->toBe(1);
	});

	it('save flow uses prepared queue fetch and cleanup', function () use ($quicktree_contents) {
		expect(preg_match('/\bdb_fetch_assoc_prepared\s*\(/', $quicktree_contents))->toBe(1);
		expect(preg_match('/\bFROM\s+quicktree_graphs\b/i', $quicktree_contents))->toBe(1);
		expect(preg_match('/\bdb_execute_prepared\s*\(/', $quicktree_contents))->toBe(1);
		expect(preg_match('/\bDELETE\s+FROM\s+quicktree_graphs\b/i', $quicktree_contents))->toBe(1);
		expect(preg_match('/\buserid\s*=\s*\?/', $quicktree_contents))->toBe(1);
	});

	it('existing-tree branch lookup is parameterized', function () use ($quicktree_contents) {
		expect(preg_match('/\bdb_fetch_cell_prepared\s*\(/', $quicktree_contents))->toBe(1);
		expect(preg_match('/\bFROM\s+graph_tree_items\b/i', $quicktree_contents))->toBe(1);
		expect(preg_match('/\bgraph_tree_id\s*=\s*\?/', $quicktree_contents))->toBe(1);
		expect(preg_match('/\btitle\s*=\s*\?/', $quicktree_contents))->toBe(1);
		expect(preg_match('/\bLIMIT\s+1\b/i', $quicktree_contents))->toBe(1);
	});

	it('has no raw db_fetch_assoc calls', function () use ($quicktree_contents) {
		expect(preg_match('/\bdb_fetch_assoc\s*\(/', $quicktree_contents))->toBe(0);
	});

	it('setup.php uses prepared plugin version lookup', function () use ($setup_contents) {
		expect(preg_match('/db_fetch_cell_prepared\s*\(/', $setup_contents))->toBe(1);
		expect(preg_match('/\bplugin_config\b/', $setup_contents))->toBe(1);
		expect(preg_match('/\bdirectory\s*=\s*\?/', $setup_contents))->toBe(1);
	});

	it('setup.php has no raw db_fetch_cell calls', function () use ($setup_contents) {
		expect(preg_match('/\bdb_fetch_cell\s*\(/', $setup_contents))->toBe(0);
	});
});
