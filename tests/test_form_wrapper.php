<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2010-2022 Howard Jones                                    |
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 |                                                                         |
 | Regression checks for shared quicktree action-form wrappers             |
 |                                                                         |
 | Run: php tests/test_form_wrapper.php                                    |
 +-------------------------------------------------------------------------+
 */

$pass = 0;
$fail = 0;
$events = array();
$request_vars = array();

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

function get_nfilter_request_var($name) {
	global $request_vars;

	return array_key_exists($name, $request_vars) ? $request_vars[$name] : null;
}

function top_header() {
	global $events;
	$events[] = 'top_header';
}

function form_start($action, $form) {
	global $events;
	$events[] = "form_start:$action:$form";
}

function html_start_box($title) {
	global $events;
	$events[] = "html_start_box:$title";
}

function html_end_box() {
	global $events;
	$events[] = 'html_end_box';
}

function form_end() {
	global $events;
	$events[] = 'form_end';
}

require_once __DIR__ . '/../ui_helpers.php';

$events = array();
$request_vars = array('header' => null);
quicktree_action_form_begin('Tree Action');
quicktree_action_form_end();

assert_true(
	'begin/end wrapper includes top header when header var is null',
	$events === array('top_header', 'form_start:quicktree.php:quicktree_form', 'html_start_box:Tree Action', 'html_end_box', 'form_end')
);

$events = array();
$request_vars = array('header' => 'false');
quicktree_action_form_begin('Branch Action');
quicktree_action_form_end();

assert_true(
	'begin/end wrapper omits top header when header var is set',
	$events === array('form_start:quicktree.php:quicktree_form', 'html_start_box:Branch Action', 'html_end_box', 'form_end')
);

$source = file_get_contents(__DIR__ . '/../quicktree.php');
assert_true('quicktree.php is readable', $source !== false);
$source = ($source === false ? '' : $source);

assert_true(
	'quicktree.php includes ui helper file',
	preg_match('/(?:include_once|require_once)\s*\(\s*[\'"]plugins\/quicktree\/ui_helpers\.php[\'"]\s*\)\s*;/', $source) === 1
);
assert_true(
	'quicktree.php uses begin wrapper in add-tree and add-branch',
	preg_match_all('/quicktree_action_form_begin\s*\(/', $source, $begin_matches) >= 2
);
assert_true(
	'quicktree.php uses end wrapper in add-tree and add-branch',
	preg_match_all('/quicktree_action_form_end\s*\(\s*\)\s*;/', $source, $end_matches) >= 2
);

echo "\n";
echo "Results: $pass passed, $fail failed\n";

exit($fail > 0 ? 1 : 0);
