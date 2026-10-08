# ChangeLog

--- develop ---

* bug: Pass a boolean (not the integer 1) to api_plugin_register_hook()'s $enable argument so the hook registration stays type-correct under Cacti 1.3's strict bool type-hint (the int only coerces while the file lacks declare(strict_types=1))
* security: Move the graph 'Add to QuickTree' icon's inline `onClick='addQuickTree()'` handler into a delegated jQuery binding in `js/quicktree.js` (via a `quicktreeAddLink` class and `data-` attributes) and replace the confirmation pages' inline `onClick='cactiReturnTo()'` Cancel buttons with the CSP-safe `cactiReturnTo` class, so the pages no longer trip Cacti's Content-Security-Policy script-src-attr directive
* dev: Replace the translation-template CI check that regenerated locales/po/cacti.pot and compared it with a diff-based gate (tests/bin/check-i18n-pot.php) requiring cacti.pot to be updated only when a pull request adds, removes, or modifies an i18n function call
* dev: Measure CI coverage with xdebug instead of pcov so the plugin's own sources are instrumented (pcov auto-scopes to the Composer root and skipped cacti/plugins/, leaving the patch-coverage gate with nothing to measure)
* dev: Enforce patch coverage of changed lines in CI and remove the inert COMPOSER_ROOT_VERSION env from the Pest step
* security: Add a version-safe CSP nonce (`plugin_quicktree_csp_nonce()`) to every inline `<script>` tag so pages stay compatible with Cacti's Content-Security-Policy nonce enforcement, while falling back cleanly on older Cacti releases that lack the `CactiSecureHeaders` class
- issue: Quicktree continually registers a realm
- issue: Fix quicktree_show_tab() building an invalid `?location=tabtab`/`?location=tabconsole` URL, an undefined-variable text-domain typo in the QuickTree page's "no graph specified" message, and a misspelled `qucktree` text domain
- security: Migrate quicktree SQL helpers to prepared statements, normalize location handling, and ensure redirects terminate execution
- test: Migrate test suite to Pest with a Cacti-provided test harness and CI workflow
- feature: Give the QuickTree add/remove graph glyphs their own theme-appropriate colors across every packaged Cacti theme, instead of reusing the core deviceUp/deviceDown classes


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
