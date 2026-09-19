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
 * top_header/form_start/html_start_box/html_end_box/form_end are stubbed once
 * in tests/bootstrap-unit.php. get_nfilter_request_var() there reads from
 * $GLOBALS['__test_request_vars'], which lets this file drive both branches
 * of quicktree_action_form_begin().
 */
require_once __DIR__ . '/../../ui_helpers.php';

describe('shared quicktree action-form wrappers', function () {
	it('begin/end wrapper includes top header when header var is not set', function () {
		$GLOBALS['__form_wrapper_events'] = array();
		unset($GLOBALS['__test_request_vars']['header']);

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

	it('begin/end wrapper omits top header when header var is set', function () {
		$GLOBALS['__form_wrapper_events'] = array();
		$GLOBALS['__test_request_vars']['header'] = 'false';

		quicktree_action_form_begin('Branch Action');
		quicktree_action_form_end();

		expect($GLOBALS['__form_wrapper_events'])->toBe(array(
			'form_start:quicktree.php:quicktree_form',
			'html_start_box:Branch Action',
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
