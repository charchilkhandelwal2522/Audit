# Audit

**Version:** 1.0.0

Laravel module for internal audits, store checks, and department visits. Standardize inspections with reusable templates, collect proof at each checkpoint, score results automatically, and track performance over time.

## Description

The Audit module plugs into a multi-company Laravel application. It lets auditors run structured checklists against departments and staff, attach photos and files as evidence, pause and resume timed sessions, and generate PDF reports. Managers get dashboards, summary reports, and optional alerts when scores fall below a threshold.

## Features

- **Audit templates** — Department-scoped templates with reorderable checkpoints; optional requirements for photos, files, and notes
- **Audit execution** — Start audits with location and photo capture; timer with pause/resume; checkpoint statuses (completed, partial, not completed)
- **Automatic scoring** — Score from checkpoint completion with configurable partial-completion weight
- **Evidence** — Per-checkpoint file uploads stored in `audit_files`
- **Dashboard** — Totals, pass/fail counts, score distribution, performance by department, audit history
- **Reports** — Filterable audit reports with export
- **PDF export** — Sync or async PDF generation with progress for large audits
- **Notifications** — Email and in-app notifications on completion; optional alerts below score threshold
- **My Audits** — Auditees can view audits where they are the responsible person
- **Company settings** — Per-company scoring, notification, and PDF options

## Requirements

- Parent Laravel application with modular structure (`nwidart/laravel-modules` or equivalent)
- PHP 8.x and dependencies required by the host app
- MySQL (or compatible database) with existing `companies`, `users`, and `teams` tables
- Queue worker recommended for async PDF export jobs

## Installation

1. Copy this module into the host application’s modules directory:

   ```
   <laravel-app>/Modules/Audit/
   ```

2. Ensure the module is registered and active in `module.json` (`"active": 1`).

3. From the Laravel application root, run migrations:

   ```bash
   php artisan migrate
   ```

4. For existing companies, seed module settings and default audit configuration:

   ```bash
   php artisan audit:activate
   ```

5. Enable the **audit** module for the company and assign permissions to roles (Settings → Roles & Permissions).

New companies receive default settings automatically via `CompanyCreatedListener`.

## Configuration

Module config is merged from `Config/config.php` and can be published:

```bash
php artisan vendor:publish --tag=config
```

| Key | Default | Description |
|-----|---------|-------------|
| `partial_completion_weight` | `0.5` | Weight applied to partially completed checkpoints when calculating score |
| `score_threshold_alert` | `70` | Scores below this percentage trigger threshold alerts |

Per-company overrides are stored in `audit_settings` (partial weight, threshold, notification toggles, auto PDF).

## Permissions

| Permission | Description |
|------------|-------------|
| `add_audit_template` | Create audit templates |
| `view_audit_template` | View templates |
| `edit_audit_template` | Edit templates |
| `delete_audit_template` | Delete templates |
| `add_audit` | Start new audits |
| `view_audit` | View audits (all / added / owned / both) |
| `edit_audit` | Edit audits |
| `delete_audit` | Delete audits |
| `manage_audit_settings` | Manage module settings |

## Main routes

All routes use the `auth` middleware and are prefixed with `/account`:

| Area | Route prefix / name |
|------|---------------------|
| Dashboard | `audit/audit-dashboard` |
| Templates | `audit/audit-templates` |
| Audits | `audits`, `audits/{audit}/execute` |
| My Audits | `my-audits` |
| Reports | `audit/audit-reports` |
| Settings | `audit-settings` |

## Database tables

| Table | Purpose |
|-------|---------|
| `audit_settings` | Company-level module settings |
| `audit_templates` | Reusable audit checklists |
| `audit_template_checkpoints` | Questions/items per template |
| `audits` | Audit sessions and scores |
| `audit_checkpoint_responses` | Per-checkpoint answers and status |
| `audit_files` | Uploaded evidence files |

## Project structure

```
Audit/
├── Config/              # Module configuration
├── Console/             # Artisan commands (audit:activate)
├── Database/
│   ├── Migrations/
│   └── Seeders/
├── Entities/            # Eloquent models
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Jobs/                # Async PDF export
├── Notifications/
├── Providers/
├── Resources/
│   ├── lang/            # en, eng locales
│   └── views/
├── Routes/
│   ├── web.php
│   └── api.php          # Reserved for future API
├── module.json
└── start.php
```

## Artisan commands

| Command | Description |
|---------|-------------|
| `php artisan audit:activate` | Creates module role settings and default `audit_settings` for all existing companies |

## Repository

**GitHub:** [https://github.com/charchilkhandelwal2522/Audit](https://github.com/charchilkhandelwal2522/Audit)

```bash
git clone https://github.com/charchilkhandelwal2522/Audit.git
cd Audit
```

## License

License terms follow the parent application. Specify here if this module is distributed separately.
