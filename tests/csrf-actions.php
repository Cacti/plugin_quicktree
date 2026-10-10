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


$source = file_get_contents(__DIR__ . '/../quicktree.php');
$start = strpos($source, 'if ($drp_action != null)');
$end = strpos($source, "header('action_3_new:", $start);
$dispatch_guard = substr($source, $start, $end - $start);

foreach (array(array('add', null), array('clear', null), array('', 3), array('view', 3)) as $route) {
	foreach (array(array('GET', null, 405), array('POST', null, 403), array('POST', 'invalid', 403), array('POST', 'valid', 200)) as $case) {
		list($method, $token, $expected) = $case;
		$seed = var_export(array('method' => $method, 'token' => $token, 'action' => $route[0], 'drp_action' => $route[1]), true);
		$script = '<?php $seed = ' . $seed . ';' . <<<'PHP'

$_SERVER['REQUEST_METHOD'] = $seed['method'];
http_response_code(200);
$action = $seed['action'];
$drp_action = $seed['drp_action'];
$code_actions = array(1 => 'add_tree', 2 => 'add_branch', 3 => 'clear');
$mutated = false;
function csrf_check($fatal) { return $GLOBALS['seed']['token'] === 'valid'; }
register_shutdown_function(function () { echo json_encode(array('status' => http_response_code(), 'mutated' => $GLOBALS['mutated'])); });
PHP;
		$script .= "\n" . $dispatch_guard . "\n\$mutated = true;";
		$process = proc_open(array(PHP_BINARY), array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
		fwrite($pipes[0], $script);
		fclose($pipes[0]);
		$result = json_decode(stream_get_contents($pipes[1]), true);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		if (proc_close($process) !== 0 || $stderr !== '' || $result['status'] !== $expected || $result['mutated'] !== ($expected === 200)) {
			throw new RuntimeException('QuickTree mutation guard regression: ' . $stderr);
		}
	}
}
echo "16 mutation guard cases passed\n";
