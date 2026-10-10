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


require_once __DIR__ . '/../setup.php';

function is_tree_allowed($id) { return $GLOBALS['view_allowed']; }
function api_user_realm_auth($realm) { return $GLOBALS['admin']; }
function db_fetch_cell_prepared($sql, $params) { return $GLOBALS['owner']; }

$cases = array(
	array(1, 5, true, false, 1, true),
	array(1, 5, true, false, 2, false),
	array(1, 5, true, true, 2, true),
	array(1, 5, false, true, 1, false),
	array(0, 5, true, true, 0, false),
	array(1, 0, true, true, 1, false),
	array(1, 5, true, false, false, false),
);
foreach ($cases as $case) {
	list($user, $tree, $GLOBALS['view_allowed'], $GLOBALS['admin'], $GLOBALS['owner'], $expected) = $case;
	$_SESSION['sess_user_id'] = $user;
	if (quicktree_can_edit_tree($tree) !== $expected) {
		throw new RuntimeException('Tree authorization regression');
	}
}

// Define the newer host helper only after testing the legacy fallback.
if (!function_exists('cacti_authorize_resource')) {
	function cacti_authorize_resource($user, $id, $type) { return $GLOBALS['resource_allowed']; }
}
$_SESSION['sess_user_id'] = 1;
$GLOBALS['view_allowed'] = true;
$GLOBALS['resource_allowed'] = false;
if (quicktree_can_edit_tree(5)) {
	throw new RuntimeException('Host ownership denial bypassed');
}
$GLOBALS['resource_allowed'] = true;
if (!quicktree_can_edit_tree(5)) {
	throw new RuntimeException('Host ownership grant ignored');
}
echo "9 authorization cases passed\n";
