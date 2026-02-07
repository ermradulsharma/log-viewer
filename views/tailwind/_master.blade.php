<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-slate-50 antialiased">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Log Viewer">
    <meta name="author" content="Er. Mradul Sharma">
    <title>Log Viewer - Developed by Er. Mradul Sharma</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    {{-- Favicon --}}
    <link rel="icon"
        href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📜</text></svg>">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                        slate: {
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Level Colors - Hardcoded to ensure availability without CDN compilation issues */
        .text-level-all {
            color: #6366f1;
        }

        /* Indigo-500 */
        .text-level-emergency {
            color: #881337;
        }

        /* Rose-900 - Darkest Red */
        .text-level-alert {
            color: #be123c;
        }

        /* Rose-700 */
        .text-level-critical {
            color: #e11d48;
        }

        /* Rose-600 */
        .text-level-error {
            color: #ef4444;
        }

        /* Red-500 */
        .text-level-warning {
            color: #f59e0b;
        }

        /* Amber-500 */
        .text-level-notice {
            color: #3b82f6;
        }

        /* Blue-500 */
        .text-level-info {
            color: #0ea5e9;
        }

        /* Sky-500 */
        .text-level-debug {
            color: #a8a29e;
        }

        /* Stone-400 */

        .bg-level-all {
            background-color: #6366f1;
        }

        .bg-level-emergency {
            background-color: #881337;
        }

        .bg-level-alert {
            background-color: #be123c;
        }

        .bg-level-critical {
            background-color: #e11d48;
        }

        .bg-level-error {
            background-color: #ef4444;
        }

        .bg-level-warning {
            background-color: #f59e0b;
        }

        .bg-level-notice {
            background-color: #3b82f6;
        }

        .bg-level-info {
            background-color: #0ea5e9;
        }

        .bg-level-debug {
            background-color: #a8a29e;
        }

        .border-level-all {
            border-color: #6366f1;
        }

        .border-level-emergency {
            border-color: #881337;
        }

        .border-level-alert {
            border-color: #be123c;
        }

        .border-level-critical {
            border-color: #e11d48;
        }

        .border-level-error {
            border-color: #ef4444;
        }

        .border-level-warning {
            border-color: #f59e0b;
        }

        .border-level-notice {
            border-color: #3b82f6;
        }

        .border-level-info {
            border-color: #0ea5e9;
        }

        .border-level-debug {
            border-color: #a8a29e;
        }


        .border-level-all {
            border-color: #6366f1;
        }

        .border-level-emergency {
            border-color: #ef4444;
        }

        .border-level-alert {
            border-color: #f97316;
        }

        .border-level-critical {
            border-color: #dc2626;
        }

        .border-level-error {
            border-color: #ef4444;
        }

        .border-level-warning {
            border-color: #f59e0b;
        }

        .border-level-notice {
            border-color: #3b82f6;
        }

        .border-level-info {
            border-color: #0ea5e9;
        }

        .border-level-debug {
            border-color: #a8a29e;
        }

        .level-icon {
            opacity: 0.5;
        }
    </style>
    <style>
        /* CustomScrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="h-full flex flex-col">

    <!-- Navbar -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <i class="fa fa-book text-primary-600 text-xl"></i>
                        <span class="font-bold text-slate-900 text-lg tracking-tight">Log Viewer</span>
                    </div>
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
                        <a href="{{ route('log-viewer::dashboard') }}"
                            class="{{ Route::is('log-viewer::dashboard') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            <i class="fa fa-dashboard mr-2"></i> @lang('Dashboard')
                        </a>
                        <a href="{{ route('log-viewer::logs.list') }}"
                            class="{{ Route::is('log-viewer::logs.list') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            <i class="fa fa-archive mr-2"></i> @lang('Logs')
                        </a>
                        <a href="{{ route('log-viewer::compare') }}"
                            class="{{ Route::is('log-viewer::compare') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            <i class="fa fa-balance-scale mr-2"></i> @lang('Compare')
                        </a>
                        <a href="{{ route('log-viewer::logs.live') }}"
                            class="{{ Route::is('log-viewer::logs.live') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            <i class="fa fa-stream mr-2"></i> @lang('Live Tail')
                        </a>
                        <a href="{{ route('log-viewer::global-search') }}"
                            class="{{ Route::is('log-viewer::global-search') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            <i class="fa fa-search mr-2"></i> @lang('Global Search')
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button id="theme-toggle" type="button"
                        class="text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-sm p-2.5 transition-all">
                        <i id="theme-toggle-dark-icon" class="hidden fa fa-moon"></i>
                        <i id="theme-toggle-light-icon" class="hidden fa fa-sun"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 sm:px-6 lg:p-6">
        <div class="mx-auto">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center text-sm text-slate-500">
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center rounded-md bg-primary-100 px-2 py-1 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-700/10">v{{ log_viewer()->version() }}</span>
            </div>
            @if (config('log-viewer.footer.enabled', true))
                <div class="flex items-center gap-1">
                    {{ __('Developed By') }} <i class="fa fa-heart text-red-500 mx-1"></i> <span
                        class="font-medium text-slate-700">{{ config('log-viewer.footer.author', 'Mradul Sharma') }}</span>
                    &copy;
                </div>
            @endif
        </div>
    </footer>

    {{-- Scripts --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        function ready(fn) {
            if (document.readyState !== 'loading') {
                fn();
            } else {
                document.addEventListener('DOMContentLoaded', fn);
            }
        }

        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        // Change the icons inside the button based on previous settings
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
            document.documentElement.classList.add('dark');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
            document.documentElement.classList.remove('dark');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');

        themeToggleBtn.addEventListener('click', function() {
            // toggle icons inside button
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            // if set via local storage previously
            if (localStorage.getItem('color-theme')) {
                if (localStorage.getItem('color-theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                }

                // if NOT set via local storage previously
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
            }
        });
    </script>
    @yield('modals')
    @yield('scripts')
</body>

</html>
