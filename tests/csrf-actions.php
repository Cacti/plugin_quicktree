<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2023 Howard Jones                                    |
 | Copyright (C) 2022 The Cacti Group                                      |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

$source         = file_get_contents(__DIR__ . '/../quicktree.php');
$start          = strpos($source, 'if ($drp_action != null)');
$end            = strpos($source, 'switch ($action) {', $start);
$dispatch_guard = substr($source, $start, $end - $start);
$mutation_cases = [['GET', null, 405], ['POST', null, 403], ['POST', 'invalid', 403], ['POST', 'valid', 200]];
$remove_cases   = [['GET', null, 200], ['POST', null, 200], ['POST', 'invalid', 200]];
$routes         = [
	['add', null, $mutation_cases],
	['clear', null, $mutation_cases],
	['save', null, $mutation_cases],
	['', 3, $mutation_cases],
	['view', 3, $mutation_cases],
	['remove', null, $remove_cases],
];
$count = 0;

foreach (['legacy', 'method-only', 'method-and-token'] as $host) {
	foreach ($routes as $route) {
		foreach ($route[2] as $case) {
			[$method, $token, $expected] = $case;
			$seed                        = var_export(['method' => $method, 'token' => $token, 'action' => $route[0], 'drp_action' => $route[1]], true);
			$script                      = '<?php $seed = ' . $seed . ';' . <<<'PHP'

$_SERVER['REQUEST_METHOD'] = $seed['method'];
http_response_code(200);
$action = $seed['action'];
$drp_action = $seed['drp_action'];
$code_actions = array(1 => 'add_tree', 2 => 'add_branch', 3 => 'clear');
$mutated = false;
$core_guard_calls = 0;
function csrf_check($fatal) { return $GLOBALS['seed']['token'] === 'valid'; }
register_shutdown_function(function () { echo json_encode(array('status' => http_response_code(), 'mutated' => $GLOBALS['mutated'], 'core_guard_calls' => $GLOBALS['core_guard_calls'])); });
PHP;
			$script .= "\nrequire " . var_export(__DIR__ . '/../includes/security.php', true) . ';';

			if ($host !== 'legacy') {
				$script .= <<<'PHP'

function csrf_require_post() {
	$GLOBALS['core_guard_calls']++;
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		header('Allow: POST');
		http_response_code(405);
		exit;
	}
PHP;

				if ($host === 'method-and-token') {
					$script .= 'if (!csrf_check(false)) { http_response_code(403); exit; }';
				}
				$script .= "\n}\n";
			}
			$script .= "\n" . $dispatch_guard . "\n\$mutated = true;";
			$process = proc_open([PHP_BINARY], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
			fwrite($pipes[0], $script);
			fclose($pipes[0]);
			$result = json_decode(stream_get_contents($pipes[1]), true);
			$stderr = stream_get_contents($pipes[2]);
			fclose($pipes[1]);
			fclose($pipes[2]);

			$uses_core_guard = $host !== 'legacy' && (in_array($route[0], ['add', 'clear', 'save'], true) || $route[1] === 3);

			if (proc_close($process) !== 0 || $stderr !== '' || $result['status'] !== $expected || $result['mutated'] !== ($expected === 200) || $result['core_guard_calls'] !== (int) $uses_core_guard) {
				throw new RuntimeException('QuickTree mutation guard regression: ' . $stderr);
			}

			$count++;
		}
	}
}
print "$count mutation guard cases passed\n";
