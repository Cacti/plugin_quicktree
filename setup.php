<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2022 Howard Jones                                    |
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
 * Installs the quicktree plugin: registers its Cacti hooks (top_header_tabs,
 * top_graph_header_tabs, config_arrays, config_settings,
 * draw_navigation_text, graph_buttons, graph_buttons_thumbnails, page_head),
 * adds its quicktree.php realm, and creates its database table. Invoked by
 * Cacti's plugin architecture when an administrator installs this plugin
 * from Console > Plugin Management.
 *
 * @return bool Always returns true.
 */
function plugin_quicktree_install() {
	api_plugin_register_hook('quicktree', 'top_header_tabs',          'quicktree_show_tab',             'setup.php');
	api_plugin_register_hook('quicktree', 'top_graph_header_tabs',    'quicktree_show_tab',             'setup.php');
	api_plugin_register_hook('quicktree', 'config_arrays',            'quicktree_config_arrays',        'setup.php');
	api_plugin_register_hook('quicktree', 'config_settings',          'quicktree_config_settings',      'setup.php');
	api_plugin_register_hook('quicktree', 'draw_navigation_text',     'quicktree_draw_navigation_text', 'setup.php');
	api_plugin_register_hook('quicktree', 'graph_buttons',            'quicktree_graph_buttons',        'setup.php');
	api_plugin_register_hook('quicktree', 'graph_buttons_thumbnails', 'quicktree_graph_buttons',        'setup.php');

	api_plugin_register_realm('quicktree', 'quicktree.php', __('QuickTree Management', 'quicktree'), 1);

	api_plugin_register_hook('quicktree', 'page_head', 'quicktree_page_head', 'setup.php');

	quicktree_setup_table();

	return true;
}

/**
 * Reads this plugin's INFO file and returns its [info] section. Used by
 * Cacti's plugin architecture via the api_plugin_version hook, and
 * internally by quicktree_check_upgrade() to detect version changes.
 *
 * @return array The parsed [info] section of the plugin's INFO file (keys
 *               such as name, version, author, description).
 *
 * @global array $config Cacti global configuration array; used to locate
 *                        the plugin's base path.
 */
function plugin_quicktree_version() {
	global $config;

	$info = parse_ini_file($config['base_path'] . '/plugins/quicktree/INFO', true);
	return $info['info'];
}

/**
 * Hook implementation for Cacti's 'config_settings' filter. Registers the
 * "Misc" tab's Quicktree Page Style setting (tab, console menu, or both).
 * Called by Cacti core via api_plugin_hook('config_settings', ...) while
 * building the Settings page.
 *
 * @return void
 *
 * @global array $tabs     Cacti's registered Settings page tabs, extended
 *                          here with the 'misc' tab label.
 * @global array $settings Cacti's registered Settings page fields, extended
 *                          here under the 'misc' key.
 */
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

/**
 * Resolves whether QuickTree should actually render at $preferred's
 * location ('tab' or 'console'), based on the 'quicktree_pagestyle'
 * setting (Tab only, Console Menu only, or Both). Called from
 * quicktree_show_tab() and quicktree.php when building links/navigation.
 *
 * @param string $preferred The caller's preferred location, 'tab' or
 *                           'console'; defaults to 'tab'.
 *
 * @return string The location to actually use: $preferred when allowed by
 *                the current page-style setting, otherwise the other
 *                location.
 */
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

/**
 * Hook implementation for Cacti's 'top_header_tabs'/'top_graph_header_tabs'
 * filters. Prints the QuickTree tab icon (highlighted when quicktree.php is
 * the current page) when the user is authorized and the page style
 * includes the tab. Called by Cacti core via
 * api_plugin_hook('top_header_tabs'/'top_graph_header_tabs', ...) while
 * rendering the page header tabs.
 *
 * @return void Outputs HTML directly.
 *
 * @global array $config Cacti global configuration array; used to build
 *                        the tab's image/link URLs.
 */
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

/**
 * Hook implementation for Cacti's 'graph_buttons'/'graph_buttons_thumbnails'
 * filters. Prints an "Add this graph to QuickTree" icon/link for the
 * current graph, when the user is authorized. Called by Cacti core via
 * api_plugin_hook('graph_buttons'/'graph_buttons_thumbnails', ...) while
 * rendering a graph's action buttons.
 *
 * @param array $data Hook payload; $data[1] contains the current graph's
 *                     'local_graph_id' and 'rra' (RRA id).
 *
 * @return void Outputs HTML directly.
 *
 * @global array $config Cacti global configuration array (unused directly
 *                        here; declared for parity with other graph_buttons
 *                        hook implementations).
 */
function quicktree_graph_buttons($data) {
	global $config;

	if (api_user_realm_auth('quicktree.php')) {
		$local_graph_id = $data[1]['local_graph_id'];
		$rra_id         = $data[1]['rra'];

		print "<a class='iconLink' onClick='addQuickTree($local_graph_id, $rra_id)' title='" . __esc('Add this graph to QuickTree', 'quicktree') . "' href='#'><i class='deviceUp fas fa-plus-circle'></i></a><br>";
	}
}

