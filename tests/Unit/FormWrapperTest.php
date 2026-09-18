<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2010-2022 Howard Jones                                    |
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
*/

/*
 * top_header/form_start/html_start_box/html_end_box/form_end are not stubbed
 * by tests/bootstrap-unit.php, so this file provides call-recording stubs.
 * get_nfilter_request_var() is already stubbed there (always returns ''),
 * so only the "header var not set" branch of quicktree_action_form_begin()
 * is reachable here.
 */
if (!function_exists('top_header')) {
	function top_header() {
		$GLOBALS['__form_wrapper_events'][] = 'top_header';
	}
}

if (!function_exists('form_start')) {
	function form_start($action, $form) {
		$GLOBALS['__form_wrapper_events'][] = "form_start:$action:$form";
	}
}

if (!function_exists('html_start_box')) {
	function html_start_box($title) {
		$GLOBALS['__form_wrapper_events'][] = "html_start_box:$title";
	}
}

if (!function_exists('html_end_box')) {
	function html_end_box() {
		$GLOBALS['__form_wrapper_events'][] = 'html_end_box';
	}
}

if (!function_exists('form_end')) {
	function form_end() {
		$GLOBALS['__form_wrapper_events'][] = 'form_end';
	}
}

require_once __DIR__ . '/../../ui_helpers.php';

describe('shared quicktree action-form wrappers', function () {
	it('begin/end wrapper emits header, form and box events in order', function () {
		$GLOBALS['__form_wrapper_events'] = array();

		quicktree_action_form_begin('Tree Action');
		quicktree_action_form_end();

		expect($GLOBALS['__form_wrapper_events'])->toBe(array(
			'top_header',
			'form_start:quicktree.php:quicktree_form',
			'html_start_box:Tree Action',
			'html_end_box',
			'form_end',
		));
	});

	it('quicktree.php includes ui helper file', function () {
		$source = file_get_contents(realpath(__DIR__ . '/../../quicktree.php'));

		expect(preg_match('/(?:include_once|require_once)\s*\(\s*[\'"]plugins\/quicktree\/ui_helpers\.php[\'"]\s*\)\s*;/', $source))->toBe(1);
	});

	it('quicktree.php uses begin wrapper in add-tree and add-branch', function () {
		$source = file_get_contents(realpath(__DIR__ . '/../../quicktree.php'));

		expect(preg_match_all('/quicktree_action_form_begin\s*\(/', $source))->toBeGreaterThanOrEqual(2);
	});

	it('quicktree.php uses end wrapper in add-tree and add-branch', function () {
		$source = file_get_contents(realpath(__DIR__ . '/../../quicktree.php'));

		expect(preg_match_all('/quicktree_action_form_end\s*\(\s*\)\s*;/', $source))->toBeGreaterThanOrEqual(2);
	});
});
