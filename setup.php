<?php
function plugin_quicktree_version() { return array
(
    'name' => 'quicktree',
    'version' => '0.2',
    'longname' => 'QuickTree',
    'author' => 'Howard Jones',
    'homepage' => 'http://wotsit.thingy.com/haj/cacti/quicktree-plugin.html',
    'email' => 'howie@thingy.com',
    'url' => 'http://wotsit.thingy.com/haj/cacti/versions.php'
); }

function quicktree_version() { return (plugin_quicktree_version()); }

function quicktree_config_settings()
{
    global $tabs, $settings;
    $tabs["misc"] = "Misc";

    $temp = array (
        "quicktree_header" => array (
            "friendly_name" => "Quicktree",
            "method" => "spacer",
        ),
        "quicktree_pagestyle" => array (
            "friendly_name" => "Page style",
            "description" => "Where to display the QuickTree",
            "method" => "drop_array",
            "array" => array (
                0 => "Tab",
                1 => "Console Menu",
                2 => "Both Tab and Console Menu"
            )
        )
    );

    if (isset($settings["misc"])) {
        $settings["misc"] = array_merge($settings["misc"], $temp);
    } else {
        $settings["misc"] = $temp;
    }
}


function plugin_quicktree_install()
{
    api_plugin_register_hook('quicktree', 'top_header_tabs', 'quicktree_show_tab', "setup.php");
    api_plugin_register_hook('quicktree', 'top_graph_header_tabs', 'quicktree_show_tab', "setup.php");
    api_plugin_register_hook('quicktree', 'config_arrays', 'quicktree_config_arrays', "setup.php");
    api_plugin_register_hook('quicktree', 'config_settings', 'quicktree_config_settings', "setup.php");
    api_plugin_register_hook('quicktree', 'draw_navigation_text', 'quicktree_draw_navigation_text', "setup.php");
    api_plugin_register_hook('quicktree', 'graph_buttons', 'quicktree_graph_buttons', "setup.php");
    api_plugin_register_hook('quicktree', 'graph_buttonsgre', 'quicktree_graph_buttons', "setup.php");
    
    api_plugin_register_hook('quicktree', 'page_head', 'quicktree_page_head', "setup.php");

    return (true);
}

function quicktree_show_tab()
{
    global $config, $user_auth_realms, $user_auth_realm_filenames;

    $realm_id2 = 0;

        
    if (isset($user_auth_realm_filenames[basename('quicktree.php')]))
    {
        $realm_id2 = $user_auth_realm_filenames[basename('quicktree.php')];
    }

    $tabname = "tab_quicktree.gif";

    if (strstr($_SERVER['REQUEST_URI'], '/plugins\/quicktree\/quicktree.php/') != false)
    {
        $tabname = "tab_quicktree_red.gif";
    }

    if ((db_fetch_assoc("select user_auth_realm.realm_id from user_auth_realm where user_auth_realm.user_id='"
        . $_SESSION["sess_user_id"] . "' and user_auth_realm.realm_id='$realm_id2'")) || (empty($realm_id2)))
    {

        if(intval(read_config_option("quicktree_pagestyle")) != 1) {

        print '<a id="qt_link" href="' . $config['url_path']
            . 'plugins/quicktree/quicktree.php"><img class="qt_drophover" id="qt_tab" src="' . $config['url_path']
            . 'plugins/quicktree/images/';
        print $tabname;
        print '" alt="quicktree" align="absmiddle" border="0"></a>';
       }
    }

    quicktree_setup_table();
}

function quicktree_graph_buttons($data)
{
// $user = db_fetch_row("select * from user_auth where id=" .$_SESSION["sess_user_id"]. " and realm = 0");
// print "X";
// if (sizeof(db_fetch_assoc("select realm_id from user_auth_realm where user_id=" . $user["id"] . " and realm_id=212")) > 0)
// {	global $config;

    global $config, $user_auth_realms, $user_auth_realm_filenames;
    $realm_id2 = 0;

    if (isset($user_auth_realm_filenames[basename('quicktree.php')]))
    {
        $realm_id2 = $user_auth_realm_filenames[basename('quicktree.php')];
    }

    if ((db_fetch_assoc("select user_auth_realm.realm_id from user_auth_realm where user_auth_realm.user_id='"
        . $_SESSION["sess_user_id"] . "' and user_auth_realm.realm_id='$realm_id2'")) || (empty($realm_id2)))
    {

        $local_graph_id = $data[1]['local_graph_id'];
        $rra_id = $data[1]['rra'];

        # print_r($data);

        print '<a title="Add this graph to QuickTree" href="' . $config['url_path']
            . 'plugins/quicktree/quicktree.php?action=add&rra_id=' . $rra_id . '&graph_id=' . $local_graph_id
            . '"><img src="' . $config['url_path']
            . 'plugins/quicktree/images/add.png" border="0" alt="Add this graph to QuickTree" style="padding: 3px;"></a><br>';
    }
}

