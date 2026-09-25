<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 +-------------------------------------------------------------------------+
*/

/*
 * Regression coverage for quicktree_show_tab()'s tab link URL: a prior
 * version concatenated a leftover literal 'tab' immediately before
 * quicktree_page_location()'s own return value, producing an invalid
 * '?location=tabtab' (or '?location=tabconsole') query string instead of
 * '?location=tab'.
 */

beforeAll(function () {
        require_once __DIR__ . '/../../setup.php';
});

beforeEach(function () {
        $GLOBALS['__test_config_options'] = array();
        $GLOBALS['__test_realm_auth']     = true;
        $_SERVER['PHP_SELF']              = '/cacti/graph_view.php';
});

it('links to plain ?location=tab when the Tab page style is enabled', function () {
        $GLOBALS['__test_config_options']['quicktree_pagestyle'] = '0';

        ob_start();
        quicktree_show_tab();
        $output = ob_get_clean();

        expect($output)->toContain('quicktree.php?location=tab"');
        expect($output)->not->toContain('location=tabtab');
        expect($output)->not->toContain('location=tabconsole');
});

it('links to plain ?location=tab when both Tab and Console Menu are enabled', function () {
        $GLOBALS['__test_config_options']['quicktree_pagestyle'] = '2';

        ob_start();
        quicktree_show_tab();
        $output = ob_get_clean();

        expect($output)->toContain('quicktree.php?location=tab"');
        expect($output)->not->toContain('location=tabtab');
        expect($output)->not->toContain('location=tabconsole');
});

it('prints nothing when the page style is Console Menu only', function () {
        $GLOBALS['__test_config_options']['quicktree_pagestyle'] = '1';

        ob_start();
        quicktree_show_tab();
        $output = ob_get_clean();

        expect($output)->toBe('');
});

it('prints nothing when the user is not authorized for the quicktree realm', function () {
        $GLOBALS['__test_config_options']['quicktree_pagestyle'] = '0';
        $GLOBALS['__test_realm_auth']                             = false;

        ob_start();
        quicktree_show_tab();
        $output = ob_get_clean();

        expect($output)->toBe('');
});
