<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
*/

/*
 * Runs the 'save' branch up to the first tree write with the tree lookup
 * stubbed, for both the tree_id and the tree-name request paths.
 */

$source = file_get_contents(__DIR__ . '/../quicktree.php');
$start  = strpos($source, "case 'save':");
$end    = strpos($source, "if (empty(\$tree_name)) {", $start);
$save   = substr($source, $start + strlen("case 'save':"), $end - $start - strlen("case 'save':"));

$cases = array(
	array('tree_id', true, 403),
	array('tree_id', false, 200),
	array('tree', true, 403),
	array('tree', false, 200),
);

foreach ($cases as $case) {
	list($path, $denied, $expected) = $case;
	$seed   = var_export(array('path' => $path, 'denied' => $denied), true);
	$script = '<?php $seed = ' . $seed . ';' . <<<'PHP'

http_response_code(200);
$user = 1;
$mutated = false;
function isset_request_var($name) { return $name === $GLOBALS['seed']['path']; }
function get_filter_request_var($name) { return 7; }
function get_nfilter_request_var($name) { return 'Shared'; }
function db_fetch_cell_prepared($sql, $params = array()) {
	return stripos($sql, 'SELECT id') !== false ? 7 : 'Shared';
}
function quicktree_can_edit_tree($id) { return !$GLOBALS['seed']['denied'] && $id === 7; }
register_shutdown_function(function () { echo json_encode(array('status' => http_response_code(), 'mutated' => $GLOBALS['mutated'])); });
PHP;
	$script .= "\n" . $save . "\n\$mutated = true;";
	$process = proc_open(array(PHP_BINARY), array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
	fwrite($pipes[0], $script);
	fclose($pipes[0]);
	$result = json_decode(stream_get_contents($pipes[1]), true);
	$stderr = stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	if (proc_close($process) !== 0 || $stderr !== '' || $result['status'] !== $expected || $result['mutated'] !== ($expected === 200)) {
		throw new RuntimeException('QuickTree save authorization regression: ' . $path . ' ' . $stderr);
	}
}
echo "4 save authorization cases passed\n";
