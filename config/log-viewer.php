<?php

use Skywalker\LogViewer\Contracts\Utilities\Filesystem;

return [

    /* -----------------------------------------------------------------
     |  Log Files Storage Path
     | -----------------------------------------------------------------
     |
     | This option defines the absolute path where your log files are stored.
     | By default, it points to Laravel's storage/logs directory.
     |
     | You can customize this to point to any directory where your application
     | stores log files. Make sure the directory exists and is readable.
     |
     | Default: storage_path('logs')
     |
     */

    'storage-path' => storage_path('logs'),

    /* -----------------------------------------------------------------
     |  Log Files Pattern
     | -----------------------------------------------------------------
     |
     | This option defines the pattern used to identify and parse log files.
     | The pattern consists of three components:
     |
     | - prefix:    The prefix of your log files (e.g., 'laravel-')
     | - date:      A regex pattern matching the date format in filenames
     | - extension: The file extension of your log files (e.g., '.log')
     |
     | The complete pattern will be: {prefix}{date}{extension}
     | Example: laravel-2024-02-06.log
     |
     */

    'pattern' => [
        'prefix'    => Filesystem::PATTERN_PREFIX,
        'date'      => Filesystem::PATTERN_DATE,
        'extension' => Filesystem::PATTERN_EXTENSION,
    ],

    /* -----------------------------------------------------------------
     |  Locale
     | -----------------------------------------------------------------
     |
     | This option defines the language used for the LogViewer interface.
     | Set to 'auto' to automatically detect the locale from your Laravel
     | application, or specify a specific locale code (e.g., 'en', 'fr').
     |
     | Supported locales:
     |   'auto', 'ar', 'bg', 'bn', 'de', 'en', 'es', 'et', 'fa', 'fr',
     |   'he', 'hu', 'hy', 'id', 'it', 'ja', 'ko', 'ms', 'nl', 'pl',
     |   'pt-BR', 'ro', 'ru', 'si', 'sv', 'th', 'tr', 'uk', 'uz',
     |   'zh', 'zh-TW'
     |
     */

    'locale' => 'auto',

    /* -----------------------------------------------------------------
     |  Theme
     | -----------------------------------------------------------------
     |
     | This option defines the visual theme for the LogViewer interface.
     |
     | Built-in themes:
     |   - 'bootstrap-5' (Modern, recommended for Laravel 9+)
     |   - 'bootstrap-4' (Stable, compatible with older Bootstrap sites)
     |   - 'bootstrap-3' (Legacy support)
     |
     | You can create custom themes by placing them in:
     | resources/views/vendor/log-viewer/{theme-name}
     |
     */

    'theme' => 'tailwind',

    /* -----------------------------------------------------------------
     |  Route Settings
     | -----------------------------------------------------------------
     |
     | This section configures the routing for LogViewer.
     |
     | - enabled:    Whether to register the default routes automatically.
     | - attributes: Route attributes like prefix and middleware.
     | - show:       The named route for displaying a single log file.
     |
     | SECURITY WARNING: Always protect LogViewer routes with proper
     | authentication middleware in production environments.
     |
     */

    'route' => [
        'enabled' => true,

        'attributes' => [
            'prefix'     => 'log-viewer',
            'middleware' => env('LOGVIEWER_MIDDLEWARE') ? explode(',', env('LOGVIEWER_MIDDLEWARE')) : null,
        ],

        'show' => 'log-viewer::logs.show',
    ],

    /* -----------------------------------------------------------------
     |  Pagination
     | -----------------------------------------------------------------
     |
     | This option defines how many log entries are displayed per page.
     | Increasing this value may impact page load performance on large files.
     |
     | Default: 30
     |
     */

    'per-page' => 30,

    /* -----------------------------------------------------------------
     |  Download Settings
     | -----------------------------------------------------------------
     |
     | This section configures the settings for downloading log files.
     |
     | The downloaded filename will be: {prefix}{date}.{extension}
     |
     */

    'download' => [
        'prefix'    => 'laravel-',
        'extension' => 'log',
    ],

    /* -----------------------------------------------------------------
     |  Menu Settings
     | -----------------------------------------------------------------
     |
     | This section configures the sidebar menu and filtering behavior.
     |
     | - filter-route:  Named route for filtering logs.
     | - icons-enabled: Set to false to disable icons in the menu.
     |
     */

    'menu' => [
        'filter-route'  => 'log-viewer::logs.filter',
        'icons-enabled' => true,
    ],

    /* -----------------------------------------------------------------
     |  Icons
     | -----------------------------------------------------------------
     |
     | This section defines the Font Awesome icons used for each log level.
     | Requirements: Font Awesome >= 4.3 (included via CDN by default).
     |
     | Reference: https://fontawesome.io/icons/
     |
     */

    'icons' => [
        'all'       => 'fa fa-fw fa-list',
        'emergency' => 'fa fa-fw fa-bug',
        'alert'     => 'fa fa-fw fa-bullhorn',
        'critical'  => 'fa fa-fw fa-heartbeat',
        'error'     => 'fa fa-fw fa-times-circle',
        'warning'   => 'fa fa-fw fa-exclamation-triangle',
        'notice'    => 'fa fa-fw fa-exclamation-circle',
        'info'      => 'fa fa-fw fa-info-circle',
        'debug'     => 'fa fa-fw fa-life-ring',
    ],

    /* -----------------------------------------------------------------
     |  Colors
     | -----------------------------------------------------------------
     |
     | Defines the color palette for log levels. These colors are used in
     | charts, badges, and progress bars throughout the dashboard.
     |
     */

    'colors' => [
        'levels' => [
            'empty'     => '#D1D1D1',
            'all'       => '#8A8A8A',
            'emergency' => '#B71C1C',
            'alert'     => '#D32F2F',
            'critical'  => '#F44336',
            'error'     => '#FF5722',
            'warning'   => '#FF9100',
            'notice'    => '#4CAF50',
            'info'      => '#1976D2',
            'debug'     => '#90CAF9',
        ],
    ],

    /* -----------------------------------------------------------------
     |  IDE Integration
     | -----------------------------------------------------------------
     |
     | This option allows you to configure the IDE integration for stack traces.
     | When enabled, file paths in stack traces will be clickable.
     |
     | Supported: 'vscode', 'phpstorm', 'sublime', 'atom', 'textmate', 'macvim'
     |
     */

    'ide' => 'vscode',

    /* -----------------------------------------------------------------
     |  Sensitive Data Masking
     | -----------------------------------------------------------------
     |
     | When enabled, LogViewer will automatically mask sensitive data patterns
     | in headers, stack traces, and contexts using regex.
     |
     */

    'masking' => [
        'enabled' => true,
        'patterns' => [
            '/[a-zA-Z0-9._%+-]+@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/' => '****@$1', // Emails
            '/(?i)(API_KEY|APP_KEY|STRIPE_SECRET|PASSWORD)[=:][^\s&"]+/'   => '$1=********', // Keys
            '/(?:\d{4}-){3}\d{4}|\d{16}/'                      => '****-****-****-****', // Credit Cards
        ],
    ],

    /* -----------------------------------------------------------------
     |  Stack Trace Highlighting
     | -----------------------------------------------------------------
     |
     | Defines regex patterns to highlight specific parts of stack traces.
     | Matched lines will be styled differently for easier debugging.
     |
     */

    'highlight' => [
        '^#\d+',          // Stack frame numbers
        '^Stack trace:',  // Stack trace header
    ],

    /* -----------------------------------------------------------------
     |  Webhooks (Notifications)
     | -----------------------------------------------------------------
     |
     | Configure Slack or Discord webhooks to receive real-time alerts
     | when high-severity logs are detected.
     |
     */

    'webhooks' => [
        'enabled' => env('LOGVIEWER_WEBHOOKS_ENABLED', false),
        'url'     => env('LOGVIEWER_WEBHOOK_URL'),
        'levels'  => ['emergency', 'alert', 'critical'],
    ],

    /* -----------------------------------------------------------------
     |  Footer Settings
     | -----------------------------------------------------------------
     |
     | This section allows you to customize or disable the LogViewer footer.
     |
     | - enabled: Toggle the visibility of the "Created with heart" attribution.
     | - author:  The name to display in the attribution footer.
     |
     | You can override the author name via the LOGVIEWER_FOOTER_AUTHOR
     | environment variable in your .env file.
     |
     */

    'footer' => [
        'enabled' => true,
        'author'  => env('LOGVIEWER_FOOTER_AUTHOR', 'Mradul Sharma'),
    ],

    /* -----------------------------------------------------------------
     |  Smart Retention Policy
     | -----------------------------------------------------------------
     |
     | Automatically clean up logs based on their age and severity.
     | 'days' => 0 means no auto-deletion for that level.
     |
     */

    'retention' => [
        'enabled' => env('LOGVIEWER_RETENTION_ENABLED', false),
        'default' => 30, // Default days to keep
        'levels'  => [
            'debug'     => 7,
            'info'      => 14,
            'notice'    => 14,
            'warning'   => 30,
            'error'     => 60,
            'critical'  => 90,
            'alert'     => 90,
            'emergency' => 90,
        ],
    ],

    /* -----------------------------------------------------------------
     |  Audit Logs
     | -----------------------------------------------------------------
     |
     | Track actions taken within the LogViewer (View, Download, Delete).
     |
     */

    'audit' => [
        'enabled' => env('LOGVIEWER_AUDIT_ENABLED', true),
        'path'    => storage_path('logs/log-viewer-audit.json'),
    ],

    /* -----------------------------------------------------------------
     |  Log Channels (Multi-Format Support)
     | -----------------------------------------------------------------
     |
     | Define patterns for different log formats.
     |
     */

    'channels' => [
        'laravel' => [
            'name'    => 'Laravel',
            'pattern' => '/^\[(?P<datetime>.*?)\] (?P<env>\w+)\.(?P<level>\w+): (?P<header>.*)/m',
        ],
        'nginx_access' => [
            'name'    => 'Nginx Access',
            'pattern' => '/^(?P<ip>\S+) \S+ \S+ \[(?P<datetime>.*?)\] "(?P<header>.*?)" (?P<level>\d+) \d+/m',
        ],
        'nginx_error' => [
            'name'    => 'Nginx Error',
            'pattern' => '/^(?P<datetime>.*?) \[(?P<level>\w+)\] (?P<ip>\d+)#\d+: \*(?P<cid>\d+) (?P<header>.*)/m',
        ],
    ],

];
