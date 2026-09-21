# GitHub Copilot Instructions

## Priority Guidelines

When generating code for this repository:

1. **Version Compatibility**: This is a Cacti plugin (`quicktree`, version 2.0) targeting Cacti 1.2.17+
2. **Context Files**: Prioritize patterns and standards defined in this file (`.github/copilot-instructions.md`)
3. **Codebase Patterns**: When context files don't provide specific guidance, scan the codebase for established patterns
4. **Architectural Consistency**: Maintain plugin-based architecture extending Cacti core
5. **Code Quality**: Prioritize security, maintainability, and compatibility in all generated code

## Technology Stack

### Core Technologies
- **PHP**: Compatible with Cacti 1.2.x supported versions
- **Platform**: Cacti Plugin Architecture ("QuickTree" - a shopping-basket style graph playlist)
- **Database**: MySQL/MariaDB

### Key Dependencies
- Cacti core framework (`api_plugin_*`, `db_*`, `read_config_option()`)
- Client-side: `quicktree.js`, `quicktree.css`

## Project Structure

```
quicktree/               # Repository root (install to plugins/quicktree/ in Cacti)
├── images/                # UI icons
├── locales/                 # Internationalization files
├── quicktree.css              # Client-side styling
├── quicktree.js                 # Client-side add/remove graph interactions
├── quicktree.php                  # Main playlist view / management page
├── INFO                              # Plugin metadata (name, version, compat)
├── README.md
└── setup.php                          # Plugin install/uninstall/upgrade hooks
```

## Naming Conventions

### Function Names
- **Plugin lifecycle/hook-registration functions** MUST be prefixed `plugin_quicktree_`: `plugin_quicktree_install()`, `plugin_quicktree_upgrade()`, `plugin_quicktree_version()`.
- **All other functions** MUST be prefixed `quicktree_`: `quicktree_show_tab()`, `quicktree_page_location()`, `quicktree_setup_table()`.
- Match the existing prefix used by the function you are editing; do not introduce a third naming scheme.

## Code Style

### Indentation and Formatting
- **Tabs**: Use tabs (not spaces) for indentation throughout all PHP files.
- **Braces**: Opening brace on the same line for functions and control structures.
- **Spacing**: Space after control structure keywords (`if`, `foreach`, `while`).

### File Headers
ALL PHP files MUST include the standard GPL v2 license header used throughout this repository (see `setup.php`), crediting "The Cacti Group" (and Howard Jones as original author).

## Security Standards

### SQL Query Security
Use prepared statements for anything involving variable input:

```php
// CORRECT
db_fetch_row_prepared('SELECT * FROM plugin_quicktree_items WHERE id = ?', array($id));

// WRONG - never do this with request-derived values
db_fetch_row("SELECT * FROM plugin_quicktree_items WHERE id = $id");
```

### Input Validation
Use `get_filter_request_var()` / `get_nfilter_request_var()` for request input; never read `$_GET`/`$_POST` directly. Validate `api_user_realm_auth('quicktree.php')` before rendering the tab or management actions.

`get_filter_request_var()` (and its `gfrv()` shorthand, where available) called with only the
`$name` argument (no regex/filter as the 2nd/3rd argument) already validates the value as numeric
and returns it as a **string** -- it does not return an int, and it halts execution if the request
value is not numeric. Because of this, do NOT cast its output to `(int)` when the result is only
used for string output (e.g. `print`/`echo`, string concatenation, embedding in HTML/JS); the cast
is redundant. Only cast when the value is genuinely used in an integer/numeric context (e.g.
arithmetic, strict `===` comparisons).

## Database Operations

### Upgrade Handling
Version-gate schema changes in `quicktree_check_upgrade()` (`setup.php`) against the stored `plugin_config` row.

## Internationalization

ALL user-facing strings MUST use `__()`/`__esc()` with the `'quicktree'` text domain:

```php
print '<img ... alt="' . __esc('Quicktree', 'quicktree') . '">';
```

## Plugin Architecture

### Plugin Hooks
Register all plugin hooks in `plugin_quicktree_install()` (`setup.php`):

```php
api_plugin_register_hook('quicktree', 'top_header_tabs',          'quicktree_show_tab',             'setup.php');
api_plugin_register_hook('quicktree', 'top_graph_header_tabs',    'quicktree_show_tab',             'setup.php');
api_plugin_register_hook('quicktree', 'config_arrays',            'quicktree_config_arrays',        'setup.php');
api_plugin_register_hook('quicktree', 'config_settings',          'quicktree_config_settings',      'setup.php');
api_plugin_register_hook('quicktree', 'draw_navigation_text',     'quicktree_draw_navigation_text', 'setup.php');
api_plugin_register_hook('quicktree', 'graph_buttons',            'quicktree_graph_buttons',        'setup.php');
api_plugin_register_hook('quicktree', 'graph_buttons_thumbnails', 'quicktree_graph_buttons',        'setup.php');
api_plugin_register_hook('quicktree', 'page_head',                'quicktree_page_head',            'setup.php');

api_plugin_register_realm('quicktree', 'quicktree.php', __('QuickTree Management', 'quicktree'), 1);
```

