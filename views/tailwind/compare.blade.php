@extends('log-viewer::tailwind._master')

@section('content')
    <div class="flex items-center justify-between lg:pb-4 mb-4">
        <h1 class="text-2xl font-bold text-slate-900">@lang('Log Comparison & Contrast')</h1>
        <div class="flex gap-2">
            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-bold border border-purple-200">
                <i class="fa fa-balance-scale mr-1"></i> @lang('Post-Deployment Delta')
            </span>
        </div>
    </div>

    <!-- Date Selectors -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mb-8">
        <form action="{{ route('log-viewer::compare') }}" method="GET" class="flex flex-col md:flex-row gap-6 items-end">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">@lang('Baseline Date') (Reference)</label>
                <select name="date1"
                    class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 text-sm">
                    @foreach ($dates as $date)
                        <option value="{{ $date }}" {{ $date1 == $date ? 'selected' : '' }}>{{ $date }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center justify-center p-2 text-slate-300">
                <i class="fa fa-arrow-right text-xl"></i>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">@lang('Target Date') (Comparison)</label>
                <select name="date2"
                    class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 text-sm">
                    @foreach ($dates as $date)
                        <option value="{{ $date }}" {{ $date2 == $date ? 'selected' : '' }}>{{ $date }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-lg transition-colors shadow-sm whitespace-nowrap">
                <i class="fa fa-sync mr-2"></i> @lang('Run Analysis')
            </button>
        </form>
    </div>

    @if ($stats1 && $stats2)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Summary Delta -->
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm h-fit">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">@lang('Volume Comparison')</h3>
                    <div class="flex gap-2">
                        <span
                            class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase">{{ $date1 }}</span>
                        <span class="text-slate-300">vs</span>
                        <span
                            class="px-2 py-0.5 bg-primary-100 text-primary-600 rounded text-[10px] font-bold uppercase">{{ $date2 }}</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-8">
                        <div class="text-center">
                            <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">@lang('Baseline Total')</div>
                            <div class="text-3xl font-bold text-slate-700">{{ $stats1['total'] }}</div>
                        </div>
                        <div class="flex flex-col items-center">
                            @php $percent = $stats1['total'] > 0 ? (($stats2['total'] - $stats1['total']) / $stats1['total']) * 100 : 0; @endphp
                            <div class="text-lg font-bold {{ $percent > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $percent > 0 ? '+' : '' }}{{ round($percent) }}%
                            </div>
                            <div class="w-24 h-px bg-slate-200 my-1"></div>
                            <div class="text-[10px] text-slate-400 uppercase font-bold">@lang('Volume Delta')</div>
                        </div>
                        <div class="text-center">
                            <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">@lang('Target Total')</div>
                            <div class="text-3xl font-bold text-slate-900">{{ $stats2['total'] }}</div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach (log_viewer()->levels() as $level)
                            @php
                                $c1 = $stats1['levels'][$level] ?? 0;
                                $c2 = $stats2['levels'][$level] ?? 0;
                                $diff = $c2 - $c1;
                            @endphp
                            <div class="flex items-center justify-between group">
                                <span class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                                    <span class="w-2 h-2 rounded-full bg-level-{{ $level }}"></span>
                                    {{ ucfirst($level) }}
                                </span>
                                <div class="flex items-center gap-4">
                                    <span class="text-xs text-slate-400 font-mono">{{ $c1 }} <i
                                            class="fa fa-arrow-right mx-1 opacity-30"></i> {{ $c2 }}</span>
                                    <span
                                        class="text-xs font-bold w-12 text-right {{ $diff > 0 ? 'text-red-500' : ($diff < 0 ? 'text-green-500' : 'text-slate-300') }}">
                                        {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- New Patterns / Errors -->
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm h-fit">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-purple-50/50">
                    <h3 class="text-base font-semibold text-purple-900 flex items-center gap-2">
                        <i class="fa fa-magic text-purple-500"></i> @lang('New Issue Signatures')
                    </h3>
                    <span
                        class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-[10px] uppercase font-bold tracking-wider">{{ count($newPatterns) }}
                        @lang('Identified')</span>
                </div>
                <div class="p-6">
                    @forelse($newPatterns as $hash)
                        <div
                            class="p-4 bg-slate-50 border border-slate-100 rounded-lg mb-3 last:mb-0 hover:border-purple-200 transition-colors group">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                                    <i class="fa fa-plus text-xs"></i>
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <div class="flex items-center justify-between mb-1">
                                        <span
                                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">@lang('Signature Hash')</span>
                                        <span
                                            class="text-[9px] px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded font-bold uppercase transition-opacity">@lang('New Cluster')</span>
                                    </div>
                                    <p class="text-xs font-mono text-slate-700 break-all mb-3">{{ $hash }}</p>
                                    <a href="{{ route('log-viewer::logs.list') }}?query={{ $hash }}"
                                        class="inline-flex items-center text-[10px] text-primary-600 hover:text-primary-700 font-bold uppercase tracking-wider group-hover:translate-x-1 transition-transform">
                                        @lang('Examine this pattern') <i class="fa fa-chevron-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-16 text-center">
                            <div
                                class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-green-100">
                                <i class="fa fa-check text-green-500 text-2xl opacity-50"></i>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900 mb-1">@lang('Perfect Stability')</h4>
                            <p class="text-xs text-slate-400 max-w-[250px] mx-auto">@lang('No new error patterns were identified in the target date that werent already present in the baseline.')</p>
                        </div>
                    @endforelse

                    @if (count($newPatterns) > 0)
                        <div
                            class="mt-6 p-4 bg-amber-50 rounded-lg border border-amber-100 text-amber-800 text-[10px] italic flex gap-3 items-start">
                            <i class="fa fa-info-circle mt-0.5 flex-shrink-0"></i>
                            <p>@lang('Important: New signatures are uniquely identified error clusters that appeared in the target date but were completely absent from the baseline baseline. These often highlight new regressions, edge cases, or environmental shifts following a release.')</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection
