<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2010-2022 Howard Jones                                    |
 | Copyright (C) 2004-2026 The Cacti Group                                 |
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

/**
 * Normalizes a requested QuickTree page location to one of two allowed
 * values, treating anything other than 'console' as 'tab'. Called from
 * quicktree.php's dispatcher to sanitize the 'location' request variable
 * before it is used to build URLs or navigation entries.
 *
 * @param string $value The raw requested location value.
 *
 * @return string Either 'console' or 'tab'.
 */
function quicktree_normalize_location($value): string {
	if ($value === 'console') {
		return 'console';
	}

	return 'tab';
}

/**
 * Reports why a QuickTree request may not change state, or 0 when it may.
 * Returns 405 when the request is not a POST, 403 when the CSRF token is
 * missing or invalid, and 0 for a CSRF-validated POST. The caller sends the
 * response and ends the request. Called from quicktree.php's dispatcher for
 * the add, clear and save actions, including the clear alias selected by
 * the bulk drp_action. The remove action stays GET until its row links
 * become POST forms.
 *
 * @return int An HTTP status code, or 0 when the request is allowed.
 */
function quicktree_mutation_status(): int {
	if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
		return 405;
	}

	if (!function_exists('csrf_check') || !csrf_check(false)) {
		return 403;
	}

	return 0;
}

/**
 * Authorize an existing tree before QuickTree writes any items to it.
 *
 * @param int $tree_id Existing tree ID.
 *
 * @return bool Whether the current user may edit this tree.
 */
function quicktree_can_edit_tree(int $tree_id): bool {
	$user_id = isset($_SESSION['sess_user_id']) ? (int) $_SESSION['sess_user_id'] : 0;

	if ($user_id <= 0 || $tree_id <= 0 || !is_tree_allowed($tree_id)) {
		return false;
	}

	if (function_exists('cacti_authorize_resource')) {
		return cacti_authorize_resource($user_id, $tree_id, 'graph_tree');
	}

	// Older hosts lack the ownership helper. Preserve its admin/owner policy.
	if (api_user_realm_auth('user_admin.php')) {
		return true;
	}

	$owner = db_fetch_cell_prepared('SELECT user_id FROM graph_tree WHERE id = ?', array($tree_id));

	return $owner !== false && $owner !== null && (int) $owner === $user_id;
}
