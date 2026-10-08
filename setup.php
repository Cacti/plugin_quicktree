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
 * Return the CSP nonce attribute for inline <script> tags, safely across
 * Cacti versions. Newer Cacti releases enforce a Content-Security-Policy that
 * requires a per-request nonce on parser-inserted scripts; older releases lack
 * the CactiSecureHeaders class, so this returns an empty string there.
 *
 * @return string The nonce attribute when supported, otherwise empty string.
 */
function plugin_quicktree_csp_nonce(): string {
	if (class_exists('CactiSecureHeaders')) {
		return CactiSecureHeaders::getNonceAttribute();
	}

	return '';
}

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
function plugin_quicktree_install(): bool {
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
function plugin_quicktree_version(): array {
	global $config;

	$info = parse_ini_file($config['base_path'] . '/plugins/quicktree/INFO', true);
	$info = is_array($info) ? $info : [];

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
function quicktree_config_settings(): void {
	global $tabs, $settings;

	$tabs['misc'] = __('Misc');

	$temp = [
		'quicktree_header' => [
			'friendly_name' => __('Quicktree', 'quicktree'),
			'method'        => 'spacer',
		],
		'quicktree_pagestyle' => [
			'friendly_name' => __('Page Style', 'quicktree'),
			'description'   => __('Where to display the QuickTree page', 'quicktree'),
			'method'        => 'drop_array',
			'array'         => [
				0 => __('Tab', 'quicktree'),
				1 => __('Console Menu', 'quicktree'),
				2 => __('Both Tab and Console Menu', 'quicktree')
			]
		]
	];

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
 * quicktree_show_tab() when building the tab/console menu link.
 *
 * @param string $preferred The caller's preferred location, 'tab' or
 *                          'console'; defaults to 'tab'.
 *
 * @return string The location to actually use: $preferred when allowed by
 *                the current page-style setting, otherwise the other
 *                location.
 */
function quicktree_page_location(string $preferred = 'tab'): string {
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
function quicktree_show_tab(): void {
	global $config;

	if (api_user_realm_auth('quicktree.php')) {
		$cp = false;

		if (basename($_SERVER['PHP_SELF']) == 'quicktree.php') {
			$cp = true;
		}

		if (read_config_option('quicktree_pagestyle') != 1) {
			print '<a href="' . $config['url_path'] . 'plugins/quicktree/quicktree.php?location=' . quicktree_page_location('tab') . '"><img src="' . $config['url_path'] . 'plugins/quicktree/images/tab_quicktree' . ($cp ? '_active' : '') . '.gif" alt="' . __esc('Quicktree', 'quicktree') . '"></a>';
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
 *                    'local_graph_id' and 'rra' (RRA id).
 *
 * @return void Outputs HTML directly.
 *
 * @global array $config Cacti global configuration array (unused directly
 *                        here; declared for parity with other graph_buttons
 *                        hook implementations).
 */
function quicktree_graph_buttons($data): void {
	global $config;

	if (api_user_realm_auth('quicktree.php')) {
		$local_graph_id = $data[1]['local_graph_id'];
		$rra_id         = $data[1]['rra'];

		print "<a class='iconLink quicktreeAddLink' data-local-graph-id='$local_graph_id' data-rra-id='$rra_id' title='" . __esc('Add this graph to QuickTree', 'quicktree') . "' href='#'><i class='quicktreeAdd fas fa-plus-circle'></i></a><br>";
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
function quicktree_config_arrays(): void {
	global $menu;

	quicktree_check_upgrade();

	if (read_config_option('quicktree_pagestyle') > 0) {
		$menu[__('Management')]['plugins/quicktree/quicktree.php?location=console'] = __('QuickTree Trees', 'quicktree');
	}
}

/**
 * Hook implementation for Cacti's 'page_head' filter. Includes this
 * plugin's JavaScript and stylesheets on every page. The base stylesheet
 * and, when present, a stylesheet matching the user's selected theme are
 * both loaded on every page because QuickTree's action glyphs are rendered
 * on the graph view page as well as on quicktree.php. Called by Cacti core
 * via api_plugin_hook('page_head', ...) while rendering the page <head>
 * section.
 *
 * @return void Outputs HTML directly.
 *
 * @global array $config Cacti global configuration array; used to build
 *                        the JS/CSS asset URLs.
 */
function quicktree_page_head(): void {
	global $config;

	print get_md5_include_js('plugins/quicktree/js/quicktree.js');

	print get_md5_include_css('plugins/quicktree/css/quicktree.css');

	if (file_exists($config['base_path'] . '/plugins/quicktree/css/' . get_selected_theme() . '.css')) {
		print get_md5_include_css('plugins/quicktree/css/' . get_selected_theme() . '.css');
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
 *                   and other plugins.
 *
 * @return array The $nav array with this plugin's breadcrumb entries
 *               added.
 */
function quicktree_draw_navigation_text($nav): array {
	$nav['quicktree.php:'] =  [
		'title'   => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:' : ''),
		'url'     => 'quicktree.php?location=' . get_nfilter_request_var('location'),
		'level'   => '1'
	];

	$nav['quicktree.php:add_ajax'] =  [
		'title'   => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:' : ''),
		'url'     => 'quicktree.php?location=console',
		'level'   => '1'
	];

	$nav['quicktree.php:add'] =  [
		'title'   => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:' : ''),
		'url'     => 'quicktree.php?location=console',
		'level'   => '1'
	];

	$nav['quicktree.php:remove'] =  [
		'title'   => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:' : ''),
		'url'     => 'quicktree.php?location=console',
		'level'   => '1'
	];

	$nav['quicktree.php:save'] =  [
		'title'   => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:' : ''),
		'url'     => 'quicktree.php?location=console',
		'level'   => '1'
	];

	$nav['quicktree.php:clear'] =  [
		'title'   => __('QuickTree', 'quicktree'),
		'mapping' => (get_nfilter_request_var('location') == 'console' ? 'index.php:' : ''),
		'url'     => 'quicktree.php?location=console',
		'level'   => '1'
	];

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
function quicktree_setup_table(): void {
	$data = [];

	$data['columns'][] = ['name' => 'id', 'type' => 'int(11)', 'NULL' => false, 'auto_increment' => true];
	$data['columns'][] = ['name' => 'userid', 'type' => 'int(11)', 'NULL' => false];
	$data['columns'][] = ['name' => 'local_graph_id', 'type' => 'int(11)', 'NULL' => false, 'default' => '0'];
	$data['columns'][] = ['name' => 'rra_id', 'type' => 'int(11)', 'NULL' => false, 'default' => '0'];
	$data['columns'][] = ['name' => 'title', 'type' => 'varchar(191)', 'NULL' => false, 'default' => ''];
	$data['primary']   = 'id';
	$data['type']      = 'InnoDB';
	$data['comment']   = 'Quicktree data';
	api_plugin_db_table_create('quicktree', 'quicktree_graphs', $data);

	$pagestyle = read_config_option('quicktree_pagestyle');

	if ($pagestyle == '' || $pagestyle < 0 || $pagestyle > 2) {
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
function plugin_quicktree_upgrade(): bool {
	// Here we will upgrade to the newest version
	quicktree_check_upgrade();

	return false;
}

/**
 * Uninstalls the quicktree plugin. Invoked by Cacti's plugin architecture
 * when an administrator uninstalls this plugin from Console > Plugin
 * Management; currently a no-op (the quicktree_graphs table is
 * intentionally left in place).
 *
 * @return void
 */
function plugin_quicktree_uninstall(): void {
	// Do any extra Uninstall stuff here
}

/**
 * Verifies the plugin's configuration is up to date. Invoked by Cacti's
 * plugin architecture on relevant page loads; currently a no-op
 * placeholder.
 *
 * @return bool Always returns true.
 */
function plugin_quicktree_check_config(): bool {
	// Here we will check to ensure everything is configured
	return true;
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
function quicktree_check_upgrade(): void {
	$files = ['plugins.php', 'quicktree.php', 'index.php', 'graph_view.php'];

	if (isset($_SERVER['PHP_SELF']) && !in_array(basename($_SERVER['PHP_SELF']), $files, true)) {
		return;
	}

	$info    = plugin_quicktree_version();
	$current = $info['version'];

	$old = db_fetch_cell_prepared('SELECT version
		FROM plugin_config
		WHERE directory = ?',
		['quicktree']);

	if ($current != $old) {
		api_plugin_register_hook('quicktree', 'page_head', 'quicktree_page_head', 'setup.php', true);

		quicktree_setup_table();

		if (function_exists('api_plugin_upgrade_register')) {
			api_plugin_upgrade_register('quicktree');
		} else {
			db_execute_prepared('UPDATE plugin_config SET
				version = ?, name = ?, author = ?, webpage = ?
				WHERE directory = ?',
				[
					$info['version'],
					$info['longname'],
					$info['author'],
					$info['homepage'],
					$info['name']
				]
			);
		}

		quicktree_prune_files();
	}
}

/**
 * Removes files and directories that a previous version of this plugin
 * shipped but that have since moved or been deleted, using the tombstone
 * and whitelist lists in manifest.json. Whitelisted (user-data) paths and
 * any VCS metadata (.git*) are never touched; the dev-only tests/ tree is
 * removed. Any path that resolves outside the plugin directory (a tampered
 * manifest.json) is refused, and any file/directory that cannot be removed
 * (e.g. read-only) is reported to the Cacti log. Any top-level entry that is
 * neither expected nor a tombstone nor whitelisted is logged to the Cacti
 * log and left in place. Called on a plugin version change.
 *
 * @return void
 *
 * @global array $config Cacti global configuration array; used to resolve
 *                       the plugin directory.
 */
function quicktree_prune_files(): void {
	global $config;

	$plugin_dir    = $config['base_path'] . '/plugins/quicktree';
	$manifest_path = $plugin_dir . '/manifest.json';

	if (!is_readable($manifest_path)) {
		return;
	}

	$manifest = json_decode((string) file_get_contents($manifest_path), true);

	if (!is_array($manifest)) {
		cacti_log('WARNING: quicktree manifest.json could not be parsed; skipping file prune', false, 'QUICKTREE');

		return;
	}

	$tombstones = isset($manifest['tombstones']) && is_array($manifest['tombstones']) ? $manifest['tombstones'] : [];
	$expected   = isset($manifest['expected'])   && is_array($manifest['expected'])   ? $manifest['expected']   : [];
	$whitelist  = isset($manifest['whitelist'])  && is_array($manifest['whitelist'])  ? $manifest['whitelist']  : [];

	$protected = function (string $rel) use ($whitelist): bool {
		if (strncmp($rel, '.git', 4) === 0 || strncmp($rel, '.md', 3) === 0) {
			return true;
		}

		foreach ($whitelist as $entry) {
			$entry = trim((string) $entry, '/');

			if ($entry !== '' && ($rel === $entry
				|| strncmp($rel, $entry . '/', strlen($entry) + 1) === 0
				|| strncmp($entry, $rel . '/', strlen($rel) + 1) === 0)) {
				return true;
			}
		}

		return false;
	};

	// Security: resolve the plugin directory so a tampered manifest.json
	// cannot steer the prune outside of it.
	$plugin_real = realpath($plugin_dir);

	// Remove tombstoned (moved/deleted) paths plus the dev-only tests/
	// tree and the phpunit.xml test configuration.
	$remove   = $tombstones;
	$remove[] = 'tests/';
	$remove[] = 'phpunit.xml';

	foreach ($remove as $rel) {
		$rel = trim((string) $rel, '/');

		if ($rel === '' || $protected($rel)) {
			continue;
		}

		// A tombstone must never contain '.'/'..' segments; a tampered manifest
		// could use them to escape the plugin directory or target its root.
		$segments = explode('/', $rel);

		if (in_array('.', $segments, true) || in_array('..', $segments, true)) {
			cacti_log(sprintf('WARNING: quicktree prune refused to remove %s: path contains a traversal segment (tampered manifest.json?)', $rel), false, 'QUICKTREE');

			continue;
		}

		$path = $plugin_dir . '/' . $rel;

		if (!is_link($path) && !file_exists($path)) {
			continue;
		}

		// Refuse any path that, after resolving symlinks and ../ segments,
		// escapes the plugin directory (protects user data from a tampered
		// manifest.json).
		$anchor = is_link($path) ? dirname($path) : $path;
		$real   = realpath($anchor);

		if ($real === false || ($real !== $plugin_real && strncmp($real, $plugin_real . DIRECTORY_SEPARATOR, strlen((string) $plugin_real) + 1) !== 0)) {
			cacti_log(sprintf('WARNING: quicktree prune refused to remove %s: path resolves outside the plugin directory (tampered manifest.json?)', $rel), false, 'QUICKTREE');

			continue;
		}

		if (is_dir($path) && !is_link($path)) {
			$removed = quicktree_rmtree($path);
		} else {
			$removed = @unlink($path);
		}

		if (!$removed) {
			cacti_log(sprintf('WARNING: quicktree upgrade could not remove %s (check file/directory permissions)', $rel), false, 'QUICKTREE');
		}
	}

	// Surface any top-level entry the manifest does not account for.
	$known = [];

	foreach (array_merge($expected, $tombstones) as $entry) {
		$top = explode('/', trim((string) $entry, '/'))[0];

		if ($top !== '') {
			$known[$top] = true;
		}
	}

	$entries = scandir($plugin_dir);

	foreach (($entries !== false ? $entries : []) as $entry) {
		if ($entry === '.' || $entry === '..' || $entry === 'tests' || $entry === 'phpunit.xml' || $protected($entry) || isset($known[$entry])) {
			continue;
		}

		cacti_log(sprintf('WARNING: quicktree upgrade found a file/directory not described in manifest.json: %s (left in place)', $entry), false, 'QUICKTREE');
	}
}

/**
 * Recursively deletes a directory and its contents. Symlinks are removed
 * without being followed. Helper for quicktree_prune_files().
 *
 * @param string $dir Absolute path to the directory to remove.
 *
 * @return bool True if the directory and everything under it was removed;
 *              false if any entry could not be deleted.
 */
function quicktree_rmtree(string $dir): bool {
	$entries = scandir($dir);
	$ok      = true;

	foreach (($entries !== false ? $entries : []) as $entry) {
		if ($entry === '.' || $entry === '..') {
			continue;
		}

		$path = $dir . '/' . $entry;

		if (is_dir($path) && !is_link($path)) {
			if (!quicktree_rmtree($path)) {
				$ok = false;
			}
		} elseif (!@unlink($path)) {
			$ok = false;
		}
	}

	if (!@rmdir($dir)) {
		$ok = false;
	}

	return $ok;
}
