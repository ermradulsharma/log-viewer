<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Log Viewer">
    <meta name="author" content="Er. Mradul Sharma">
    <title>Log Viewer - Developed by Er. Mradul Sharma</title>
    {{-- Styles --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon"
        href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📜</text></svg>">
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <style>
        :root {
            /* Core Palette - Slate & Indigo */
            --primary-50: #eef2ff;
            --primary-100: #e0e7ff;
            --primary-500: #6366f1;
            --primary-600: #4f46e5;
            --primary-700: #4338ca;

            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;

            /* Semantics */
            --bg-body: var(--slate-50);
            --bg-card: #ffffff;
            --text-main: var(--slate-800);
            --text-muted: var(--slate-600);
            --border-color: var(--slate-200);

            --font-main: 'Outfit', system-ui, -apple-system, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-body);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        body>.container-fluid {
            flex: 1;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            color: var(--slate-900);
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        /* Navbar */
        .navbar {
            background-color: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            box-shadow: none !important;
            padding: 0.75rem 0;
        }

        .navbar-brand {
            color: var(--primary-600) !important;
            font-weight: 700;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-nav .nav-link {
            color: var(--slate-600) !important;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem !important;
            transition: all 0.2s;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--primary-600) !important;
            background-color: var(--primary-50);
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: var(--slate-800);
        }

        /* Stats Box */
        .box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .box:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: var(--primary-200);
        }

        .box-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            color: var(--slate-800);
        }

        .box-content .box-text {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .box-content .box-number {
            color: var(--slate-900);
            font-size: 1.875rem;
            font-weight: 700;
            margin-top: 0.5rem;
        }

        /* Badges */
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 600;
            border-radius: 0.375rem;
            letter-spacing: 0.025em;
        }

        /* Level Specific Colors */
        .box {
            --level-color: var(--slate-300);
        }

        .box.level-all {
            --level-color: #6366f1;
        }

        .box.level-emergency {
            --level-color: #ef4444;
        }

        .box.level-alert {
            --level-color: #f97316;
        }

        .box.level-critical {
            --level-color: #dc2626;
        }

        .box.level-error {
            --level-color: #ef4444;
        }

        .box.level-warning {
            --level-color: #f59e0b;
        }

        .box.level-notice {
            --level-color: #3b82f6;
        }

        .box.level-info {
            --level-color: #0ea5e9;
        }

        .box.level-debug {
            --level-color: #a8a29e;
        }

        .box {
            border-left: 4px solid var(--level-color);
        }

        .box .box-icon {
            color: var(--level-color);
        }

        .badge {
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .badge-level-all {
            background-color: #6366f1;
        }

        .badge-level-emergency {
            background-color: #ef4444;
        }

        .badge-level-alert {
            background-color: #f97316;
        }

        .badge-level-critical {
            background-color: #dc2626;
        }

        .badge-level-error {
            background-color: #ef4444;
        }

        .badge-level-warning {
            background-color: #f59e0b;
        }

        .badge-level-notice {
            background-color: #3b82f6;
        }

        .badge-level-info {
            background-color: #0ea5e9;
        }

        .badge-level-debug {
            background-color: #a8a29e;
        }

        .badge.empty {
            background-color: #cbd5e1;
            color: var(--slate-600);
            text-shadow: none;
        }

        .badge-level-all {
            background-color: #6366f1;
        }

        .badge-level-emergency {
            background-color: #ef4444;
        }

        .badge-level-alert {
            background-color: #f97316;
        }

        .badge-level-critical {
            background-color: #dc2626;
        }

        .badge-level-error {
            background-color: #ef4444;
        }

        .badge-level-warning {
            background-color: #f59e0b;
        }

        .badge-level-notice {
            background-color: #3b82f6;
        }

        .badge-level-info {
            background-color: #0ea5e9;
        }

        .badge-level-debug {
            background-color: #a8a29e;
        }

        .badge.empty {
            background-color: #cbd5e1;
            color: var(--slate-600);
            text-shadow: none;
        }

        .badge.text-bg-info {
            background-color: var(--primary-500) !important;
        }

        /* Table */
        .table {
            --bs-table-bg: transparent;
        }

        .table thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom-width: 1px;
            padding: 1rem;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 1rem;
            color: var(--slate-700);
            font-weight: 500;
        }

        .table-hover tbody tr:hover {
            background-color: var(--slate-50);
        }

        .stack-content {
            font-family: var(--font-mono);
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 0.5rem;
            padding: 1rem;
            color: #b91c1c;
            font-size: 0.8rem;
        }

        /* Footer */
        .main-footer {
            border-top: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-muted);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-md navbar-dark sticky-top bg-dark">
        <a href="{{ route('log-viewer::dashboard') }}" class="navbar-brand m-0">
            <i class="fa fa-fw fa-book"></i>{{ __('Log Viewer') }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item {{ Route::is('log-viewer::dashboard') ? 'active' : '' }}">
                    <a href="{{ route('log-viewer::dashboard') }}" class="nav-link">
                        <i class="fa fa-dashboard"></i> @lang('Dashboard')
                    </a>
                </li>
                <li class="nav-item {{ Route::is('log-viewer::logs.list') ? 'active' : '' }}">
                    <a href="{{ route('log-viewer::logs.list') }}" class="nav-link">
                        <i class="fa fa-archive"></i> @lang('Logs')
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <main role="main">
            @yield('content')
        </main>
    </div>

    {{-- Footer --}}
    <footer class="main-footer">
        <div class="container-fluid d-flex justify-content-between py-2">
            <p class="text-muted mb-0"><span class="badge text-bg-info text-white">version
                    {{ log_viewer()->version() }}</span>
            </p>
            @if (config('log-viewer.footer.enabled', true))
                <p class="text-muted mb-0">{{ __('Developed By') }}
                    <i class="fa fa-heart"></i> <span>{{ config('log-viewer.footer.author', 'Mradul Sharma') }}</span>
                    <sup>&copy;</sup>
                </p>
            @endif
        </div>
    </footer>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        function ready(fn) {
            if (document.readyState !== 'loading') {
                fn();
            } else {
                document.addEventListener('DOMContentLoaded', fn);
            }
        }
    </script>
    @yield('modals')
    @yield('scripts')
</body>

</html>
