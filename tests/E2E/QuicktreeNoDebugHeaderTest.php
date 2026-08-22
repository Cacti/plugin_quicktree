<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
*/

describe('quicktree security regression wiring', function () {
	it('does not emit the raw debug action header anymore', function () {
		$path = realpath(__DIR__ . '/../../quicktree.php');
		expect($path)->not->toBeFalse();

		$contents = file_get_contents($path);
		expect($contents)->not->toBeFalse();

		expect($contents)->not->toContain("header('action_3_new: '. \$action);");
	});
});
