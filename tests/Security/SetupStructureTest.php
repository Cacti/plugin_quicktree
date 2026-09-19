<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
*/

describe('quicktree setup.php structure', function () {
	$source = file_get_contents(realpath(__DIR__ . '/../../setup.php'));
	$info   = parse_ini_file(realpath(__DIR__ . '/../../INFO'), true)['info'];

	it('defines plugin_quicktree_install function', function () use ($source) {
		expect($source)->toContain('function plugin_quicktree_install');
	});

	it('defines plugin_quicktree_version function', function () use ($source) {
		expect($source)->toContain('function plugin_quicktree_version');
	});

	it('defines plugin_quicktree_uninstall function', function () use ($source) {
		expect($source)->toContain('function plugin_quicktree_uninstall');
	});

	it('declares a plugin name in INFO', function () use ($info) {
		expect($info)->toHaveKey('name');
	});

	it('declares a plugin version in INFO', function () use ($info) {
		expect($info)->toHaveKey('version');
	});

	it('registers hooks in install function', function () use ($source) {
		expect($source)->toContain('api_plugin_register_hook');
	});
});
