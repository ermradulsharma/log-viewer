@extends('log-viewer::tailwind._master')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs">
                    <li class="inline-flex items-center">
                        <a href="{{ route('log-viewer::dashboard') }}"
                            class="text-slate-500 hover:text-primary-600 transition-colors">@lang('Dashboard')</a>
                    </li>
                    <li class="flex items-center space-x-2 text-slate-400">
                        <i class="fa fa-chevron-right text-[10px]"></i>
                        <span>@lang('Request Journey')</span>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                @lang('Request Journey')
                <span
                    class="px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm font-mono">{{ $query }}</span>
            </h1>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider w-32">
                            @lang('Date')</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider w-32">
                            @lang('Level')</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            @lang('Content')</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">
                            @lang('Actions')</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($entries as $item)
                        @php $entry = $item['entry']; @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors duration-150 group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('log-viewer::logs.show', $item['date']) }}"
                                    class="text-xs font-medium text-slate-500 hover:text-primary-600 transition-colors">
                                    {{ $item['date'] }}
                                    <div class="text-[10px] opacity-70">{{ $entry->datetime->format('H:i:s') }}</div>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-level-{{ $entry->level }} text-white shadow-sm">
                                    {!! $entry->icon() !!} <span class="ml-1 uppercase">{{ $entry->name() }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    @if ($entry->ip)
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span
                                                class="px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-mono border border-slate-200">
                                                <i class="fa fa-globe-americas mr-1"></i>{{ $entry->ip }}
                                            </span>
                                        </div>
                                    @endif
                                    <p class="text-sm text-slate-700 font-medium leading-relaxed">{{ $entry->header() }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="https://stackoverflow.com/search?q=[laravel] {{ $entry->header }}"
                                        target="_blank"
                                        class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-all">
                                        <i class="fab fa-stack-overflow"></i>
                                    </a>
                                    @if ($entry->hasStack() || $entry->hasContext())
                                        <button onclick="toggleStack('log-stack-{{ $loop->index }}')"
                                            class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                            <i class="fa fa-search-plus"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if ($entry->hasStack() || $entry->hasContext())
                            <tr id="log-stack-{{ $loop->index }}" class="hidden bg-slate-50/30">
                                <td colspan="4" class="px-6 py-4">
                                    <div class="flex flex-col gap-4">
                                        @if ($entry->hasContext())
                                            <div>
                                                <h4
                                                    class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                                                    <i class="fa fa-info-circle text-primary-500"></i> @lang('Context')
                                                </h4>
                                                <pre
                                                    class="p-4 bg-slate-900 text-slate-300 rounded-lg text-xs overflow-x-auto shadow-inner border border-slate-800 leading-relaxed">{{ $entry->context() }}</pre>
                                            </div>
                                        @endif
                                        @if ($entry->hasStack())
                                            <div>
                                                <h4
                                                    class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                                                    <i class="fa fa-align-left text-primary-500"></i> @lang('Stack Trace')
                                                </h4>
                                                <pre
                                                    class="p-4 bg-slate-900 text-slate-400 rounded-lg text-xs overflow-x-auto shadow-inner border border-slate-800 leading-loose">{!! $entry->stack() !!}</pre>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-300">
                                        <i class="fa fa-route text-3xl"></i>
                                    </div>
                                    <h3 class="text-slate-900 font-bold">@lang('No Journey Logs Found')</h3>
                                    <p class="text-slate-500 text-sm max-w-xs mx-auto mt-1">@lang('We could not find any other logs associated with this Correlation ID.')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($entries->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                {!! $entries->render('pagination::tailwind') !!}
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        function toggleStack(id) {
            const el = document.getElementById(id);
            el.classList.toggle('hidden');
        }
    </script>
@endsection