### Page Placement Setting
`quicktree_page_location()` honors the `quicktree_pagestyle` setting (Tab / Console Menu / Both) — when adding new entry points, respect this same placement logic rather than hardcoding a tab-only or menu-only presentation.

## Best Practices

1. Always guard tab/UI rendering with `api_user_realm_auth('quicktree.php')`.
2. Respect the `quicktree_pagestyle` setting for any new navigation entry point.
3. Wrap all user-facing strings with `__()`/`__esc()` and the `quicktree` domain.

## Common Pitfalls to Avoid

```php
// WRONG - unescaped output in an alt attribute
print '<img alt="' . $label . '">';

// CORRECT
print '<img alt="' . __esc($label, 'quicktree') . '">';
```

## Version Control

Document all changes in `CHANGELOG.md`; use descriptive commit messages referencing issue/PR numbers when applicable.

## CI & Dependency Baselines

- Do not commit a `composer.json` or `composer.lock` in this plugin's own repo root — the shared CI workflow installs Pest/dev dependencies into Cacti's own Composer-managed vendor tree (checked out alongside the plugin). Use Cacti's `composer.json`, not a plugin-local one.
- Do not add a plugin-local `.phpstan.neon`/`phpstan.neon` or `.php-cs-fixer.php`/`.php-cs-fixer.dist.php` — lint/static-analysis steps run against Cacti's own config from the Cacti core checkout, targeting this plugin's directory. Use the Cacti version, not a plugin-local config.
- Prefer Cacti's `cacti_count()`/`cacti_sizeof()` wrappers over the raw `count()`/`sizeof()` builtins in new or edited code.

## Internationalization (i18n)

- Translatable strings are managed with GNU gettext via `locales/build_gettext.sh`. `locales/po/cacti.pot` is the source template; Weblate owns syncing the per-language `.po`/`.mo` files from it.
- When a pull request adds or changes a string wrapped in `__()`/`__n()`/`__esc()`/`__x()`/`__xn()`/`__gettext()`, run `locales/build_gettext.sh` before pushing and add the resulting change to `locales/po/cacti.pot` only. Do not commit the regenerated per-language `.po`/`.mo` files in the same PR — Weblate takes care of the rest.

## References

- [Cacti main repo](https://github.com/Cacti/cacti/tree/1.2.x)
- [Cacti Documentation](https://www.github.com/Cacti/documentation)
- `README.md` for feature descriptions
- `CHANGELOG.md` for version history

## Security & Quality Conventions

These conventions apply across the Cacti plugin fleet and should be followed whenever touching
existing code or adding new code, not just in dedicated cleanup passes:

- **No hardcoded third-party hosts.** Never hardcode a third-party IP address, hostname, or URL
  in plugin code (even for tooling/download helpers). Expose it as a plugin setting instead, with
  secure-by-default values (e.g. an SSL-verification setting that defaults to verify-on).
- **Prepared statements over `db_qstr()`.** Build dynamic `WHERE` clauses using the
  `$sql_where`/`$sql_params` prepared-statement pattern, not string concatenation via `db_qstr()`.
- **Use `html_escape_request_var()`.** Prefer it over the `html_escape(get_request_var(...))` call
  chain.
- **Harden `unserialize()`.** Always pass `['allow_classes' => false]` as the second argument.
- **i18n text domain.** Every `__()`/`__esc()` call must include this plugin's text domain as the
  final argument, except when deliberately comparing against a literal, untranslated Cacti-core
  label.
- **Plugin table-creation API.** Use `api_plugin_db_table_create()`/`api_plugin_db_add_column()`
  (from Cacti core's `lib/plugins.php`) instead of raw `CREATE TABLE`/`ALTER TABLE ... ADD COLUMN`.
  Both are idempotent (safe no-ops when already applied), so the same call can run unconditionally
  from both the install AND upgrade paths.
- **PHPDoc shape.** Every function gets a PHPDoc block: a one-line description, a blank comment
  line, `@param` lines, a blank comment line, then `@return`. Infer parameter/return types from
  actual usage; don't change the function's real type-hints in the same pass (let static analysis
  flag mismatches separately). Skip vendored third-party library files.
