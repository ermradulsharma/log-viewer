# LogViewer [![License](https://img.shields.io/github/license/ermradulsharma/log-viewer?style=flat-square)](LICENSE.md) [![For Laravel](https://img.shields.io/badge/Laravel-5.x%20to%2012.x-orange.svg?style=flat-square)](https://github.com/ermradulsharma/log-viewer)

[![GitHub Release](https://img.shields.io/github/v/release/ermradulsharma/log-viewer?style=flat-square)](https://github.com/ermradulsharma/log-viewer)
[![GitHub Stars](https://img.shields.io/github/stars/ermradulsharma/log-viewer?style=flat-square)](https://github.com/ermradulsharma/log-viewer)
[![GitHub Issues](https://img.shields.io/github/issues/ermradulsharma/log-viewer?style=flat-square)](https://github.com/ermradulsharma/log-viewer)

_By Mradul Sharma_

This package allows you to manage and keep track of each one of your log files.

> **NOTE: You can also use LogViewer as an API.**

Official documentation for LogViewer can be found at the [\_docs folder](_docs/1.Installation-and-Setup.md).

Feel free to check out the [license](LICENSE.md), and [contribution guidelines](CONTRIBUTING.md).

## Features

- A great Log viewer API.
- Laravel `5.x` to `12.x` are supported.
- Ready to use (Views, Routes, controllers &hellip; Out of the box) [Note: No need to publish assets]
- **Security**: Role-Based Access Control (Admin, Auditor, Viewer) & Audit Trails.
- **Insights**: Performance Hotspots, Slow Query Detection, and AI-Powered Error Analysis.
- **Reporting**: Automated Executive Summaries (PDF/HTML) & Email Dispatch.
- **Integrations**: Push logs to Jira/GitHub with one click.
- View, paginate, filter, download and delete logs.
- Load a custom logs storage path.
- Localized log levels.
- Logs menu/tree generator.
- Grouped logs by dates and levels.
- Customized log levels icons.
- **Dark Mode** support for professional environments.
- Works great with big logs !!
- Well documented package (IDE Friendly).
- Well tested (100% code coverage with maximum code quality).

## Version Compatibility

| Laravel                        | LogViewer                             |
| :----------------------------- | :------------------------------------ |
| ![Laravel v12.x][laravel_12_x] | ![LogViewer v1.0.0][log_viewer_1_0_0] |
| ![Laravel v11.x][laravel_11_x] | ![LogViewer v1.0.0][log_viewer_1_0_0] |
| ![Laravel v10.x][laravel_10_x] | ![LogViewer v1.0.0][log_viewer_1_0_0] |
| ![Laravel v9.x][laravel_9_x]   | ![LogViewer v1.0.0][log_viewer_1_0_0] |
| ![Laravel v8.x][laravel_8_x]   | ![LogViewer v1.0.0][log_viewer_1_0_0] |
| ![Laravel v7.x][laravel_7_x]   | ![LogViewer v1.0.0][log_viewer_1_0_0] |
| ![Laravel v6.x][laravel_6_x]   | ![LogViewer v1.0.0][log_viewer_1_0_0] |
| ![Laravel v5.x][laravel_5_x]   | ![LogViewer v1.0.0][log_viewer_1_0_0] |

## Installation & Setup

### Composer

You can install this package via [Composer](http://getcomposer.org/).

#### From Packagist (Stable)

Run this command:

```bash
composer require ermradulsharma/log-viewer:^1.0
```

#### From VCS (Development)

If you want to install directly from the Git repository, add the following to your `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/ermradulsharma/log-viewer.git"
    }
],
```

Then run:

```bash
composer require ermradulsharma/log-viewer:dev-master
```

### Laravel

> **NOTE :** The package will automatically register itself if you're using Laravel `>= v5.5`, so you can skip this section.

Once the package is installed, you can register the service provider in `config/app.php` in the `providers` array:

```php
'providers' => [
    ...
    Ermradulsharma\LogViewer\LogViewerServiceProvider::class,
],
```

> No need to register the LogViewer facade, it's done automagically.

#### Important Note:

For Laravel 8.x and above, you need to match the pagination styling with LogViewer template. The [default pagination uses tailwindcss](https://laravel.com/docs/8.x/upgrade#pagination) as default styling.

## Configuration

To publish the config and translations files, run this command:

```bash
php artisan log-viewer:publish
```

> To force publishing add `--force` flag.

## Enterprise Features

LogViewer now includes a suite of advanced tools for production environments:

### 🛡️ Security & RBAC

Control who sees what. Define granular roles (Admin, Auditor, Viewer) and track every administrative action with the built-in [Audit Trail](_docs/4.Security-and-RBAC.md).

### 🚀 Performance Insights

Don't just see errors—see bottlenecks. The new [Performance Hotspots](_docs/5.Performance-Insights.md) widget identifies slow queries and high-memory operations automatically.

### 📊 Automated Reporting

Keep stakeholders informed without lifting a finger. Generate [Executive Summaries](_docs/6.Integrations-and-Reporting.md) with a single click or schedule them via email.

## Usage

Go to `http://{your-project}/log-viewer` (See the [Configuration](_docs/2.Configuration.md) page to change the uri and other stuff).

### CLI Commands

- **Publish resources**:

  ```bash
  php artisan log-viewer:publish
  ```

- **Check application requirements & log files**:

  ```bash
  php artisan log-viewer:check
  ```

- **View logs stats**:

  ```bash
  php artisan log-viewer:stats
  ```

- **Clear all generated log files**:
  ```bash
  php artisan log-viewer:clear
  ```

## Table of contents

1. [Installation and Setup](_docs/1.Installation-and-Setup.md)
2. [Configuration](_docs/2.Configuration.md)
3. [Usage](_docs/3.Usage.md)
4. [Security & RBAC](_docs/4.Security-and-RBAC.md)
5. [Performance Insights](_docs/5.Performance-Insights.md)
6. [Integrations & Reporting](_docs/6.Integrations-and-Reporting.md)

### Supported localizations

> Dear artisans, i'm counting on you to help me out to add more translations ( ^\_^)b

| Local   | Language              |
| ------- | --------------------- |
| `ar`    | Arabic                |
| `bg`    | Bulgarian             |
| `bn`    | Bengali               |
| `de`    | German                |
| `en`    | English               |
| `es`    | Spanish               |
| `et`    | Estonian              |
| `fa`    | Farsi                 |
| `fr`    | French                |
| `he`    | Hebrew                |
| `hu`    | Hungarian             |
| `hy`    | Armenian              |
| `id`    | Indonesian            |
| `it`    | Italian               |
| `ja`    | Japanese              |
| `ko`    | Korean                |
| `ms`    | Malay                 |
| `nl`    | Dutch                 |
| `pl`    | Polish                |
| `pt-BR` | Brazilian Portuguese  |
| `ro`    | Romanian              |
| `ru`    | Russian               |
| `si`    | Sinhalese             |
| `sv`    | Swedish               |
| `th`    | Thai                  |
| `tr`    | Turkish               |
| `uk`    | Ukrainian             |
| `uz`    | Uzbek                 |
| `zh`    | Chinese (Simplified)  |
| `zh-TW` | Chinese (Traditional) |

## Contribution

Any ideas are welcome. Feel free to submit any issues or pull requests, please check the [contribution guidelines](CONTRIBUTING.md).

## Security

If you discover any security related issues, please email <skywalkerlknw@gmail.com> instead of using the issue tracker.

## Credits

- Mradul Sharma
- [All Contributors](https://packagist.org/packages/ermradulsharma/log-viewer)

[laravel_12_x]: https://img.shields.io/badge/version-12.x-blue.svg?style=flat-square "Laravel v12.x"
[laravel_11_x]: https://img.shields.io/badge/version-11.x-blue.svg?style=flat-square "Laravel v11.x"
[laravel_10_x]: https://img.shields.io/badge/version-10.x-blue.svg?style=flat-square "Laravel v10.x"
[laravel_9_x]: https://img.shields.io/badge/version-9.x-blue.svg?style=flat-square "Laravel v9.x"
[laravel_8_x]: https://img.shields.io/badge/version-8.x-blue.svg?style=flat-square "Laravel v8.x"
[laravel_7_x]: https://img.shields.io/badge/version-7.x-blue.svg?style=flat-square "Laravel v7.x"
[laravel_6_x]: https://img.shields.io/badge/version-6.x-blue.svg?style=flat-square "Laravel v6.x"
[laravel_5_x]: https://img.shields.io/badge/version-5.5-blue.svg?style=flat-square "Laravel v5.5"
[log_viewer_1_0_0]: https://img.shields.io/badge/version-1.0.0-blue.svg?style=flat-square "LogViewer v1.0.0"
