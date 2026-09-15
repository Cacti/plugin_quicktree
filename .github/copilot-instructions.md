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

## References

- [Cacti main repo](https://github.com/Cacti/cacti/tree/1.2.x)
- [Cacti Documentation](https://www.github.com/Cacti/documentation)
- `README.md` for feature descriptions
- `CHANGELOG.md` for version history
