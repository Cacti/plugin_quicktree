<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2010-2022 Howard Jones                                    |
 | Copyright (C) 2022-2024 The Cacti Group                                 |
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

$guest_account = true;

chdir('../../');
include_once('include/auth.php');

define('QUICKTREE_BASE_URI', $config['url_path'] . 'plugins/quicktree/');

$form_actions = array(
	1 => __('Save To New Tree', 'quicktree'),
	2 => __('Save To Branch', 'quicktree'),
	3 => __('Clear All Graphs', 'quicktree')
);

$code_actions = array(
	1 => 'add_tree',
	2 => 'add_branch',
	3 => 'clear'
);

set_default_action();

$action = get_request_var('action');
$user   = $_SESSION['sess_user_id'];

/* ================= input validation ================= */
$drp_action = get_filter_request_var('drp_action', FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^([a-zA-Z0-9_]+)$/')));
/* ==================================================== */

if ($drp_action != null) {
	$action = $code_actions[$drp_action];
}

header('action_3_new: '. $action);

switch ($action) {
	case 'add':
		$graph = 0;

		if (isset_request_var('graph_id')) {
			$graph = intval(get_filter_request_var('graph_id'));
			$rra   = intval(get_filter_request_var('rra_id'));

			$title = db_fetch_cell_prepared('SELECT title_cache
				FROM graph_templates_graph
				WHERE local_graph_id = ?',
				array($graph));

			$exists = db_fetch_cell_prepared('SELECT COUNT(*)
				FROM quicktree_graphs
				WHERE local_graph_id = ?',
				array($graph));

			if (!$exists) {
				db_execute_prepared('INSERT INTO quicktree_graphs
					(userid, local_graph_id, rra_id, title)
					VALUES (?, ?, ?, ?)',
					array($user, $graph, $rra, $title));

				$message['title']   = __('Graph Added to QuickTree', 'quicktree');
				$message['message'] = __('Graph %s added to QuickTree.  Goto the QuickTree page to move all Graphs to a new or existing Tree.', $title, 'quicktree');
				$message['level']   = MESSAGE_LEVEL_INFO;
			} else {
				$message['title']   = __('Graph Not Added to QuickTree', 'quicktree');
				$message['message'] = __('Graph %s was NOT added to QuickTree as it\'s already there waiting to be placed on a Tree.  Goto the QuickTree page to move all Graphs to a new or existing Tree.', $title, 'quicktree');
				$message['level']   = MESSAGE_LEVEL_ERROR;
			}
		} else {
			$message['title']   = __('Graph Not Specified.  Not added to QuickTree', 'quicktree');
			$message['message'] = __('No Graph was added to QuickTree as no Graph was specified.', $title);
			$message['level']   = MESSAGE_LEVEL_ERROR;
		}

		print json_encode($message);

		break;
	case 'add_tree':
		if (get_nfilter_request_var('header') == null) {
			top_header();
		}

		form_start('quicktree.php', 'quicktree_form');

		html_start_box($form_actions[$drp_action], '60%', '', '3', 'center', '');

		print '<tr><td>';

		print '<p>' . __('Click \'Continue\' to Add the following Tree.', 'quicktree') . '</p>';

		print '<table class="filterTable">';

		print '<tr>
			<td>
				<p><label for="tree">' . __('Tree Name', 'quicktree') . '</label></p>
			</td>
			<td>
				<input class="ui-state-default ui-corner-all" id="tree" name="tree" value="' . __('New Tree', 'quicktree') . '">
			</td>
		</tr>';

		print '<tr>
			<td>
				<p><label for="branch">' .__('Branch Name', 'quicktree') . '</label></p>
			</td>
			<td>
				<input class="ui-state-default ui-corner-all" id="branch" name="branch" value="' . __('QuickTree', 'quicktree') . '">
			</td>
		</tr></p>';

		print '</table></td></tr>';

		print '<tr><td class="saveRow">
			<input type="hidden" name="action" value="save">
			<input type="button" value="' . __esc('Cancel') . '" onClick="cactiReturnTo()">&nbsp;<input type="submit" value="' . __esc('Continue') . '" title="' . __esc('Add To Branch', 'quicktree') . '">
		</td></tr>';

		html_end_box();

		form_end();

		break;
	case 'add_branch':
		if (get_nfilter_request_var('header') == null) {
			top_header();
		}

		form_start('quicktree.php', 'quicktree_form');

		html_start_box($form_actions[$drp_action], '60%', '', '3', 'center', '');

		$queryrows = db_fetch_assoc("SELECT g.id, g.name
			FROM graph_tree AS g
			ORDER BY g.name");

		print '<tr><td>';

		print '<p>' . __('Click \'Continue\' to Add the following Tree Branch.', 'quicktree') . '</p>';

		print '<p>' . __('Add to which Graph Tree?', 'quicktree') . '</p>';

		print '<table class="filterTable">';

		print '<tr>
			<td>
				<p><label for="tree_id">' . __('Tree Name', 'quicktree') . '</label></p>
			</td>
			<td>
				<select id="tree_id" name="tree_id">';

				if (cacti_sizeof($queryrows)) {
					foreach ($queryrows as $tr) {
						printf('<option value="%d">%s</option>', $tr['id'], html_escape($tr['name']));
					}
				}

				print '</select>
			</td>
		</tr>';

		print '<tr>
			<td>
				<p><label for="branch">' .__('Branch Name', 'quicktree') . '</label></p>
			</td>
			<td>
				<input class="ui-state-default ui-corner-all" id="branch" name="branch" value="' . __('QuickTree', 'quicktree') . '">
			</td>
		</tr></p>';

		print '</table></td></tr>';

		print '<tr><td class="saveRow">
			<input type="hidden" name="action" value="save">
			<input type="button" value="' . __esc('Cancel') . '" onClick="cactiReturnTo()"><input type="submit" value="' . __esc('Continue') . '" title="' . __esc('Add To Branch', 'quicktree') . '">
		</td></tr>';

		html_end_box();

		form_end();

		break;
	case 'clear':
		$SQL = db_execute_prepared('DELETE FROM quicktree_graphs WHERE userid = ?', array($user));

		header('Location: quicktree.php?header=false&drp_action=&action=&location=' . get_nfilter_request_var('location'));

		break;
	case 'save':
		$new_tree_id = -1;
		$parent_id   = 0;

		$username = db_fetch_cell_prepared('SELECT username
			FROM user_auth
			WHERE id = ?',
			array($user));

		if (isset_request_var('tree_id')) {
			$new_tree_id = get_filter_request_var('tree_id');

			$tree_name = db_fetch_cell_prepared('SELECT name
				FROM graph_tree
				WHERE id = ?',
				array($new_tree_id));
		} elseif (isset_request_var('tree')) {
			$new_tree_id = db_fetch_cell_prepared('SELECT id
				FROM graph_tree
				WHERE name = ?',
				array(get_nfilter_request_var('tree')));

			$tree_name = get_nfilter_request_var('tree');
		}

		if (empty($tree_name)) {
			$tree_name = 'New Tree';
		}

		if (isset_request_var('branch') && get_nfilter_request_var('branch') != '') {
			$branch = get_nfilter_request_var('branch');
		} else {
			$branch = '';
		}

		$graphs = db_fetch_assoc_prepared('SELECT *
			FROM quicktree_graphs
			WHERE userid = ?',
			array($user));

		if (cacti_sizeof($graphs)) {
			include_once($config['base_path'] . '/lib/api_tree.php');

			if (empty($new_tree_id)) {
				$seq = db_fetch_cell('SELECT MAX(sequence) FROM graph_tree');

				if ($seq == NULL || $seq < 0) {
					$seq = 1;
				}

				$save = array();
				$save['id']            = '';
				$save['name']          = $tree_name;
				$save['sort_type']     = TREE_ORDERING_ALPHABETIC;
				$save['sequence']      = $seq;
				$save['last_modified'] = date('Y-m-d H:i:s', time());
				$save['modified_by']   = $_SESSION['sess_user_id'];

				if (empty($save['id'])) {
					$save['user_id'] = $_SESSION['sess_user_id'];
				}

				$new_tree_id = sql_save($save, 'graph_tree');

				if ($branch != '') {
					$parent_id = api_tree_item_save(0, $new_tree_id, TREE_ITEM_TYPE_HEADER,
						0, $branch, 0, 0, 0, 1, TREE_ORDERING_INHERIT, false);
				} else {
					$parent_id = 0;
				}
			} else {
				// if an existing tree was picked, create a new heading to
				// be the parent for all our graphs
				$parent_id = db_fetch_cell_prepared('SELECT id FROM graph_tree_items
					WHERE graph_tree_id = ?
					AND title = ?
					AND local_graph_id = 0
					ORDER BY id
					LIMIT 1',
					array($new_tree_id, $branch));

				if (empty($parent_id)) {
					$parent_id = api_tree_item_save(0, $new_tree_id, TREE_ITEM_TYPE_HEADER,
						0, $branch, 0, 0, 0, 1, TREE_ORDERING_INHERIT, false);
				}
			}

			foreach ($graphs as $gr) {
				$tree_item_id = api_tree_item_save(0, $new_tree_id, TREE_ITEM_TYPE_GRAPH,
					$parent_id, '', $gr['local_graph_id'], $gr['rra_id'], 0, 1, TREE_ORDERING_INHERIT, false);
			}

			raise_message('tree_done', __('QuickTree has Created Tree and/or Branch and added Graphs', 'quicktree'), MESSAGE_LEVEL_INFO);

			if ($parent_id > 0) {
				$url = $config['url_path'] . 'graph_view.php?action=tree&node=tbranch-' . $parent_id . '&site_id=-1&host_id=-1&host_template_id=-1&hgd=&hyper=true';
			} else {
				$url = $config['url_path'] . 'graph_view.php?action=tree&node=tree_anchor-' . $new_tree_id . '&site_id=-1&host_id=-1&host_template_id=-1&hgd=&hyper=true';
			}

			?>
			<script type="text/javascript"> $(function() { document.location = "<?php print $url;?>"; }); </script>
			<?php

			exit;
		} else {
			raise_message('nographs', __('QuickTree has no Graphs Queued', 'quicktree'), MESSAGE_LEVEL_ERROR);

			header('Location: ' . $config['url_path'] . 'plugins/quicktree/quicktree.php');
			exit;
		}

		break;
	case 'remove':
		$graph = 0;

		if (isset_request_var('id')) {
			$graph = get_filter_request_var('id');

			$result = db_execute_prepared('DELETE FROM quicktree_graphs
				WHERE userid = ?
				AND id = ?;',
				array($user, $graph));
		}

		header('Location: quicktree.php?location=' . get_nfilter_request_var('location'));

		break;
	case 'add_ajax':
		header('Content-type: text/plain');

		print "{ status: 'OK' }";

		break;
	default:
		if (get_nfilter_request_var('location') == 'console') {
			top_header();
		} else {
			general_header();
		}

		form_start('quicktree.php?location=' . get_nfilter_request_var('location'), 'quicktree_form');
		html_start_box(__('QuickTree', 'quicktree'), '100%', true, '3', 'center', '');

		print "<div class='spacer formHeader collapsible' id='row_info'>
			<div class='formHeaderText'>" . __('Information/Directions', 'quicktree') . "
				<div class='formHeaderAnchor'>
					<i class='fa fa-angle-double-up'></i>
				</div>
			</div>
		</div>";

		print "<table class='cactiTable' id='row_info_child'>";

		$form_items = array(
			array(__('The Graphs below are Queue to be added to a Cacti Tree.  You may keep them here for as long as you like, or you can perform one of the following actions', 'quicktree')),
			array('<b>' . __('Save To New Tree', 'quicktree') . '</b>', __('Save your selection to a new Graph Tree so you can keep them for later and work on something new.', 'quicktree')),
			array('<b>' . __('Save To Branch', 'quicktree') . '</b>', __('Save your selection as a branch to an existing tree so that they appear in a specific section of an existing tree.', 'quicktree')),
			array('<b>' . __('Clear all graphs', 'quicktree') . '</b>', __('Clear the Graphs on this page from the Graphs Queue so that you have a blank QuickTree ready for new selections', 'quicktree')),
			array('<hr>' . __('You can manage the individual graphs that appear here by clicking:', 'quicktree')),
			array('<i class="deviceUp fas fa-plus-circle"></i>' . __('Add', 'quicktree'), __('This icon is next to a Graph on the %s tab.', '<a href="../../graph_view.php">' . __('Graph View Page', 'quicktree') . '</a>', 'quicktree')),
			array('<i class="deviceDown fas fa-times-circle"></i>' . __('Delete', 'quicktree'), __('This icon next to the Graphs below to remove them from the QuickTree Queue.', 'qucktree')),
			array('<hr><b>' . __('Note:', 'quicktree') . '</br>'),
			array(__('Adding, removing or clearing on this page does not affect any other parts of Cacti (only Creating/Saving does)', 'quicktree'))
		);

		foreach ($form_items as $details) {
			form_alternate_row();

			if (cacti_sizeof($details) == 1) {
				print '<td style=\'vertical-align:top;\' colspan=\'2\'>'.$details[0].'</td>';
			} else {
				print '<td class=\'nowrap\' style=\'vertical-align:top;\'>'.$details[0].'</td>';
				print '<td>'.$details[1].'</td>';
			}

			form_end_row();
		}

		print '</table>';
		print '<div class="spacer formHeader" id="row_action"><div class="formHeaderText">' . __('Actions', 'quicktree') . '</div></div>';

		draw_actions_dropdown($form_actions);

		html_end_box(false,true);

		form_end();

		print '<hr>';

		$queryrows = db_fetch_assoc_prepared('SELECT qt.*, gtg.title_cache
			FROM quicktree_graphs AS qt
			INNER JOIN graph_templates_graph AS gtg
			ON qt.local_graph_id = gtg.local_graph_id
			WHERE userid = ?',
			array($user));

        if (cacti_sizeof($queryrows)) {
			foreach ($queryrows as $gr) {
				$graph_title = html_escape($gr['title_cache']);

				print '<table class="cactiTable"><thead><tr><th class="center">' . $graph_title;

				print '&nbsp;&nbsp;<a class="pic iconLink" href="' . html_escape('quicktree.php?location=' . get_nfilter_request_var('location') . '&action=remove&id=' . $gr['id'])
					. '" title="' . __esc('Remove This Graph From QuickTree', 'quicktree') . '"><i class="deviceDown fas fa-times-circle"></i></a>';
				print '</th></tr></thead>';

				print '<tbody><tr><td style="padding:5px;" class="center">';
				print '<a style="padding:5px" class="pic" href="' . html_escape($config['url_path'] . 'graph.php?action=view&rra_id=all&local_graph_id=' . $gr['local_graph_id']) . '"><img class="graphimage" id="graph_' . $gr['local_graph_id'] . '" src="' . $config['url_path'] . '/graph_image.php?action=view&local_graph_id=' . $gr['local_graph_id'] . '&rra_id=' . $gr['rra_id'] . '" alt="' . $graph_title . '"></a>';

				print '</td></tr></tbody></table>';
            }

            print '<hr>';
        } else {
            print '<p><em>' . __('No Graphs Added Yet', 'quicktree') . '</em></p>';
        }

		bottom_footer();

		break;
}

