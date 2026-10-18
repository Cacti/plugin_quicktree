<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
*/


require_once __DIR__ . '/../../includes/security.php';

describe('quicktree mutation status', function () {
	// A function cannot be undefined once declared, so the case without
	// csrf_check has to run before the stub below exists.
	it('rejects non-POST requests and POSTs that fail the CSRF check', function () {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		expect(quicktree_mutation_status())->toBe(405);

		unset($_SERVER['REQUEST_METHOD']);
		expect(quicktree_mutation_status())->toBe(405);

		$_SERVER['REQUEST_METHOD'] = 'POST';
		expect(quicktree_mutation_status())->toBe(403);

		function csrf_check($fatal) {
			return $GLOBALS['__test_csrf_valid'];
		}

		$GLOBALS['__test_csrf_valid'] = false;
		expect(quicktree_mutation_status())->toBe(403);

		$GLOBALS['__test_csrf_valid'] = true;
		expect(quicktree_mutation_status())->toBe(0);

		$_SERVER['REQUEST_METHOD'] = 'GET';
		expect(quicktree_mutation_status())->toBe(405);
	});
});
