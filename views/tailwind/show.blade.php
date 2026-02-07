@extends('log-viewer::tailwind._master')

@section('content')
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar -->
        <div class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">@lang('Log Levels')</h3>
                </div>
                <nav class="p-2 space-y-1">
                    @foreach ($levels as $level => $count)
                        <a href="{{ route('log-viewer::logs.filter', [$log->date, $level]) }}"
                            class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request('level') == $level ? 'bg-level-' . $level . ' text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                            <div class="flex items-center">
                                <span class="mr-2 opacity-70">{!! log_styler()->icon($level) !!}</span>
                                {{ ucfirst($level) }}
                            </div>
                            <span
                                class="text-[10px] font-bold {{ request('level') == $level ? 'bg-white/20' : 'bg-slate-100 text-slate-500' }} px-2 py-0.5 rounded-full">
                                {{ $count }}
                            </span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 min-w-0">
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                        <a href="{{ route('log-viewer::logs.list') }}"
                            class="text-slate-400 hover:text-primary-600 transition-colors">
                            <i class="fa fa-arrow-left text-sm"></i>
                        </a>
                        {{ $log->date }}
                        @if (request('query'))
                            <span class="text-slate-400 font-normal">/ @lang('Search Results')</span>
                        @endif
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        @lang('File Path'): <code
                            class="bg-slate-100 px-1 rounded text-primary-700 text-[10px]">{{ $log->getPath() }}</code>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('log-viewer::logs.list') }}"
                        class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <i class="fa fa-list mr-2"></i> @lang('All Logs')
                    </a>
                    <div class="h-6 w-px bg-slate-200 mx-1"></div>
                    <a href="{{ route('log-viewer::logs.show', [$log->date]) . '?group=1' }}"
                        class="inline-flex items-center px-4 py-2 {{ request('group') ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }} text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <i class="fa fa-layer-group mr-2"></i> @lang('Group Same')
                    </a>
                    @if ($userRole === 'admin' || $userRole === 'auditor')
                        <a href="{{ route('log-viewer::logs.download', [$log->date]) }}"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                            <i class="fa fa-download mr-2"></i> @lang('Download')
                        </a>
                    @endif
                    @if ($userRole === 'admin')
                        <button type="button" onclick="confirmDelete('{{ $log->date }}')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                            <i class="fa fa-trash-alt mr-2"></i> @lang('Delete')
                        </button>
                    @endif
                </div>
            </div>

            <!-- Search Bar -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
                <div class="flex items-center gap-4">
                    <form action="{{ route('log-viewer::logs.show', [$log->date]) }}" method="GET" class="flex-1">
                        <div class="relative group">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary-500 transition-colors">
                                <i class="fa fa-search text-sm"></i>
                            </div>
                            <input type="text" name="query" value="{{ request('query') }}"
                                class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-sm transition-all"
                                placeholder="@lang('Filter by message, IP, environment (e.g., error, 127.0.0.1, production)...')">
                            @if (request('query'))
                                <a href="{{ route('log-viewer::logs.show', [$log->date]) }}"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                    <i class="fa fa-times-circle"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                    @if ($userRole === 'admin' || $userRole === 'auditor')
                        <button type="button" onclick="openSaveSearchModal()"
                            class="inline-flex items-center px-4 py-2 bg-primary-50 text-primary-600 hover:bg-primary-100 text-sm font-semibold rounded-lg transition-all">
                            <i class="fa fa-bookmark mr-2"></i> @lang('Save Search')
                        </button>
                    @endif
                    <div class="flex items-center gap-2">
                        <span
                            class="text-xs font-bold text-slate-400 uppercase tracking-widest px-2">@lang('Sort')</span>
                        <form id="sort-form" action="{{ route('log-viewer::logs.show', [$log->date]) }}" method="GET">
                            <input type="hidden" name="query" value="{{ request('query') }}">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="order" value="asc"
                                    {{ request('order') == 'asc' ? 'checked' : '' }} class="sr-only peer"
                                    onchange="document.getElementById('sort-form').submit()">
                                <div
                                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600">
                                </div>
                                <span class="ml-3 text-xs font-medium text-slate-500">
                                    {{ request('order') == 'asc' ? __('Oldest First') : __('Newest First') }}
                                </span>
                            </label>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div
                class="grid grid-cols-1 sm:grid-cols-3 gap-0 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                <div class="p-5 text-center">
                    <h2 class="text-2xl font-bold text-slate-900">{{ $entries->total() }}</h2>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">@lang('Entries')</p>
                </div>
                <div class="p-5 text-center">
                    <h2 class="text-2xl font-bold text-slate-900">{{ $log->size() }}</h2>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">@lang('Size')</p>
                </div>
                <div class="p-5 text-center">
                    <h2 class="text-base font-bold text-slate-900">{{ $log->updatedAt() }}</h2>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">@lang('Updated At')</p>
                </div>
            </div>

            <!-- Logs -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                @if ($entries->hasPages())
                    <div class="px-6 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <span class="text-xs text-slate-500">
                            {{ __('Page :current of :last', ['current' => $entries->currentPage(), 'last' => $entries->lastPage()]) }}
                        </span>
                        {{ $entries->links('pagination::tailwind') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        @if (request('group'))
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-center">
                                        @lang('Count')
                                    </th>
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">@lang('Level')
                                    </th>
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">@lang('Last Seen')
                                    </th>
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">@lang('Message')
                                    </th>
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">
                                        @lang('Actions')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($entries as $groupItem)
                                    @php $entry = $groupItem['example']; @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                                                {{ $groupItem['count'] }}x
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-level-{{ $entry->level }} text-white">
                                                {!! $entry->level() !!}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono">
                                            {{ $entry->datetime->format('H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-700 max-w-2xl truncate"
                                            title="{{ $entry->header }}">
                                            @if ($entry->ip)
                                                <span
                                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-500 border border-slate-200 mr-2"
                                                    title="IP Address">
                                                    <i
                                                        class="fa fa-globe-americas mr-1 text-[8px]"></i>{{ $entry->ip }}
                                                </span>
                                            @endif
                                            @if ($entry->correlationId)
                                                <a href="{{ route('log-viewer::journey', $entry->correlationId) }}"
                                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono bg-primary-50 text-primary-600 border border-primary-100 mr-2 hover:bg-primary-100 transition-colors"
                                                    title="View Request Journey">
                                                    <i
                                                        class="fa fa-route mr-1 text-[8px]"></i>{{ Str::limit($entry->correlationId, 12, '..') }}
                                                </a>
                                            @endif
                                            {!! preg_replace(
                                                '/(?:execution|query|request|db|sql|api) (?:took|time|latency)[:]?\s+([\d.]+)\s*(ms|s)|memory (?:limit|usage)[:]?\s+([\d.]+)\s*(MB|GB)/i',
                                                '<span class="bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-md font-bold border border-amber-200">$0</span>',
                                                Str::limit($entry->header(), 100),
                                            ) !!}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="https://stackoverflow.com/search?q=[laravel] {{ $entry->header }}"
                                                    target="_blank"
                                                    class="text-slate-400 hover:text-slate-600 transition-colors"
                                                    title="Search on Stack Overflow">
                                                    <i class="fab fa-stack-overflow text-lg"></i>
                                                </a>
                                                @if ($entry->hasStack() || $entry->hasContext())
                                                    <button onclick="toggleStack('log-stack-{{ $loop->index }}')"
                                                        class="text-primary-600 hover:text-primary-700 text-xs font-bold uppercase tracking-wide">
                                                        <i class="fa fa-search-plus mr-1"></i> @lang('Details')
                                                    </button>
                                                @endif
                                                @php $entryHash = md5($entry->level . preg_replace(['/\d+/', '/\'[^\']*\'/', '/"[^"]*"/', '/\[.*?\]/'], ['N', "'S'", '"S"', ''], $entry->header)); @endphp
                                                @if ($userRole === 'admin' || $userRole === 'auditor')
                                                    <button
                                                        onclick="openNoteModal('{{ $entryHash }}', '{{ addslashes(Str::limit($entry->header, 50)) }}')"
                                                        class="inline-flex items-center text-slate-400 hover:text-amber-500 transition-colors"
                                                        title="@lang('Notes')">
                                                        <i class="fa fa-comment-alt text-lg"></i>
                                                        @if (isset($notes[$entryHash]))
                                                            <span
                                                                class="ml-1 text-[10px] font-bold bg-amber-100 text-amber-700 px-1 rounded-full">{{ count($notes[$entryHash]) }}</span>
                                                        @endif
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @if (isset($notes[$entryHash]))
                                        <tr class="bg-amber-50/30">
                                            <td colspan="5" class="px-6 py-2">
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($notes[$entryHash] as $note)
                                                        <div
                                                            class="px-3 py-1 bg-white border border-amber-100 rounded text-[10px] shadow-sm">
                                                            <span
                                                                class="font-bold text-amber-700 mr-2">{{ $note['user'] }}:</span>
                                                            <span class="text-slate-600 italic">{{ $note['text'] }}</span>
                                                            <span
                                                                class="ml-2 text-slate-400 opacity-70">{{ $note['time'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($entry->hasStack() || $entry->hasContext())
                                        <tr id="log-stack-{{ $loop->index }}" class="hidden bg-slate-50">
                                            <td colspan="5" class="px-6 py-4 border-b border-slate-100">
                                                <div class="space-y-4">
                                                    @if ($entry->hasStack())
                                                        <div>
                                                            <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">
                                                                Stack
                                                                Trace (Latest)</h4>
                                                            <div
                                                                class="bg-white border border-slate-200 rounded-lg p-4 font-mono text-xs overflow-x-auto max-h-96 text-red-700">
                                                                {!! $entry->stack() !!}
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if ($entry->hasContext())
                                                        <div>
                                                            <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">
                                                                Context
                                                                (Latest)
                                                            </h4>
                                                            <pre class="bg-slate-800 text-slate-200 rounded-lg p-4 font-mono text-xs overflow-x-auto">{{ $entry->context() }}</pre>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                            <div class="flex flex-col items-center">
                                                <i class="fa fa-inbox text-4xl mb-3 opacity-20"></i>
                                                <span>@lang('No grouped entries found')</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        @else
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">@lang('Level')
                                    </th>
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">@lang('Time')
                                    </th>
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">@lang('Env')
                                    </th>
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">@lang('Header')
                                    </th>
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">
                                        @lang('Actions')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($entries as $key => $entry)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-level-{{ $entry->level }} text-white">
                                                {!! $entry->level() !!}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono">
                                            {{ $entry->datetime->format('H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs border border-slate-200">{{ $entry->env }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-700 max-w-md truncate">
                                            @if ($entry->ip)
                                                <span
                                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-500 border border-slate-200 mr-2"
                                                    title="IP Address">
                                                    <i
                                                        class="fa fa-globe-americas mr-1 text-[8px]"></i>{{ $entry->ip }}
                                                </span>
                                            @endif
                                            @if ($entry->correlationId)
                                                <a href="{{ route('log-viewer::journey', $entry->correlationId) }}"
                                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono bg-primary-50 text-primary-600 border border-primary-100 mr-2 hover:bg-primary-100 transition-colors"
                                                    title="View Request Journey">
                                                    <i
                                                        class="fa fa-route mr-1 text-[8px]"></i>{{ Str::limit($entry->correlationId, 12, '..') }}
                                                </a>
                                            @endif
                                            {!! preg_replace(
                                                '/(?:execution|query|request|db|sql|api) (?:took|time|latency)[:]?\s+([\d.]+)\s*(ms|s)|memory (?:limit|usage)[:]?\s+([\d.]+)\s*(MB|GB)/i',
                                                '<span class="bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-md font-bold border border-amber-200">$0</span>',
                                                $entry->header(),
                                            ) !!}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="https://stackoverflow.com/search?q=[laravel] {{ $entry->header }}"
                                                    target="_blank"
                                                    class="text-slate-400 hover:text-slate-600 transition-colors"
                                                    title="Search on Stack Overflow">
                                                    <i class="fab fa-stack-overflow text-lg"></i>
                                                </a>
                                                @if ($entry->hasStack() || $entry->hasContext())
                                                    <button onclick="toggleStack('log-stack-{{ $key }}')"
                                                        class="text-primary-600 hover:text-primary-700 text-xs font-bold uppercase tracking-wide">
                                                        <i class="fa fa-search-plus mr-1"></i> @lang('Details')
                                                    </button>
                                                @endif
                                                @php $entryHash = md5($entry->level . preg_replace(['/\d+/', '/\'[^\']*\'/', '/"[^"]*"/', '/\[.*?\]/'], ['N', "'S'", '"S"', ''], $entry->header)); @endphp
                                                @if ($userRole === 'admin' || $userRole === 'auditor')
                                                    <button
                                                        onclick="openAIExplainModal('{{ base64_encode($entry->header) }}')"
                                                        class="inline-flex items-center text-slate-400 hover:text-purple-500 transition-colors"
                                                        title="@lang('Explain with AI')">
                                                        <i class="fa fa-robot text-lg"></i>
                                                    </button>
                                                @endif

                                                @if ($userRole === 'admin' || $userRole === 'auditor')
                                                    <button
                                                        onclick="openTrackerModal('{{ base64_encode($entry->header) }}', '{{ addslashes(Str::limit($entry->header, 50)) }}')"
                                                        class="inline-flex items-center text-slate-400 hover:text-blue-500 transition-colors"
                                                        title="@lang('Push to Tracker')">
                                                        <i class="fa fa-external-link-alt text-lg"></i>
                                                    </button>
                                                @endif

                                                @if ($userRole === 'admin' || $userRole === 'auditor')
                                                    <button
                                                        onclick="openNoteModal('{{ $entryHash }}', '{{ addslashes(Str::limit($entry->header, 50)) }}')"
                                                        class="inline-flex items-center text-slate-400 hover:text-amber-500 transition-colors"
                                                        title="@lang('Notes')">
                                                        <i class="fa fa-comment-alt text-lg"></i>
                                                        @if (isset($notes[$entryHash]))
                                                            <span
                                                                class="ml-1 text-[10px] font-bold bg-amber-100 text-amber-700 px-1 rounded-full">{{ count($notes[$entryHash]) }}</span>
                                                        @endif
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @if (isset($notes[$entryHash]))
                                        <tr class="bg-amber-50/30">
                                            <td colspan="5" class="px-6 py-2">
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($notes[$entryHash] as $note)
                                                        <div
                                                            class="px-3 py-1 bg-white border border-amber-100 rounded text-[10px] shadow-sm">
                                                            <span
                                                                class="font-bold text-amber-700 mr-2">{{ $note['user'] }}:</span>
                                                            <span class="text-slate-600 italic">{{ $note['text'] }}</span>
                                                            <span
                                                                class="ml-2 text-slate-400 opacity-70">{{ $note['time'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($entry->hasStack() || $entry->hasContext())
                                        <tr id="log-stack-{{ $key }}" class="hidden bg-slate-50">
                                            <td colspan="5" class="px-6 py-4 border-b border-slate-100">
                                                <div class="space-y-4">
                                                    @if ($entry->hasStack())
                                                        <div>
                                                            <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">
                                                                Stack
                                                                Trace</h4>
                                                            <div
                                                                class="bg-white border border-slate-200 rounded-lg p-4 font-mono text-xs overflow-x-auto max-h-96 text-red-700">
                                                                {!! $entry->stack() !!}
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if ($entry->hasContext())
                                                        <div>
                                                            <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">
                                                                Context
                                                            </h4>
                                                            <pre class="bg-slate-800 text-slate-200 rounded-lg p-4 font-mono text-xs overflow-x-auto">{{ $entry->context() }}</pre>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                            <div class="flex flex-col items-center">
                                                <i class="fa fa-inbox text-4xl mb-3 opacity-20"></i>
                                                <span>@lang('No log entries found')</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        @endif
                    </table>
                </div>
                @if ($entries->hasPages())
                    <div class="px-6 py-3 border-t border-slate-100 flex justify-center bg-slate-50">
                        {{ $entries->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <div id="delete-log-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="date" value="">

                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fa fa-exclamation-triangle text-red-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">
                                        @lang('Delete log file')</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-slate-500 modal-body-text">@lang('Are you sure you want to delete this log file? This action cannot be undone.')</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                @lang('Delete')
                            </button>
                            <button type="button" onclick="closeModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">
                                @lang('Cancel')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Note Modal -->
    <div id="note-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeNoteModal()"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform border border-amber-200 overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form id="note-form">
                        @csrf
                        <input type="hidden" name="hash" id="note-entry-hash">
                        <div class="bg-amber-50 px-4 py-3 border-b border-amber-100">
                            <h3 class="text-sm font-bold text-amber-900" id="note-modal-title">@lang('Add Team Note')</h3>
                            <p id="note-entry-summary" class="text-[10px] text-amber-700 truncate opacity-70"></p>
                        </div>
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
                            <textarea name="note" id="note-text" rows="3" required
                                class="w-full rounded-lg border-amber-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-sm"
                                placeholder="@lang('Write your note here... (e.g., investigating, fixed, etc.)')"></textarea>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" id="save-note-btn"
                                class="inline-flex w-full justify-center rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 sm:ml-3 sm:w-auto transition-colors">
                                @lang('Save Note')
                            </button>
                            <button type="button" onclick="closeNoteModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">
                                @lang('Cancel')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Search Modal -->
    <div id="save-search-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeSaveSearchModal()">
        </div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form id="save-search-form">
                        @csrf
                        <input type="hidden" name="query" value="{{ request('query') }}">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fa fa-save text-primary-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-base font-semibold leading-6 text-slate-900">@lang('Save Search Snapshot')
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-slate-500">@lang('Give this search filter a name to access it quickly from the dashboard.')</p>
                                        <input type="text" name="label" required
                                            class="mt-3 block w-full rounded-md border-slate-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                            placeholder="e.g., Critical Errors Today">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" id="save-search-btn"
                                class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto">
                                @lang('Save')
                            </button>
                            <button type="button" onclick="closeSaveSearchModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">
                                @lang('Cancel')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tracker Modal -->
    <div id="tracker-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeTrackerModal()">
        </div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form id="tracker-form">
                        @csrf
                        <input type="hidden" name="header" id="tracker-log-header">
                        <div class="bg-blue-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <i class="fa fa-external-link-alt"></i> @lang('Push to External Tracker')
                            </h3>
                        </div>
                        <div class="bg-white px-6 py-6">
                            <div class="mb-4">
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-2">@lang('Target Platform')</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label
                                        class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:border-blue-200 transition-all border-slate-100 bg-slate-50">
                                        <input type="radio" name="type" value="jira" checked
                                            class="sr-only peer">
                                        <div class="flex flex-col items-center peer-checked:text-blue-600">
                                            <i class="fab fa-jira text-3xl mb-2 opacity-30 peer-checked:opacity-100"></i>
                                            <span class="text-xs font-bold uppercase tracking-wide">Jira Software</span>
                                        </div>
                                        <div class="absolute top-2 right-2 hidden peer-checked:block text-blue-600">
                                            <i class="fa fa-check-circle"></i>
                                        </div>
                                    </label>
                                    <label
                                        class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:border-slate-300 transition-all border-slate-100 bg-slate-50">
                                        <input type="radio" name="type" value="github" class="sr-only peer">
                                        <div class="flex flex-col items-center peer-checked:text-slate-900">
                                            <i class="fab fa-github text-3xl mb-2 opacity-30 peer-checked:opacity-100"></i>
                                            <span class="text-xs font-bold uppercase tracking-wide">GitHub Issues</span>
                                        </div>
                                        <div class="absolute top-2 right-2 hidden peer-checked:block text-slate-900">
                                            <i class="fa fa-check-circle"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase mb-2">@lang('Issue Summary')</label>
                                <textarea name="summary" id="tracker-summary" rows="3" required
                                    class="w-full rounded-lg border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm"
                                    placeholder="@lang('Describe the issue briefly...')"></textarea>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100">
                            <button type="button" onclick="closeTrackerModal()"
                                class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                                @lang('Cancel')
                            </button>
                            <button type="submit" id="push-tracker-btn"
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                                <i class="fa fa-paper-plane mr-2"></i> @lang('Push to Tracker')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Explain Modal -->
    <div id="ai-explain-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeAIExplainModal()">
        </div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-purple-100">
                    <div class="bg-purple-600 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <i class="fa fa-robot animate-bounce"></i> @lang('AI Error Insight')
                        </h3>
                        <button onclick="closeAIExplainModal()" class="text-white/50 hover:text-white transition-colors">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div class="bg-white px-6 py-6 min-h-[300px]" id="ai-content">
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400" id="ai-loading">
                            <div class="relative w-16 h-16 mb-4">
                                <div
                                    class="absolute inset-0 rounded-full border-4 border-purple-100 border-t-purple-600 animate-spin">
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <i class="fa fa-brain text-purple-200 text-xl"></i>
                                </div>
                            </div>
                            <p class="text-sm font-medium animate-pulse">@lang('Analyzing patterns and stack trace...')</p>
                        </div>
                        <div id="ai-result" class="hidden prose prose-sm max-w-none text-slate-700">
                            <!-- Injected by JS -->
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-between border-t border-slate-100">
                        <span
                            class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">@lang('Powered by Advanced Pattern ML')</span>
                        <button type="button" onclick="closeAIExplainModal()"
                            class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors shadow-sm">
                            @lang('Close Insight')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleStack(id) {
            const el = document.getElementById(id);
            if (el) el.classList.toggle('hidden');
        }

        function confirmDelete(date) {
            const modal = document.getElementById('delete-log-modal');
            modal.querySelector('input[name="date"]').value = date;
            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('delete-log-modal').classList.add('hidden');
        }

        function openNoteModal(hash, summary) {
            document.getElementById('note-entry-hash').value = hash;
            document.getElementById('note-entry-summary').innerText = summary;
            document.getElementById('note-text').value = '';
            document.getElementById('note-modal').classList.remove('hidden');
        }

        function closeNoteModal() {
            document.getElementById('note-modal').classList.add('hidden');
        }

        function openSaveSearchModal() {
            document.getElementById('save-search-modal').classList.remove('hidden');
        }

        function closeSaveSearchModal() {
            document.getElementById('save-search-modal').classList.add('hidden');
        }

        async function openAIExplainModal(errorBase64) {
            const modal = document.getElementById('ai-explain-modal');
            const loading = document.getElementById('ai-loading');
            const result = document.getElementById('ai-result');
            const content = document.getElementById('ai-content');

            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            result.classList.add('hidden');

            try {
                const response = await fetch('{{ route('log-viewer::ai-explain') }}?error=' + errorBase64, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();

                if (data.explanation) {
                    result.innerHTML = `
                        <div class="space-y-4">
                            <div class="p-4 bg-purple-50 rounded-xl border border-purple-100 shadow-sm">
                                <h4 class="text-purple-900 font-bold flex items-center gap-2 mb-2">
                                    <i class="fa fa-lightbulb"></i> @lang('Executive Summary')
                                </h4>
                                <p class="text-purple-800 text-sm">${data.explanation}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-3 bg-red-50 rounded-lg border border-red-100">
                                    <h5 class="text-[10px] font-bold text-red-700 uppercase mb-1">@lang('Root Cause')</h5>
                                    <p class="text-xs text-red-800">${data.root_cause}</p>
                                </div>
                                <div class="p-3 bg-green-50 rounded-lg border border-green-100">
                                    <h5 class="text-[10px] font-bold text-green-700 uppercase mb-1">@lang('Recommended Fix')</h5>
                                    <p class="text-xs text-green-800">${data.recommendation}</p>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">@lang('Reasoning Details')</h5>
                                <div class="text-xs text-slate-600 italic bg-slate-50 p-3 rounded border border-slate-200">
                                    ${data.reasoning}
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    result.innerHTML =
                        '<p class="text-slate-500">@lang('Sorry, AI was unable to analyze this specific error pattern.')</p>';
                }

                loading.classList.add('hidden');
                result.classList.remove('hidden');
            } catch (error) {
                console.error('AI Error:', error);
                result.innerHTML = '<p class="text-red-500 font-bold">@lang('AI Service Connectivity Error. Please try again later.')</p>';
                loading.classList.add('hidden');
                result.classList.remove('hidden');
            }
        }

        function closeAIExplainModal() {
            document.getElementById('ai-explain-modal').classList.add('hidden');
        }

        function openTrackerModal(headerBase64, summary) {
            document.getElementById('tracker-log-header').value = headerBase64;
            document.getElementById('tracker-summary').value = "Error: " + summary;
            document.getElementById('tracker-modal').classList.remove('hidden');
        }

        function closeTrackerModal() {
            document.getElementById('tracker-modal').classList.add('hidden');
        }

        ready(() => {
            const trackerForm = document.getElementById('tracker-form');
            trackerForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('push-tracker-btn');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Pushing...';

                try {
                    const response = await fetch('{{ route('log-viewer::push-to-tracker') }}', {
                        method: 'POST',
                        body: new FormData(trackerForm),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert(data.message + " (Issue ID: " + data.issue_id + ")");
                        closeTrackerModal();
                    } else {
                        alert('Failed to push to tracker. Check permissions.');
                    }
                } catch (error) {
                    console.error('Error pushing tracker:', error);
                    alert('Network error while pushing to tracker.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });

            const noteForm = document.getElementById('note-form');
            noteForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('save-note-btn');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Saving...';

                try {
                    const response = await fetch('{{ route('log-viewer::notes.store') }}', {
                        method: 'POST',
                        body: new FormData(noteForm),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('Failed to save note. Please check permissions.');
                    }
                } catch (error) {
                    console.error('Error saving note:', error);
                    alert('Network error while saving note.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });

            const saveSearchForm = document.getElementById('save-search-form');
            saveSearchForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('save-search-btn');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Saving...';

                try {
                    const response = await fetch('{{ route('log-viewer::searches.save') }}', {
                        method: 'POST',
                        body: new FormData(saveSearchForm),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        closeSaveSearchModal();
                        alert('Search layout saved to dashboard!');
                    } else {
                        alert('Failed to save search. Check permissions.');
                    }
                } catch (error) {
                    console.error('Error saving search:', error);
                    alert('Network error while saving search.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        });
    </script>
@endsection
