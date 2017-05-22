# cacti-quicktree
Make playlists of graphs in Cacti

***NOTE*** Version 0.2 of QuickTree only works on Cacti 0.8.x!

Quite often while looking at an issue, you find yourself switching between a small set of graphs that relate to the problem, but not necessarily to the same device - the server load, it's mail queue, and the switch port, for example. QuickTree is a 'shopping basket' for graphs - click the '+' icon next to a graph and it's added to the QuickTree page. You can collect graphs from across your Cacti install very quickly. If it turns out that the set of graphs will be useful in the future too, you can save them as a normal Cacti graph tree, provided that you have permissions to do so.

Each user (with QuickTree permssions) gets their own QuickTree.

Install using the Plugin Management screen. Then enter the User Management page and give the required users access to it. A green '+' icon should appear next to graphs on the 'single graph' viewing page (not in preview or list view - the hook doesn't exist to add one there).

Known Issue: the way Cacti caches Graph Trees means that you might need to switch to another graph tree before the newly-created one appears in your list. This is true for Cacti-created trees too, so it seems to just be how it is.
