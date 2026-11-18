# cacti-quicktree

Make play-lists of graphs in Cacti


This source branch requires Cacti 1.2.29 or later. The published 2.0 tag
declares Cacti 1.2.17; use its tagged documentation when installing that package.

***NOTE*** Version 1.0 of QuickTree only works on Cacti 1.1.9 or above!

***NOTE*** Version 0.2 of QuickTree only works on Cacti 0.8.x!

Quite often while looking at an issue, you find yourself switching between a
small set of graphs that relate to the problem, but not necessarily to the same
device - the server load, its mail queue, and the switch port, for example.
QuickTree is a 'shopping basket' for graphs - click the '+' icon next to a graph
and it's added to the QuickTree page. You can collect graphs from across your
Cacti install very quickly. If it turns out that the set of graphs will be
useful in the future too, you can save them as a normal Cacti graph tree,
provided that you have permissions to do so.

Each user (with QuickTree permissions) gets their own QuickTree.

Make sure that you rename the folder to `quicktree` so that the plugin system is
happy.

Install using the Plugin Management screen. Then enter the User Management page
and give the required users access to it. A green '+' icon should appear next to
graphs where Cacti exposes the graph action hooks, including single-graph and
thumbnail views supported by this branch.

By default QuickTree appears as a tab. To change where it is shown, set 'Page
Style' under Console > Settings > Misc to 'Tab', 'Console Menu', or 'Both Tab
and Console Menu'.

Known Issue: the way Cacti caches Graph Trees means that you might need to
switch to another graph tree before the newly-created one appears in your list.
This is true for Cacti-created trees too, so it seems to just be how it is.

## Versions and compatibility

This README describes the `main` source branch.

| Code | Plugin version | Minimum Cacti declared in `INFO` |
| --- | --- | --- |
| [Source branch](INFO) | 2.0 | 1.2.29 |
| [Published v2.0](https://github.com/Cacti/plugin_quicktree/releases/tag/v2.0) | 2.0 | 1.2.17 |

Use the published release’s assets and tagged documentation when installing that
package. Branch code can include later changes, even when its version number
matches the release. Cacti’s own PHP and database requirements also apply.

Install this plugin in **`<cacti-root>/plugins/quicktree`**, using the directory
name `quicktree` rather than `plugin_quicktree`.

See [CHANGELOG.md](CHANGELOG.md) for the version history.

## Cacti compatibility

If you are running a version of Cacti below 1.2.31, please add the function
below to the `applySkin()` function in `include/layout.js` to enable the Cancel
buttons on forms to work:

```js
$(document).off('click.cactiReturnTo', '.cactiReturnTo')
    .on('click.cactiReturnTo', '.cactiReturnTo', function(event) {
        event.preventDefault();
        cactiReturnTo($(this).attr('data-url'));
    });
```

## History

See CHANGELOG.md

-----------------------------------------------
Copyright (c) 2004-2026 - The Cacti Group, Inc.
