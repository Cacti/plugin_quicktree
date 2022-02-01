<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2010-2022 Howard Jones                                    |
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

function plugin_quicktree_install() {
	api_plugin_register_hook('quicktree', 'top_header_tabs',          'quicktree_show_tab',             'setup.php');
	api_plugin_register_hook('quicktree', 'top_graph_header_tabs',    'quicktree_show_tab',             'setup.php');
	api_plugin_register_hook('quicktree', 'config_arrays',            'quicktree_config_arrays',        'setup.php');
	api_plugin_register_hook('quicktree', 'config_settings',          'quicktree_config_settings',      'setup.php');
	api_plugin_register_hook('quicktree', 'draw_navigation_text',     'quicktree_draw_navigation_text', 'setup.php');
	api_plugin_register_hook('quicktree', 'graph_buttons',            'quicktree_graph_buttons',        'setup.php');
	api_plugin_register_hook('quicktree', 'graph_buttons_thumbnails', 'quicktree_graph_buttons',        'setup.php');

	api_plugin_register_hook('quicktree', 'page_head', 'quicktree_page_head', 'setup.php');

	quicktree_setup_table();

	return true;
}

function plugin_quicktree_version() {
	global $config;

	$info = parse_ini_file($config['base_path'] . '/plugins/quicktree/INFO', true);
	return $info['info'];
}

function quicktree_config_settings() {
	global $tabs, $settings;

	$tabs['misc'] = __('Misc');

	$temp = array(
		'quicktree_header' => array(
			'friendly_name' => __('Quicktree', 'quicktree'),
			'method' => 'spacer',
		),
		'quicktree_pagestyle' => array(
			'friendly_name' => __('Page Style', 'quicktree'),
			'description' => __('Where to display the QuickTree page', 'quicktree'),
			'method' => 'drop_array',
			'array' => array(
				0 => __('Tab', 'quicktree'),
				1 => __('Console Menu', 'quicktree'),
				2 => __('Both Tab and Console Menu', 'quicktree')
			)
		)
	);

	if (isset($settings['misc'])) {
		$settings['misc'] = array_merge($settings['misc'], $temp);
	} else {
		$settings['misc'] = $temp;
	}
}

function quicktree_page_location($preferred = 'tab') {
	$locsetting = read_config_option('quicktree_pagestyle');

	if ($locsetting == 2) {
		return $preferred;
	}

	if ($preferred == 'tab') {
		if ($locsetting == 0) {
			return $preferred;
		} else {
			return 'console';
		}
	} else {
		if ($locsetting == 1) {
			return $preferred;
		} else {
			return 'tab';
		}
	}
}

function quicktree_show_tab() {
	global $config;

	if (api_user_realm_auth('quicktree.php')) {
		$cp = false;
		if (basename($_SERVER['PHP_SELF']) == 'quicktree.php') {
			$cp = true;
		}

		if (read_config_option('quicktree_pagestyle') != 1) {
			print '<a href="' . $config['url_path'] . 'plugins/quicktree/quicktree.php?location=tab' . quicktree_page_location('tab') . '"><img src="' . $config['url_path'] . 'plugins/quicktree/images/tab_quicktree' . ($cp ? '_active' : '') . '.gif" alt="' . __esc('Quicktree', 'quicktree') . '"></a>';
		}
	}
}

function quicktree_graph_buttons($data) {
	global $config;

	if (api_user_realm_auth('quicktree.php')) {
		$local_graph_id = $data[1]['local_graph_id'];
		$rra_id         = $data[1]['rra'];

		print "<a class='iconLink' onClick='addQuickTree($local_graph_id, $rra_id)' title='" . __esc('Add this graph to QuickTree', 'quicktree') . "' href='#'><i class='deviceUp fas fa-plus-circle'></i></a><br>";
	}
}

function quicktree_config_arrays() {
	global $menu;

	quicktree_check_upgrade();

	api_plugin_register_realm('quicktree', 'quicktree.php', __('QuickTree Tree Management', 'quicktree'), 1);

	if (read_config_option('quicktree_pagestyle') > 0) {
		$menu[__('Management')]['plugins/quicktree/quicktree.php?location=console'] = __('QuickTree Trees', 'quicktree');
	}
}

function quicktree_page_head() {
	global $config;

	$page = get_current_page();

	print '<script type="text/javascript" src="' . $config['url_path'] . 'plugins/quicktree/quicktree.js"></script>';

    if (strstr($page, 'quicktree.php') !== false) {
		print '<link rel="stylesheet" href="' . $config['url_path'] . 'plugins/quicktree/quicktree.css"></link>';
	}
}

function quicktree_draw_navigation_text($nav) {
	$nav['quicktree.php:'] = array (
		'title' => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:':''),
		'url' => 'quicktree.php?location=' . get_nfilter_request_var('location'),
		'level' => '1'
	);

	$nav['quicktree.php:add_ajax'] = array (
		'title' => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:':''),
		'url' => 'quicktree.php?location=console',
		'level' => '1'
	);

	$nav['quicktree.php:add'] = array (
		'title' => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:':''),
		'url' => 'quicktree.php?location=console',
		'level' => '1'
	);

	$nav['quicktree.php:remove'] = array (
		'title' => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:':''),
		'url' => 'quicktree.php?location=console',
		'level' => '1'
	);

	$nav['quicktree.php:save'] = array (
		'title' => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:':''),
		'url' => 'quicktree.php?location=console',
		'level' => '1'
	);

	$nav['quicktree.php:clear'] = array (
		'title' => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:':''),
		'url' => 'quicktree.php?location=console',
		'level' => '1'
	);

	return $nav;
}

function quicktree_setup_table() {
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

	$pagestyle = read_config_option('quicktree_pagestyle');

	if ($pagestyle == '' or $pagestyle < 0 or $pagestyle > 2) {
		set_config_option('quicktree_pagestyle', '0');
	}
}

function plugin_quicktree_upgrade() {
	/* Here we will upgrade to the newest version */
	quicktree_check_upgrade();
	return FALSE;
}

function plugin_quicktree_uninstall() {
	/* Do any extra Uninstall stuff here */
}

function plugin_quicktree_check_config() {
	/* Here we will check to ensure everything is configured */
	return TRUE;
}

function quicktree_check_upgrade() {
    $files = array('plugins.php', 'quicktree.php', 'index.php', 'graph_view.php');
    if (isset($_SERVER['PHP_SELF']) && !in_array(basename($_SERVER['PHP_SELF']), $files)) {
        return;
    }

    $info    = plugin_quicktree_version();
    $current = $info['version'];

	$old = db_fetch_cell('SELECT version
		FROM plugin_config
		WHERE directory = "quicktree"');

    if ($current != $old) {
    	api_plugin_register_hook('quicktree', 'page_head', 'quicktree_page_head', 'setup.php', 1);

        quicktree_setup_table();

        db_execute_prepared('UPDATE plugin_config SET
            version = ?, name = ?, author = ?, webpage = ?
            WHERE directory = ?',
			array(
				$info['version'],
				$info['longname'],
				$info['author'],
				$info['homepage'],
				$info['name']
			)
		);
    }
}

