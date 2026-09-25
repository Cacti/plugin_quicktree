# ChangeLog

--- develop ---

- issue: Quicktree continually registers a realm
- issue: Fix quicktree_show_tab() building an invalid `?location=tabtab`/`?location=tabconsole` URL, an undefined-variable text-domain typo in the QuickTree page's "no graph specified" message, and a misspelled `qucktree` text domain
- security: Migrate quicktree SQL helpers to prepared statements, normalize location handling, and ensure redirects terminate execution
- test: Migrate test suite to Pest with a Cacti-provided test harness and CI workflow


--- 2.0 ---

- feature: Allow creation of named Trees
- feature: Allow Adding optional sub-branches to new Trees
- feature: Add more callbacks to make navaigation smoother
- feature: Move away from concept of 'Personal Trees' till Cacti arrives there


--- 1.1 ---

- issue: Correct some image issues
- issue: Made default sorting Alphabetic for new trees
- issue: Made default sorting Inherited for new branches/graphs


--- 1.0 ---

- feature: Updated to work with 1.x


--- 0.2 ---

- feature: Added option to display as a console menu item instead
- feature: Added option to save to an existing Graph Tree


--- v0.1 --- 

- Initial Release

-----------------------------------------------
Copyright (c) 2004-2026 - The Cacti Group, Inc.
