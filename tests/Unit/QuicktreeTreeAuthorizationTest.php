<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
*/

/*
 * quicktree_can_edit_tree() gates every write into an existing graph tree.
 * The legacy cases run first because the host ownership helper, once
 * defined, cannot be removed from the process.
 */

beforeAll(function () {
	require_once __DIR__ . '/../../setup.php';

	if (!function_exists('is_tree_allowed')) {
		function is_tree_allowed($tree_id) {
			return $GLOBALS['__test_tree_allowed'];
		}
	}
});

beforeEach(function () {
	$GLOBALS['__test_tree_allowed'] = true;
	$GLOBALS['__test_realm_auth']   = false;
	quicktree_test_set_db_fetch_cell_prepared_return(1);
	$_SESSION['sess_user_id'] = 1;
});

it('allows the tree owner', function () {
	expect(quicktree_can_edit_tree(5))->toBeTrue();
});

it('denies a user who does not own the tree', function () {
	quicktree_test_set_db_fetch_cell_prepared_return(2);

	expect(quicktree_can_edit_tree(5))->toBeFalse();
});

it('allows an administrator on a tree owned by someone else', function () {
	quicktree_test_set_db_fetch_cell_prepared_return(2);
	$GLOBALS['__test_realm_auth'] = true;

	expect(quicktree_can_edit_tree(5))->toBeTrue();
});

it('denies a tree the user cannot view even when they are an administrator', function () {
	$GLOBALS['__test_tree_allowed'] = false;
	$GLOBALS['__test_realm_auth']   = true;

	expect(quicktree_can_edit_tree(5))->toBeFalse();
});

it('denies an unauthenticated session', function () {
	$_SESSION['sess_user_id'] = 0;
	$GLOBALS['__test_realm_auth'] = true;

	expect(quicktree_can_edit_tree(5))->toBeFalse();
});

it('denies a missing tree id', function () {
	$GLOBALS['__test_realm_auth'] = true;

	expect(quicktree_can_edit_tree(0))->toBeFalse();
});

it('denies a tree with no owner row', function () {
	quicktree_test_set_db_fetch_cell_prepared_return(false);

	expect(quicktree_can_edit_tree(5))->toBeFalse();
});

it('defers to the host ownership helper when it exists', function () {
	if (!function_exists('cacti_authorize_resource')) {
		function cacti_authorize_resource($user_id, $tree_id, $type) {
			return $GLOBALS['__test_resource_allowed'];
		}
	}

	$GLOBALS['__test_resource_allowed'] = false;
	expect(quicktree_can_edit_tree(5))->toBeFalse();

	$GLOBALS['__test_resource_allowed'] = true;
	expect(quicktree_can_edit_tree(5))->toBeTrue();
});

afterEach(function () {
	quicktree_test_set_db_fetch_cell_prepared_return('');
	$GLOBALS['__test_realm_auth'] = true;
});
