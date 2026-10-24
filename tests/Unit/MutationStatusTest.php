<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
*/

it('protects mutation routes with legacy, method-only and token-checking Cacti guards', function () {
	$process = proc_open([PHP_BINARY, __DIR__ . '/../csrf-actions.php'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
	$stdout  = stream_get_contents($pipes[1]);
	$stderr  = stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);

	expect(proc_close($process))->toBe(0)
		->and($stderr)->toBe('')
		->and(trim($stdout))->toBe('69 mutation guard cases passed');
});
