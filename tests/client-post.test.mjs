/* Copyright (C) 2026 The Cacti Group. GPL version 2 or later. */
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import test from 'node:test';

test('adding a graph sends a POST body and preserves the success notification', () => {
	let request;
	let notifications = 0;
	const context = {
		urlPath: '/cacti/',
		$: { ajax: (options) => { request = options; } },
		displayMessages: () => { notifications++; },
	};
	runInNewContext(readFileSync(new URL('../quicktree.js', import.meta.url), 'utf8'), context);
	context.addQuickTree(42, 7);
	assert.equal(request.type, 'POST');
	assert.equal(request.url, '/cacti/plugins/quicktree/quicktree.php');
	assert.equal(request.dataType, 'json');
	assert.deepEqual(JSON.parse(JSON.stringify(request.data)), {action: 'add', rra_id: 7, graph_id: 42});
	request.success({title: 'Added', message: 'Queued'});
	assert.equal(context.sessionMessageTitle, 'Added');
	assert.equal(context.sessionMessage.message, 'Queued');
	assert.equal(notifications, 1);
});
