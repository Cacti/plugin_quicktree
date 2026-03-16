<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2010-2022 Howard Jones                                    |
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 |                                                                         |
 | Regression checks for prepared DB helper migration in quicktree plugin  |
 |                                                                         |
 | Run: php tests/test_prepared_statements.php                             |
 +-------------------------------------------------------------------------+
 */

$pass = 0;
$fail = 0;

function assert_true($label, $value) {
	global $pass, $fail;

	if ($value) {
		echo "PASS  $label\n";
		$pass++;
	} else {
		echo "FAIL  $label\n";
		$fail++;
	}
}

$quicktree_contents = file_get_contents(__DIR__ . '/../quicktree.php');
$setup_contents = file_get_contents(__DIR__ . '/../setup.php');

assert_true('quicktree.php is readable', $quicktree_contents !== false);
assert_true('setup.php is readable', $setup_contents !== false);

$quicktree_contents = ($quicktree_contents === false ? '' : $quicktree_contents);
$setup_contents = ($setup_contents === false ? '' : $setup_contents);

assert_true(
	'quicktree.php uses prepared graph tree list query',
	preg_match('/db_fetch_assoc_prepared\s*\(/', $quicktree_contents) === 1
	&& preg_match('/\bgraph_tree\b/', $quicktree_contents) === 1
);
assert_true(
	'quicktree.php uses prepared max sequence query',
	preg_match('/db_fetch_cell_prepared\s*\(/', $quicktree_contents) === 1
	&& preg_match('/MAX\s*\(\s*sequence\s*\)/i', $quicktree_contents) === 1
	&& preg_match('/\bgraph_tree\b/', $quicktree_contents) === 1
);
assert_true(
	'quicktree.php has no raw db_fetch_assoc calls',
	preg_match('/\bdb_fetch_assoc\s*\(/', $quicktree_contents) === 0
);
assert_true(
	'setup.php uses prepared plugin version lookup',
	preg_match('/db_fetch_cell_prepared\s*\(/', $setup_contents) === 1
	&& preg_match('/\bplugin_config\b/', $setup_contents) === 1
	&& preg_match('/\bdirectory\s*=\s*\?/', $setup_contents) === 1
);
assert_true(
	'setup.php has no raw db_fetch_cell calls',
	preg_match('/\bdb_fetch_cell\s*\(/', $setup_contents) === 0
);

echo "\n";
echo "Results: $pass passed, $fail failed\n";

exit($fail > 0 ? 1 : 0);
