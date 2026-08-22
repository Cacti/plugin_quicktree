<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
*/

describe('quicktree redirect and url wiring', function () {
	it('normalizes location before redirects and form links', function () {
		$path = realpath(__DIR__ . '/../../quicktree.php');
		expect($path)->not->toBeFalse();

		$contents = file_get_contents($path);
		expect($contents)->not->toBeFalse();

		expect($contents)->toContain("include_once('plugins/quicktree/quicktree_security.php');");
		expect($contents)->toContain("\$location = quicktree_normalize_location(get_nfilter_request_var('location'));");
		expect($contents)->toContain("header('Location: quicktree.php?location=' . \$location);");
		expect($contents)->toContain("form_start('quicktree.php?location=' . \$location, 'quicktree_form');");
	});
});