/**
 * Hook implementation for Cacti's 'config_arrays' filter. Adds the
 * "QuickTree Trees" entry under the Management section of Cacti's menu
 * when the page style setting allows the console view, and triggers the
 * version-upgrade check. Called by Cacti core via
 * api_plugin_hook('config_arrays', ...) while building the navigation
 * menu.
 *
 * @return void
 *
 * @global array $menu Cacti's main navigation menu array, extended here
 *                      with this plugin's entry when applicable.
 */
function quicktree_config_arrays() {
	global $menu;

	quicktree_check_upgrade();

	if (read_config_option('quicktree_pagestyle') > 0) {
		$menu[__('Management')]['plugins/quicktree/quicktree.php?location=console'] = __('QuickTree Trees', 'quicktree');
	}
}

/**
 * Hook implementation for Cacti's 'page_head' filter. Includes this
 * plugin's JavaScript on every page, and its stylesheet specifically on
 * quicktree.php. Called by Cacti core via api_plugin_hook('page_head', ...)
 * while rendering the page <head> section.
 *
 * @return void Outputs HTML directly.
 *
 * @global array $config Cacti global configuration array; used to build
 *                        the JS/CSS asset URLs.
 */
function quicktree_page_head() {
	global $config;

	$page = get_current_page();

	print '<script type="text/javascript" src="' . $config['url_path'] . 'plugins/quicktree/js/quicktree.js"></script>';

    if (strstr($page, 'quicktree.php') !== false) {
		print '<link rel="stylesheet" href="' . $config['url_path'] . 'plugins/quicktree/css/quicktree.css"></link>';
	}
}

/**
 * Hook implementation for Cacti's 'draw_navigation_text' filter. Adds
 * breadcrumb entries for quicktree.php's default, add_ajax, add, remove,
 * save, and clear views, mapping back to the console index when viewed in
 * console mode. Called by Cacti core via
 * api_plugin_hook('draw_navigation_text', ...) while rendering the page
 * breadcrumb trail.
 *
 * @param array $nav The existing breadcrumb map contributed by Cacti core
 *                    and other plugins.
 *
 * @return array The $nav array with this plugin's breadcrumb entries
 *               added.
 */
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

/**
 * Creates the quicktree_graphs database table used to store each user's
 * saved QuickTree graphs, and initializes the 'quicktree_pagestyle' setting
 * to a valid default if it is currently unset or out of range. Called from
 * plugin_quicktree_install() and quicktree_check_upgrade().
 *
 * @return void
 */
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

/**
 * Triggers this plugin's version-upgrade check. Invoked by Cacti's plugin
 * architecture to determine whether the plugin needs to run an upgrade
 * routine.
 *
 * @return bool Always returns false (no separate upgrade routine to run).
 */
function plugin_quicktree_upgrade() {
	/* Here we will upgrade to the newest version */
	quicktree_check_upgrade();
	return FALSE;
}

/**
 * Uninstalls the quicktree plugin. Invoked by Cacti's plugin architecture
 * when an administrator uninstalls this plugin from Console > Plugin
 * Management; currently a no-op (the quicktree_graphs table is
 * intentionally left in place).
 *
 * @return void
 */
function plugin_quicktree_uninstall() {
	/* Do any extra Uninstall stuff here */
}

/**
 * Verifies the plugin's configuration is up to date. Invoked by Cacti's
 * plugin architecture on relevant page loads; currently a no-op
 * placeholder.
 *
 * @return bool Always returns true.
 */
function plugin_quicktree_check_config() {
	/* Here we will check to ensure everything is configured */
	return TRUE;
}

/**
 * Compares the plugin's INFO-file version against the version recorded in
 * plugin_config and, if they differ, re-registers the page_head hook,
 * re-runs the table setup, and updates the stored plugin_config row (or,
 * on newer Cacti versions, registers the upgrade via
 * api_plugin_upgrade_register()). Skips its work on requests other than
 * plugins.php, quicktree.php, index.php, or graph_view.php to avoid
 * unnecessary database access on every page load. Called from
 * quicktree_config_arrays() and plugin_quicktree_upgrade().
 *
 * @return void
 */
function quicktree_check_upgrade() {
    $files = array('plugins.php', 'quicktree.php', 'index.php', 'graph_view.php');
    if (isset($_SERVER['PHP_SELF']) && !in_array(basename($_SERVER['PHP_SELF']), $files)) {
        return;
    }

    $info    = plugin_quicktree_version();
    $current = $info['version'];

	$old = db_fetch_cell_prepared('SELECT version
		FROM plugin_config
		WHERE directory = ?',
		array('quicktree'));

    if ($current != $old) {
    	api_plugin_register_hook('quicktree', 'page_head', 'quicktree_page_head', 'setup.php', 1);

        quicktree_setup_table();

		if (function_exists('api_plugin_upgrade_register')) {
			api_plugin_upgrade_register('quicktree');
		} else {
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
}
