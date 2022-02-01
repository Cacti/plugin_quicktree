# cacti-quicktree

Make play-lists of graphs in Cacti


***NOTE*** Version 2.0 of QuickTree only works on Cacti 1.2.17 or above!

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
graphs on the 'single graph' viewing page (not in preview or list view - the
hook doesn't exist to add one there).

Known Issue: the way Cacti caches Graph Trees means that you might need to
switch to another graph tree before the newly-created one appears in your list.
This is true for Cacti-created trees too, so it seems to just be how it is.

## History

Version | Description
---: | :---
v1.1 | Correct some image issues
&nbsp; | Made default sorting Alphabetic for new trees
&nbsp; | Made default sorting Inherited for new branches/graphs
&nbsp; | &nbsp;
v1.0 | Updated to work with 1.x
&nbsp; | &nbsp;
v0.2 | Added option to display as a console menu item instead
&nbsp; | Added option to save to an existing Graph Tree
&nbsp; | &nbsp;
v0.1 | Initial Release
