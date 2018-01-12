<?php
$guest_account = true;

chdir('../../');
include_once('include/auth.php');

$action = "";

$form_actions = array(
	1 => __('Save To New Tree'),
	2 => __('Save To Branch'),
	3 => __('Clear All Graphs')
);

set_default_action();

$user = $_SESSION["sess_user_id"];

switch ($action) {
    case 'add':
        $graph = 0;

        if (isset($_GET['graph_id'])) {
            $graph = intval($_GET['graph_id']);
            $rra = intval($_GET['rra_id']);

            $title = db_fetch_cell_prepared("select title_cache from graph_templates_graph where local_graph_id=?", array($graph));

            db_execute_prepared("insert into quicktree_graphs (userid,local_graph_id,rra_id,title) values (?,?,?,?)", array($user,$graph,$rra,$title));
        }
        header("Location: quicktree.php");
        break;

    case 'clear':
        $SQL = db_execute_prepared("delete from  quicktree_graphs where userid=?;", array($user));
        header("Location: quicktree.php");
        break;

    case 'save':
        $new_tree_id = -1;
        $parent_id = 0;
        $username = db_fetch_cell_prepared("select username from user_auth where id=?", array($user));

        if (isset($_REQUEST['tree_id'])) {
            $new_tree_id = intval($_REQUEST['tree_id']);
        }

        if ($new_tree_id > 0) {
            $tree_name = "saved quicktree for user '" . $username . "' on " . date("r");
        } else {
            $tree_name = "saved quicktree for user '" . $username . "'";
        }

        $graphs = db_fetch_assoc_prepared("select * from quicktree_graphs where userid=?", array($user));

        if (sizeof($graphs) > 0) {
	    include_once($config['base_path'] . '/lib/api_tree.php');
            // if no existing tree was picked, create one
            if ($new_tree_id < 1) {
                $seq = db_fetch_cell("select max(sequence) from graph_tree");
                if (seq == NULL || $seq < 0) {
                    $seq = 1;
                }
                $save = array();
                $save["id"] = "";
                $save["name"] = $tree_name;
                $save["sort_type"] = TREE_ORDERING_NONE;
                $save["sequence"] = $seq;
                $save['last_modified'] = date('Y-m-d H:i:s', time());
                $save['modified_by']   = $_SESSION['sess_user_id'];
                if (empty($save['id'])) {
                        $save['user_id'] = $_SESSION['sess_user_id'];
                }

                $new_tree_id = sql_save($save, "graph_tree");
                //sort_tree(SORT_TYPE_TREE, $new_tree_id, TREE_ORDERING_NONE);
            } else {
                // if an existing tree was picked, create a new heading to
                // be the parent for all our graphs

                $parent_id = api_tree_item_save(0, $new_tree_id, TREE_ITEM_TYPE_HEADER,
                    0, // all items are children of the root item
                    $tree_name, 0, 0, 0, 1, 1, false);
            }

            foreach ($graphs as $gr) {
                $tree_item_id = api_tree_item_save(0, $new_tree_id, TREE_ITEM_TYPE_GRAPH,
                    $parent_id, // all items are children of the root item
                    "", $gr['local_graph_id'], $gr['rra_id'], 0, 1, 1, false);
            }
        }

        header("Location: ../../tree.php?action=edit&id=" . $new_tree_id);
        break;

    case 'remove':
        $graph = 0;

        if (isset($_GET['id'])) {
            $graph = intval($_GET['id']);
            $result = db_execute_prepared("delete from  quicktree_graphs where userid=? and id=?;", array($user, $graph));
        }
        header("Location: quicktree.php");
        break;

    case 'add_ajax':
        header('Content-type: text/plain');

        print "{ status: 'OK' }";
        break;

    default:
	top_header();
	form_start('quicktree.php','quicktree_form');
        html_start_box('QuickTree', '100%', true, '3', 'center', '');

	print "<div class='spacer formHeader collapsible' id='row_info'><div class='formHeaderText'>Information<div class='formHeaderAnchor'><i class='fa fa-angle-double-up'></i></div></div></div>";
	print "<table class='cactiTable' id='row_info_child' width='100%'>";
	$form_items = array(
		array('<br>These are the graphs that you have added to your QuickTree. You can keep them here for as long as you like, or you can perform one of the following actions:<br>&nbsp;'),
		array('Save To New Tree','Save your selection to a new Graph Tree so you can keep them for later and work on something new.'),
		array('Save To Branch','Save your selection as a branch to an existing tree so that they appear in a specific section of an existing tree.'),
		array('Clear all graphs','Clear the page so that you have a blank QuickTree reading for new selections'),
		array('<br>You can manage the individual graphs that appear here by clicking:<br>&nbsp;'),
		array('<img src=\'images/add.png\'> Add','This icon is next to a graph on the <a href="../../graph_view.php">graph</a> tab.'),
		array('<img src=\'images/delete.png\'> Delete','This icon next to the graph on this page.'),
                array('&lt;graph&gt;','Any graph itself to see the full history of it.'),
		array('<br>Don\'t Worry!<ul><li>Each user gets their own QuickTree as the idea is to collect together the graphs <b>you</b> want to quickly monitor, as easily as possible.</li><li>Adding, removing or clearing on this page does not affect any other parts of Cacti (only Creating/Saving does)</li></ul>')
	);

	foreach ($form_items as $details) {
		form_alternate_row();
		if (sizeof($details) == 1) {
			print '<td style=\'vertical-align:top;\' colspan=\'2\'>'.$details[0].'</td>';
		} else {
			print '<td class=\'nowrap\' style=\'vertical-align:top;\'>'.$details[0].'</td>';
			print '<td>'.$details[1].'</td>';
		}
		form_end_row();
	}
	print '</table>';
	print "<div class='spacer formHeader' id='row_actio'><div class='formHeaderText'>Actions</div></div>";
	draw_actions_dropdown($form_actions);
	html_end_box(false,true);
	form_end();

        $SQL = "select g.id, g.name from graph_tree g order by g.name";
        $queryrows = db_fetch_assoc($SQL);
        print "<div id='qt_treeselector'>";
        if (sizeof($queryrows) > 0) {
            print "<h3>Add to which graph tree?</h3><form method='post' action='quicktree.php'><input name='action' type='hidden' value='save' /><select name='tree_id'>";
            foreach ($queryrows as $tr) {
                printf("<option value='%d'>%s</option>", $tr['id'], htmlspecialchars($tr['name']));
            }
            print "</select><input type='submit' value='Add to this tree' /></form>";
        } else {
            print "<p>Unable to find any trees to add graphs to</p>";
        }
        print "</div>";

        print "<hr>";

        $queryrows = db_fetch_assoc_prepared("select qt.*, gtg.title_cache from quicktree_graphs qt,graph_templates_graph gtg where qt.local_graph_id = gtg.local_graph_id and userid=?", array($user));

        if (sizeof($queryrows) > 0) {
            foreach ($queryrows as $gr) {
                $graph_title = $gr['title_cache'];
                print "<table><thead><tr><th>";
                print htmlspecialchars($graph_title);
                print "&nbsp;&nbsp;<a href='quicktree.php?action=remove&id=" . $gr['id']
                    . "'><img border=0 src='images/delete.png' title='Remove This Graph From QuickTree'></a>";
                print "</th></tr></thead>\n";
                print "<tbody><tr><td>";
                ?>

                <a href="../../graph.php?action=view&rra_id=all&local_graph_id=<?php print $gr['local_graph_id']; ?>"><img
                            class='graphimage'
                            id='graph_<?php print $gr["local_graph_id"] ?>'
                            src='../../graph_image.php?action=view&local_graph_id=<?php print $gr["local_graph_id"]; ?>&rra_id=<?php print $gr["rra_id"]; ?>'
                            border='0' alt='<?php print $graph_title; ?>'></a>

                <?php
                print "</td></tr></tbody></table>\n";
            }

            print "<hr>";
        } else {
            print "<p><em>No graphs yet</em></p>";
        }

        bottom_footer();
        break;
}
