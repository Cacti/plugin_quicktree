<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
*/

/*
 * Verify setup.php defines required plugin hooks and info function.
 */

$setupPath = realpath(__DIR__ . '/../../setup.php');
if ($setupPath === false) {
	throw new RuntimeException('Unable to resolve setup.php');
}

$source = file_get_contents($setupPath);
if ($source === false) {
	throw new RuntimeException('Unable to read setup.php');
}

$infoFile = parse_ini_file(__DIR__ . '/../../INFO', true);
if (!is_array($infoFile) || !isset($infoFile['info']) || !is_array($infoFile['info'])) {
	throw new RuntimeException('Unable to parse the INFO section');
}
$info = $infoFile['info'];

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