function quicktree_config_arrays()
{
    global $user_auth_realms, $user_auth_realm_filenames, $menu;

    $user_auth_realms[212] = 'Plugin -> QuickTree: Access';
    $user_auth_realm_filenames['quicktree.php'] = 212;

    if(read_config_option("quicktree_pagestyle")>0) {
        $menu["Management"]['plugins/quicktree/quicktree.php'] = "QuickTree";
    }
}

function quicktree_page_head()
{
    global $config;

   if( strstr($_SERVER['REQUEST_URI'], "quicktree.php") !== false) {
        print '<script type="text/javascript" src="' . $config['url_path']
            . 'plugins/quicktree/jquery-latest.min.js"></script>';
        print '<script type="text/javascript">jQuery.noConflict();</script>';
        # print '<script type="text/javascript" src="' . $config['url_path'] . 'plugins/quicktree/interface.js"></script>';
        print '<script type="text/javascript" src="' . $config['url_path'] . 'plugins/quicktree/quicktree.js"></script>';
        print '<link rel="stylesheet" href="' . $config['url_path'] . 'plugins/quicktree/quicktree.css"></link>';
   }
}

function quicktree_draw_navigation_text($nav)
{
    $nav["quicktree.php:"] = array
    (
        "title" => "QuickTree",
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    $nav["quicktree.php:add_ajax"] = array
    (
        "title" => "QuickTree",
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    $nav["quicktree.php:add"] = array
    (
        "title" => "QuickTree",
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    $nav["quicktree.php:remove"] = array
    (
        "title" => "QuickTree",
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    $nav["quicktree.php:save"] = array
    (
        "title" => "QuickTree",
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    $nav["quicktree.php:clear"] = array
    (
        "title" => "QuickTree",
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    return ($nav);
}

function quicktree_setup_table()
{
    global $config, $database_default;
    include_once($config["library_path"] . DIRECTORY_SEPARATOR . "database.php");

    $sql = "show tables from " . $database_default;
    $result = db_fetch_assoc($sql) or die(mysql_error());

    $tables = array ();
    $sql = array ();

    foreach ($result as $index => $arr)
    {
        foreach ($arr as $t)
        {
            $tables[] = $t;
        }
    }

    if (!in_array('quicktree_graphs', $tables))
    {
        $sql[]
            = "CREATE TABLE quicktree_graphs (
                        id int(11) NOT NULL auto_increment,
                        userid int(11) NOT NULL,
  			local_graph_id mediumint(8) unsigned NOT NULL default '0',
  			rra_id smallint(8) unsigned NOT NULL default '0',
  			title varchar(255) default NULL,
                        PRIMARY KEY  (id)
                ) TYPE=MyISAM;";
    }

 $pagestyle = read_config_option("quicktree_pagestyle");

        if ($pagestyle == '' or $pagestyle < 0 or $pagestyle > 2) {
            $sql[] = "replace into settings values('quicktree_pagestyle',0)";
        }


    if (!empty($sql))
    {
        for ($a = 0; $a < count($sql); $a++)
        {
            $result = db_execute($sql[$a]);
        }
    }
}




function plugin_quicktree_upgrade () {
        /* Here we will upgrade to the newest version */
        quicktree_check_upgrade();
        return FALSE;
}

function plugin_quicktree_uninstall () {
        /* Do any extra Uninstall stuff here */
}

function plugin_quicktree_check_config () {
        /* Here we will check to ensure everything is configured */
        quicktree_check_upgrade();
        return TRUE;
}

function quicktree_check_upgrade () {
        global $config;

        $files = array('index.php', 'plugins.php');
        if (isset($_SERVER['PHP_SELF']) && !in_array(basename($_SERVER['PHP_SELF']), $files)) {
                return;
        }

        $current = plugin_quicktree_version();
        $current = $current['version'];
        $old     = db_fetch_row("SELECT * FROM plugin_config WHERE directory='quicktree'");
        if (sizeof($old) && $current != $old["version"]) {
                /* if the plugin is installed and/or active */
                if ($old["status"] == 1 || $old["status"] == 4) {
                        /* re-register the hooks */
                        plugin_quicktree_install();

                        /* perform a database upgrade */
                        quicktree_database_upgrade();
                }

                /* update the plugin information */
                $info = plugin_quicktree_version();
                $id   = db_fetch_cell("SELECT id FROM plugin_config WHERE directory='quicktree'");
                db_execute("UPDATE plugin_config
                        SET name='" . $info["longname"] . "',
                        author='"   . $info["author"]   . "',
                        webpage='"  . $info["homepage"] . "',
                        version='"  . $info["version"]  . "'
                        WHERE id='$id'");
        }
}

function quicktree_database_upgrade() {
        global $plugins, $config;
        return TRUE;
}


// vim:ts=4:sw=4:
?>
