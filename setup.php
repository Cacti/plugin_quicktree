<?php

function plugin_quicktree_version()
{
    global $config;
    $info = parse_ini_file($config['base_path'] . '/plugins/quicktree/INFO', true);
    return $info['info'];
}

function quicktree_config_settings()
{
    global $tabs, $settings;
    $tabs["misc"] = "Misc";

    $temp = array(
        "quicktree_header" => array(
            "friendly_name" => __("Quicktree"),
            "method" => "spacer",
        ),
        "quicktree_pagestyle" => array(
            "friendly_name" => __("Page style"),
            "description" => __("Where to display the QuickTree"),
            "method" => "drop_array",
            "array" => array(
                0 => __("Tab"),
                1 => __("Console Menu"),
                2 => __("Both Tab and Console Menu")
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

    quicktree_setup_table();

    return true;
}

function quicktree_show_tab()
{
    global $config;

    if (api_user_realm_auth('quicktree.php')) {
        $cp = false;
        if (basename($_SERVER['PHP_SELF']) == 'quicktree.php') {
            $cp = true;
        }

        print '<a href="' . $config['url_path'] . 'plugins/quicktree/quicktree.php"><img src="' . $config['url_path'] . 'plugins/quicktree/images/tab_quicktree' . ($cp ? '_active' : '') . '.gif" alt="quicktree" align="absmiddle" border="0"></a>';
    }
}

function quicktree_graph_buttons($data)
{
    global $config;

    if (api_user_realm_auth('quicktree.php')) {

        $local_graph_id = $data[1]['local_graph_id'];
        $rra_id = $data[1]['rra'];

        print '<a title="Add this graph to QuickTree" href="' . $config['url_path']
            . 'plugins/quicktree/quicktree.php?action=add&rra_id=' . $rra_id . '&graph_id=' . $local_graph_id
            . '"><img src="' . $config['url_path']
            . 'plugins/quicktree/images/add.png" border="0" alt="Add this graph to QuickTree" style="padding: 3px;"></a><br>';
    }
}

function quicktree_config_arrays()
{
    global $menu;

    api_plugin_register_realm("quicktree", 'quicktree.php', __('Plugin -> QuickTree: Access'), 1);

    if (read_config_option("quicktree_pagestyle") > 0) {
        $menu["Management"]['plugins/quicktree/quicktree.php'] = "QuickTree";
    }
}

function quicktree_page_head()
{
    global $config;

    if (strstr($_SERVER['REQUEST_URI'], "quicktree.php") !== false) {
        print '<script type="text/javascript" src="' . $config['url_path'] . 'plugins/quicktree/quicktree.js"></script>';
        print '<link rel="stylesheet" href="' . $config['url_path'] . 'plugins/quicktree/quicktree.css"></link>';
    }
}

function quicktree_draw_navigation_text($nav)
{
    $nav["quicktree.php:"] = array
    (
        "title" => __("QuickTree"),
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    $nav["quicktree.php:add_ajax"] = array
    (
        "title" => __("QuickTree"),
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    $nav["quicktree.php:add"] = array
    (
        "title" => __("QuickTree"),
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    $nav["quicktree.php:remove"] = array
    (
        "title" => __("QuickTree"),
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    $nav["quicktree.php:save"] = array
    (
        "title" => __("QuickTree"),
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    $nav["quicktree.php:clear"] = array
    (
        "title" => __("QuickTree"),
        "mapping" => "index.php:",
        "url" => "quicktree.php",
        "level" => "1"
    );

    return $nav;
}

function quicktree_setup_table()
{
    $data = array();

    $data['columns'][] = array('name' => 'id', 'type' => 'int(11)', 'NULL' => false, 'auto_increment' => true);
    $data['columns'][] = array('name' => 'userid', 'type' => 'int(11)', 'NULL' => false);
    $data['columns'][] = array('name' => 'local_graph_id', 'type' => 'int(11)', 'NULL' => false, 'default' => '0');
    $data['columns'][] = array('name' => 'rra_id', 'type' => 'int(11)', 'NULL' => false, 'default' => '0');
    $data['columns'][] = array('name' => 'title', 'type' => 'varchar(191)', 'NULL' => false, 'default' => '');
    $data['primary'] = 'id';
    $data['type'] = 'InnoDB';
    $data['comment'] = 'Quicktree data';
    api_plugin_db_table_create('quicktree', 'quicktree_graphs', $data);


    $pagestyle = read_config_option("quicktree_pagestyle");

    if ($pagestyle == '' or $pagestyle < 0 or $pagestyle > 2) {
        db_execute("replace into settings values('quicktree_pagestyle',0)");
    }
}


function plugin_quicktree_upgrade()
{
    /* Here we will upgrade to the newest version */
    quicktree_check_upgrade();
    return FALSE;
}

function plugin_quicktree_uninstall()
{
    /* Do any extra Uninstall stuff here */
}

function plugin_quicktree_check_config()
{
    /* Here we will check to ensure everything is configured */
    return TRUE;
}


// vim:ts=4:sw=4:
