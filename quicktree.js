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
