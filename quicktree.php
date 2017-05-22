<?php
$guest_account = true;

chdir('../../');

include_once("./include/auth.php");
include_once("./include/global.php");
include_once($config["library_path"] . "/tree.php");
include_once($config["library_path"] . "/api_tree.php");


$action = "";

if (isset($_REQUEST['action']))
{
    $action = $_REQUEST['action'];
}
$user = $_SESSION["sess_user_id"];

switch ($action)
{
    case 'add':
        $graph = 0;

        if (isset($_GET['graph_id']))
        {
            $graph = intval($_GET['graph_id']);
            $rra = intval($_GET['rra_id']);

            $title = mysql_escape_string(
                db_fetch_cell("select title_cache from graph_templates_graph where local_graph_id=$graph"));

            $SQL =
                "insert into quicktree_graphs (userid,local_graph_id,rra_id,title) values ($user,$graph,$rra,'$title');";
            $result = db_execute($SQL);
        }
        header("Location: quicktree.php");
        break;

    case 'clear':
        $SQL = "delete from  quicktree_graphs where userid=$user;";
        $result = db_execute($SQL);
        header("Location: quicktree.php");
        break;

    case 'save':

        $new_tree_id = -1;
        $parent_id = 0;
        $username = db_fetch_cell("select username from user_auth where id=$user");
        
        if(isset($_REQUEST['tree_id']))
        {
            $new_tree_id = intval($_REQUEST['tree_id']);
        }

        if($new_tree_id > 0)
        {
            $tree_name = "saved quicktree for user '" . $username . "' on ".date("r");
        }
        else
        {
            $tree_name = "saved quicktree for user '" . $username . "'";
        }

        $SQL = sprintf("select * from quicktree_graphs where userid=%d", $user);
        $graphs = db_fetch_assoc($SQL);

        if (sizeof($graphs) > 0)
        {
            // if no existing tree was picked, create one
            if($new_tree_id < 1)
            {
                $save = array ();
                $save["id"] = "";
                $save["name"] = $tree_name;
                $save["sort_type"] = TREE_ORDERING_NONE;

                $new_tree_id = sql_save($save, "graph_tree");
                sort_tree(SORT_TYPE_TREE, $new_tree_id, TREE_ORDERING_NONE);
            }
            else
            {
                // if an existing tree was picked, create a new heading to
                // be the parent for all our graphs

                $parent_id = api_tree_item_save(0, $new_tree_id, TREE_ITEM_TYPE_HEADER,
                    0, // all items are children of the root item
                    $tree_name, 0, 0, 0, 1, 1, false);
            }

            foreach ($graphs as $gr)
            {
                $tree_item_id = api_tree_item_save(0, $new_tree_id, TREE_ITEM_TYPE_GRAPH,
                    $parent_id, // all items are children of the root item
                "", $gr['local_graph_id'], $gr['rra_id'], 0, 1, 1, false);
            }
        }

        header("Location: ../../tree.php?action=edit&id=" . $new_tree_id);
        break;

    case 'remove':
        $graph = 0;

        if (isset($_GET['id']))
        {
            $graph = intval($_GET['id']);

            $SQL = "delete from  quicktree_graphs where userid=$user and id=$graph;";
            $result = db_execute($SQL);
        }
        header("Location: quicktree.php");
        break;

    case 'add_ajax':
        header('Content-type: text/plain');

        print "{ status: 'OK' }";
        break;

    default:
        include_once($config["base_path"] . "/include/top_header.php");

        print
            "<p>These are the graphs that you have added to your QuickTree. You can keep them here for as long as you like, or you can <a href='quicktree.php?action=save'>save them to a new Graph Tree</a> so you can keep them for later and work on something new. (you can also <a id='qt_existing'>save them as a branch to an existing tree</a>) The idea is to collect together the graphs for a situation you are monitoring, as easily as possible.</p>";
        
        $SQL = "select g.id, g.name from graph_tree g order by g.name";
        $queryrows = db_fetch_assoc($SQL);
        if (sizeof($queryrows) > 0)
        {
            print "<div id='qt_treeselector'><h3>Add to which graph tree?</h3><form method='post' action='quicktree.php'><input name='action' type='hidden' value='save' /><select name='tree_id'>";
# <option value=1>1<option value=2>2
            foreach ($queryrows as $tr)
            {
                printf("<option value='%d'>%s</option>", $tr['id'], htmlspecialchars($tr['name']));
            }
            print "</select><input type='submit' value='Add to this tree' /></form></div>";
        }
        
        print
            "<p>You can add more graphs here by clicking the <img src='images/add.png'> icon next to a graph. You can remove them from this list by clicking the <img src='images/delete.png'> next to the graph on this page. You can see the full history for any graph by clicking on it. Finally, you can <a href='quicktree.php?action=clear'>clear all the graphs from this QuickTree</a>.</p>";
        print
            "<p>Don't Worry! Deleting a graph on this page does not affect the rest of Cacti. Each user gets their own QuickTree, if they have permission.</p>";
        print "<hr>";

        $SQL =
            "select qt.*, gtg.title_cache from quicktree_graphs qt,graph_templates_graph gtg where qt.local_graph_id = gtg.local_graph_id and userid="
            . $user;
        $queryrows = db_fetch_assoc($SQL);

        if (sizeof($queryrows) > 0)
        {
            foreach ($queryrows as $gr)
            {
                # print $gr['local_graph_id']."/".$gr['rra_id'];
                $graph_title = $gr['title_cache'];
                print "<table><thead><tr><th>";
                print htmlspecialchars($graph_title);
                print "&nbsp;&nbsp;<a href='quicktree.php?action=remove&id=" . $gr['id']
                    . "'><img border=0 src='images/delete.png' title='Remove This Graph From QuickTree'></a>";
                print "</th></tr></thead>\n";
                print "<tbody><tr><td>";
?>

            <a href = "../../graph.php?action=view&rra_id=all&local_graph_id=<?php print $gr['local_graph_id']; ?>"><img class = 'graphimage'
                id = 'graph_<?php print $gr["local_graph_id"] ?>'
                src = '../../graph_image.php?action=view&local_graph_id=<?php print $gr["local_graph_id"];?>&rra_id=<?php print $gr["rra_id"];?>'
                border = '0' alt = '<?php print $graph_title;?>'></a>

<?php
            print "</td></tr></tbody></table>\n";
            }

            print "<hr>";
        }
        else
        {
            print "<p><em>No graphs yet</em></p>";
        }

        include_once($config["base_path"] . "/include/bottom_footer.php");
        break;
}
?>