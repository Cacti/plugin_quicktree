/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2010-2022 Howard Jones                                    |
 | Copyright (C) 2022-2025 The Cacti Group                                 |
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

function addQuickTree(local_graph_id, rra_id) {
	var strURL = urlPath +
		'plugins/quicktree/quicktree.php' +
		'?action=add' +
		'&rra_id='    + rra_id +
		'&graph_id='  + local_graph_id;

	$.getJSON(strURL, function(message) {
		if (message.title) {
			sessionMessageTitle = message.title;
		}

		if (message.message) {
			sessionMessage = message;
		}

		displayMessages();
	});
}
