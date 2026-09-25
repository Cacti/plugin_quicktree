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
	/**
	 * Opens a QuickTree action form: prints the page header (unless
	 * suppressed via the 'header' request variable), then starts the form
	 * and its enclosing box. Called from quicktree.php before rendering an
	 * action's input fields.
	 *
	 * @param string $title The box title to display above the form.
	 *
	 * @return void Outputs HTML directly.
	 */
	function quicktree_action_form_begin(string $title): void {
		if (get_nfilter_request_var('header') == null) {
			top_header();
		}

		form_start('quicktree.php', 'quicktree_form');
		html_start_box($title, '60%', false, 3, 'center', '');
	}
}

if (!function_exists('quicktree_action_form_end')) {
	/**
	 * Closes a QuickTree action form opened by quicktree_action_form_begin():
	 * ends the enclosing box and the form itself. Called from quicktree.php
	 * after rendering an action's input fields.
	 *
	 * @return void Outputs HTML directly.
	 */
	function quicktree_action_form_end(): void {
		html_end_box();
		form_end();
	}
}
