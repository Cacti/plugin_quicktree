<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
*/

describe('auth guard presence in quicktree', function () {
	it('includes auth.php or global.php in all UI entry points', function () {
		$uiFiles = array(
		'quicktree.php',
		);

		foreach ($uiFiles as $relativeFile) {
			$path = realpath(__DIR__ . '/../../' . $relativeFile);
			if ($path === false) continue;
			$contents = file_get_contents($path);
			if ($contents === false) continue;

			// Files that include setup.php or are library files don't need direct auth
			if (strpos($relativeFile, 'include/') === 0 || strpos($relativeFile, 'lib/') === 0) continue;
			if (strpos($relativeFile, 'poller_') === 0) continue;

			$hasAuth = (
				strpos($contents, 'auth.php') !== false ||
				strpos($contents, 'global.php') !== false ||
				strpos($contents, 'global_arrays.php') !== false
			);

			expect($hasAuth)->toBeTrue(
				"File {$relativeFile} does not include auth.php or global.php"
			);
		}
	});

	it('validates numeric IDs from request variables before DB queries', function () {
		$uiFiles = array(
		'quicktree.php',
		);

		foreach ($uiFiles as $relativeFile) {
			$path = realpath(__DIR__ . '/../../' . $relativeFile);
			expect($path)->not->toBeFalse();

			$contents = file_get_contents($path);
			expect($contents)->not->toBeFalse();

			// 'id' must always be read through the filtered helper, never the raw one
			expect(preg_match('/get_request_var\s*\(\s*[\'\"]id[\'\"]/', $contents))->toBe(0,
				"File {$relativeFile} reads 'id' via get_request_var() without validation"
			);

			expect(preg_match('/get_filter_request_var\s*\(\s*[\'\"]id[\'\"]/', $contents))->toBeGreaterThan(0,
				"File {$relativeFile} does not validate 'id' via get_filter_request_var()"
			);
		}
	});
});
