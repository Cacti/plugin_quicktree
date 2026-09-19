<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
*/

require_once __DIR__ . '/../../quicktree_security.php';

describe('quicktree location normalization', function () {
	it('allows only the supported quicktree locations', function () {
		expect(quicktree_normalize_location('console'))->toBe('console');
		expect(quicktree_normalize_location('tab'))->toBe('tab');
		expect(quicktree_normalize_location('console%0d%0aX-Test:1'))->toBe('tab');
		expect(quicktree_normalize_location('../../evil'))->toBe('tab');
	});
});
