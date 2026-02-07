@extends('log-viewer::tailwind._master')

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-slate-900">@lang('Global Search')</h1>
        <p class="text-slate-500 text-sm">@lang('Searching across all log files')</p>
    </div>

    {{-- Search Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <form action="{{ route('log-viewer::global-search') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="query" value="{{ $query }}" placeholder="@lang('Search keywords across all logs...')"
                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all text-sm">
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" name="regex" value="1" {{ request('regex') ? 'checked' : '' }}
                            class="sr-only peer">
                        <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:bg-primary-600 transition-colors">
                        </div>
                        <div
                            class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full peer-checked:translate-x-5 transition-transform">
                        </div>
                    </div>
                    <span class="text-sm font-medium text-slate-600 group-hover:text-slate-900">@lang('Regex')</span>
                </label>
                <button type="submit"
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors text-sm">
                    @lang('Search')
                </button>
            </div>
        </form>
    </div>

    @if (!empty($query))
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">
                    @lang('Search Results')
                    <span
                        class="ml-2 px-2 py-0.5 bg-slate-100 text-slate-500 text-xs rounded-full">{{ $entries->total() }}</span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">@lang('Date')</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">@lang('Level')</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">@lang('Time')</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">@lang('Header')</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">
                                @lang('Actions')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($entries as $key => $item)
                            @php
                                $entry = $item['entry'];
                                $date = $item['date'];
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-600">
                                    <a href="{{ route('log-viewer::logs.show', [$date]) }}"
                                        class="hover:text-primary-600 underline decoration-dotted">
                                        {{ $date }}
                                    </a>
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
                                <td class="px-6 py-4 text-sm text-slate-700 max-w-md truncate" title="{{ $entry->header }}">
                                    @if ($entry->ip)
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-600 border border-slate-200 mr-2"
                                            title="IP Address">
                                            <i class="fa fa-globe-americas mr-1 text-[8px]"></i>{{ $entry->ip }}
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
                                    {{ $entry->header() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="https://stackoverflow.com/search?q=[laravel] {{ $entry->header }}"
                                            target="_blank" class="text-slate-400 hover:text-slate-600 transition-colors"
                                            title="Search on Stack Overflow">
                                            <i class="fab fa-stack-overflow text-lg"></i>
                                        </a>
                                        @if ($entry->hasStack() || $entry->hasContext())
                                            <button onclick="toggleStack('log-stack-{{ $key }}')"
                                                class="text-primary-600 hover:text-primary-700 text-xs font-bold uppercase tracking-wide">
                                                <i class="fa fa-search-plus mr-1"></i> @lang('Details')
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @if ($entry->hasStack() || $entry->hasContext())
                                <tr id="log-stack-{{ $key }}" class="hidden bg-slate-50">
                                    <td colspan="5" class="px-6 py-4 border-b border-slate-100">
                                        <div class="space-y-4">
                                            @if ($entry->hasStack())
                                                <div>
                                                    <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">
                                                        @lang('Stack Trace')</h4>
                                                    <div
                                                        class="bg-white border border-slate-200 rounded-lg p-4 font-mono text-xs overflow-x-auto max-h-96 text-red-700">
                                                        {!! $entry->stack() !!}
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($entry->hasContext())
                                                <div>
                                                    <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">
                                                        @lang('Context')</h4>
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
                                        <i class="fa fa-search text-4xl mb-3 opacity-20"></i>
                                        <span>@lang('No entries found matching your query')</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($entries->hasPages())
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                    {{ $entries->links('log-viewer::tailwind._pagination') }}
                </div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="flex flex-col items-center max-w-md mx-auto">
                <div
                    class="w-16 h-16 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center mb-4 text-2xl">
                    <i class="fa fa-search"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">@lang('Search across all logs')</h3>
                <p class="text-slate-500 text-sm mb-6">@lang('Enter a keyword above to scan all historical log files. You can use regex for advanced filtering.')</p>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        function toggleStack(id) {
            const el = document.getElementById(id);
            if (el.classList.contains('hidden')) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        }
    </script>
@endsection
