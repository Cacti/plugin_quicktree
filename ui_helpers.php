<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2010-2022 Howard Jones                                    |
 | Copyright (C) 2004-2026 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 +-------------------------------------------------------------------------+
 */

if (!function_exists('quicktree_action_form_begin')) {
	function quicktree_action_form_begin($title) {
		if (get_nfilter_request_var('header') == null) {
			top_header();
		}

		form_start('quicktree.php', 'quicktree_form');
		html_start_box($title, '60%', '', '3', 'center', '');
	}
}

if (!function_exists('quicktree_action_form_end')) {
	function quicktree_action_form_end() {
		html_end_box();
		form_end();
	}
}
