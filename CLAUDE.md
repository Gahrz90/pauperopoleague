# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WordPress site for a Pauper (Magic: The Gathering) tournament league. Built on **Bedrock** (modern WordPress boilerplate) with the **Sage 10** theme framework (Acorn/Laravel in WordPress), Blade templates, Tailwind CSS, and Vite.

## Commands

All build commands run from the theme directory: `web/app/themes/pauperopoleague/`

```bash
# Development server with HMR
pnpm run dev

# Production build
pnpm run build

# PHP linting (Laravel Pint, from theme directory)
composer lint
composer lint:fix
```

Root-level PHP dependencies:
```bash
composer install   # from project root
```

Node version: 22 (see `.nvmrc`).

## Architecture

### Bedrock structure
WordPress core lives in `web/wp/` (managed by Composer). Themes, plugins, and mu-plugins are in `web/app/` instead of `wp-content/`. Environment config is loaded from `.env` via `config/application.php`.

### Sage 10 theme (`web/app/themes/pauperopoleague/`)
- **Blade templates** in `resources/views/` — layouts, sections, partials, components, and page-type templates
- **View Composers** in `app/View/Composers/` — inject data into Blade templates (e.g., `SingleTappa.php` for event detail pages)
- **Service Providers** in `app/Providers/ThemeServiceProvider.php` — register bindings, composers, and theme services via Acorn
- **Vite entry points**: `resources/css/app.css`, `resources/js/app.js`, `resources/css/editor.css`, `resources/js/editor.js`, `resources/js/decklist.js`
- Assets referenced in Blade with `@vite(...)` directive

### Custom mu-plugin: Decklist API (`web/app/mu-plugins/paupero-decklist-api.php`)
REST endpoint at `POST /wp-json/paupero/v1/decklist`. Validates tournament code against an ACF field, checks event start time, then appends the player's decklist to an ACF repeater on the event post. Publicly accessible — all validation is server-side.

The frontend (`resources/js/decklist.js`) is a vanilla JS two-step form: step 1 verifies the stage code, step 2 submits the decklist. Uses WordPress nonces for CSRF.

### ACF (Advanced Custom Fields Pro)
The site relies heavily on ACF for structured data — particularly repeater fields on "Tappa" (event) posts to store decklists. The custom plugin reads/writes these via `get_field()` / `add_row()`.

### Key plugins
- `advanced-custom-fields-pro` — custom fields and repeaters
- `magic-the-gathering-card-tooltips` — MTG card hover tooltips
- `classic-editor` — WordPress editor

## Environment Setup

Copy `.env.example` to `.env` and set:
- `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST`
- `WP_ENV` (`development` / `staging` / `production`)
- `WP_HOME` — full site URL (e.g., `http://pauperopoleague.test`)
- `WP_SITEURL` — `${WP_HOME}/wp`
- Auth keys/salts — generate at https://roots.io/salts.html
