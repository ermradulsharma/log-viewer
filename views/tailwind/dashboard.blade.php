@extends('log-viewer::tailwind._master')

@section('styles')
@endsection

@section('content')
    <div class="flex items-center justify-between lg:pb-4 mb-4">
        <h1 class="text-2xl font-bold text-slate-900">@lang('Enterprise Dashboard')</h1>
        <div class="flex gap-2">
            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold border border-green-200">
                <i class="fa fa-shield-check mr-1"></i> @lang('Retention Active')
            </span>
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold border border-blue-200">
                <i class="fa fa-history mr-1"></i> @lang('Auditing Enabled')
            </span>
        </div>
    </div>

    @if ($anomalies['is_spike'])
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm animate-pulse">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fa fa-exclamation-circle text-red-600 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-red-900">@lang('Critical Status Spike Detected')</h3>
                    <p class="text-xs text-red-700 mt-0.5">{{ $anomalies['message'] }}</p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('log-viewer::logs.list') }}?query=level:error,critical,emergency"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold rounded-lg transition-colors whitespace-nowrap">
                        @lang('Investigate Spike')
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Top Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Distribution Chart -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 h-full">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-base font-semibold text-slate-900">@lang('Log Level Distribution')</h3>
            </div>
            <div class="p-6">
                <canvas id="stats-doughnut-chart" height="250" class="max-h-[250px]"></canvas>
            </div>
        </div>

        <!-- Trend Chart -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900">@lang('Traffic & Error Velocity (7 Days)')</h3>
                <span
                    class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[10px] uppercase font-bold tracking-wider">@lang('Volume Analysis')</span>
            </div>
            <div class="p-6">
                <canvas id="trend-line-chart" height="250" class="max-h-[250px]"></canvas>
            </div>
        </div>

        <!-- Performance Hotspots -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                    <i class="fa fa-bolt text-amber-500"></i> @lang('Performance Hotspots')
                </h3>
            </div>
            <div class="p-6">
                @forelse($hotspots as $hotspot)
                    <div class="mb-4 last:mb-0">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-slate-700 truncate max-w-[70%]"
                                title="{{ $hotspot['label'] }}">{{ $hotspot['label'] }}</span>
                            <span class="text-xs font-mono font-bold text-amber-600">{{ $hotspot['time'] }}ms</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full"
                                style="width: {{ min(($hotspot['time'] / 2000) * 100, 100) }}%"></div>
                        </div>
                        <div class="mt-1 text-[10px] text-slate-400">@lang('Detected on') {{ $hotspot['date'] }}</div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <i class="fa fa-check-circle text-green-500 text-3xl opacity-20 mb-2"></i>
                        <p class="text-slate-400 text-sm">@lang('No performance issues detected.')</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Insights Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Recurring Errors -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                    <i class="fa fa-layer-group text-primary-500"></i> @lang('Top Recurring Errors')
                </h3>
                <span
                    class="px-2 py-0.5 bg-primary-50 text-primary-600 rounded text-[10px] uppercase font-bold tracking-wider">@lang('Smart Summary')</span>
            </div>
            <div class="p-6">
                @forelse($topErrors as $error)
                    <div class="mb-4 last:mb-0 pb-4 border-b border-slate-50 last:border-0">
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-sm font-medium text-slate-700 leading-tight">
                                {{ $error['message'] }}
                            </span>
                            <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 rounded text-[10px] font-bold">
                                {{ $error['count'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-[10px] text-slate-400">
                            <span><i class="fa fa-clock mr-1"></i> {{ $error['last_seen']->diffForHumans() }}</span>
                            <span><i class="fa fa-tag mr-1"></i> {{ $error['level'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <i class="fa fa-smile text-green-500 text-3xl opacity-20 mb-2"></i>
                        <p class="text-slate-400 text-sm">@lang('No critical patterns detected.')</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Health & Resource Monitor -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                    <i class="fa fa-heartbeat text-red-500"></i> @lang('Health & Resource Monitor')
                </h3>
                @if ($storage['warning'])
                    <span class="flex h-2 w-2 rounded-full bg-red-600 animate-ping"></span>
                @endif
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="p-4 bg-slate-50 rounded-lg border border-slate-100 relative overflow-hidden">
                        <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">@lang('Storage Usage')</div>
                        <div class="text-xl font-bold text-slate-900">{{ $storage['total_size'] }}</div>
                        <div class="text-[10px] text-slate-500">@lang('of') {{ $storage['limit'] }}
                            @lang('limit')</div>
                        <div class="absolute bottom-0 left-0 h-1 bg-primary-500 opacity-20"
                            style="width: {{ $storage['usage_percent'] }}%"></div>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg border border-slate-100">
                        <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">@lang('Cleanup Score')</div>
                        <div class="text-xl font-bold text-green-600">{{ round($storage['health']) }}%</div>
                        <div class="text-[10px] text-slate-500">@lang('Efficiency Rating')</div>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-semibold text-slate-600">@lang('Overall System Health')</span>
                        <span
                            class="px-2 py-0.5 {{ $storage['health'] > 80 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded-full text-[10px] font-bold">
                            {{ $storage['health'] > 80 ? 'OPTIMAL' : 'CRITICAL STORAGE' }}
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="{{ $storage['health'] > 80 ? 'bg-green-500' : 'bg-red-600' }} h-2.5 rounded-full transition-all duration-1000"
                            style="width: {{ $storage['health'] }}%"></div>
                    </div>
                </div>

                @if ($userRole === 'admin')
                    <button type="button" onclick="runManualCleanup()" id="manual-cleanup-btn"
                        class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-2 shadow-sm">
                        <i class="fa fa-broom"></i> @lang('Run Manual Cleanup Cycle')
                    </button>
                @endif

                <div class="flex items-center gap-2 text-xs text-slate-500 mt-4">
                    <i class="fa fa-info-circle text-primary-400"></i>
                    <span>@lang('Automated retention: keeping logs for') <strong>{{ $notificationSettings['retention_days'] ?? 30 }}
                            @lang('days')</strong>.</span>
                </div>
            </div>
        </div>

        <!-- Management Reporting -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                    <i class="fa fa-file-invoice text-blue-500"></i> @lang('Management & Reporting')
                </h3>
            </div>
            <div class="p-6">
                <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                    @lang('Generate comprehensive executive summaries including system health, error trends, and administrative audit trails.')
                </p>

                <div class="space-y-3">
                    <a href="{{ route('log-viewer::reports.download') }}?type=summary" target="_blank"
                        class="w-full py-2.5 bg-white hover:bg-slate-50 text-slate-900 text-xs font-bold rounded-lg border border-slate-200 transition-all flex items-center justify-center gap-2 shadow-sm">
                        <i class="fa fa-download text-slate-400"></i> @lang('Generate Executive Summary (HTML)')
                    </a>

                    <button type="button" onclick="sendEmailReport()" id="email-report-btn"
                        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-2 shadow-sm">
                        <i class="fa fa-paper-plane"></i> @lang('Email Report to Team')
                    </button>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-4">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">@lang('Automated Delivery')</span>
                        <span
                            class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-[10px] font-black uppercase">@lang('Enterprise Only')</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-6 bg-slate-200 rounded-full relative opacity-50 cursor-not-allowed">
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full"></div>
                        </div>
                        <span class="text-xs text-slate-400 italic">@lang('Scheduled weekly reporting is currently disabled.')</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Insights Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        @if ($userRole === 'admin' || $userRole === 'auditor')
            <!-- Saved Search Snapshots -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                        <i class="fa fa-bookmark text-amber-500"></i> @lang('Quick Access Snapshots')
                    </h3>
                    <span
                        class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[10px] uppercase font-bold tracking-wider">@lang('Custom Filters')</span>
                </div>
                <div class="p-6">
                    @forelse($savedSearches as $search)
                        <div class="mb-3 last:mb-0">
                            <a href="{{ route('log-viewer::logs.list') }}?query={{ urlencode($search['query']) }}"
                                class="flex items-center justify-between p-3 bg-slate-50 hover:bg-primary-50 border border-slate-100 hover:border-primary-200 rounded-lg transition-all group">
                                <div class="truncate mr-4 flex-1">
                                    <div
                                        class="text-sm font-bold text-slate-700 group-hover:text-primary-700 transition-colors">
                                        {{ $search['label'] }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono truncate">{{ $search['query'] }}
                                    </div>
                                </div>
                                <div class="flex-shrink-0 text-slate-300 group-hover:text-primary-400">
                                    <i class="fa fa-chevron-right text-xs"></i>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="py-8 text-center bg-slate-50 rounded-lg border border-dashed border-slate-200">
                            <i class="fa fa-search text-slate-300 text-2xl mb-2"></i>
                            <p class="text-slate-400 text-xs">@lang('No snapshots yet. Save a search from the log view!')</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        @if ($userRole === 'admin')
            <!-- Notification Hub -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                        <i class="fa fa-bell text-primary-500"></i> @lang('Notification Hub')
                    </h3>
                    <span
                        class="px-2 py-0.5 bg-primary-50 text-primary-600 rounded text-[10px] uppercase font-bold tracking-wider">@lang('Real-time Alerts')</span>
                </div>
                <div class="p-6">
                    <form id="notification-settings-form" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase mb-1">@lang('Slack Webhook')</label>
                                <input type="url" name="slack_webhook"
                                    value="{{ $notificationSettings['slack_webhook'] }}"
                                    class="w-full px-3 py-2 text-xs rounded-lg border-slate-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                    placeholder="https://hooks.slack.com/services/...">
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase mb-1">@lang('Discord Webhook')</label>
                                <input type="url" name="discord_webhook"
                                    value="{{ $notificationSettings['discord_webhook'] }}"
                                    class="w-full px-3 py-2 text-xs rounded-lg border-slate-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                    placeholder="https://discord.com/api/webhooks/...">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase mb-1">@lang('Email Alerts')</label>
                                <input type="email" name="email_alerts"
                                    value="{{ $notificationSettings['email_alerts'] }}"
                                    class="w-full px-3 py-2 text-xs rounded-lg border-slate-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                    placeholder="admin@example.com">
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase mb-1">@lang('Alert Threshold')</label>
                                <select name="alert_level"
                                    class="w-full px-3 py-2 text-xs rounded-lg border-slate-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                    <option value="error"
                                        {{ $notificationSettings['alert_level'] == 'error' ? 'selected' : '' }}>
                                        @lang('Error & Above')</option>
                                    <option value="critical"
                                        {{ $notificationSettings['alert_level'] == 'critical' ? 'selected' : '' }}>
                                        @lang('Critical & Above')</option>
                                    <option value="emergency"
                                        {{ $notificationSettings['alert_level'] == 'emergency' ? 'selected' : '' }}>
                                        @lang('Emergency Only')</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase mb-1">@lang('Log Retention Period')</label>
                                <select name="retention_days"
                                    class="w-full px-3 py-2 text-xs rounded-lg border-slate-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                    <option value="7"
                                        {{ ($notificationSettings['retention_days'] ?? 30) == 7 ? 'selected' : '' }}>
                                        @lang('7 Days (Aggressive)')</option>
                                    <option value="30"
                                        {{ ($notificationSettings['retention_days'] ?? 30) == 30 ? 'selected' : '' }}>
                                        @lang('30 Days (Standard)')</option>
                                    <option value="90"
                                        {{ ($notificationSettings['retention_days'] ?? 30) == 90 ? 'selected' : '' }}>
                                        @lang('90 Days (Enterprise)')</option>
                                    <option value="0"
                                        {{ ($notificationSettings['retention_days'] ?? 30) == 0 ? 'selected' : '' }}>
                                        @lang('Keep Forever')</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <div
                                    class="p-3 bg-blue-50 rounded-lg border border-blue-100 flex items-center gap-3 w-full">
                                    <i class="fa fa-info-circle text-blue-500"></i>
                                    <p class="text-[10px] text-blue-700 leading-tight">@lang('Logs older than the selected period will be automatically flagged for cleanup during the next cycle.')</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">
                                @lang('External Issue Trackers')</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-500 uppercase mb-1 flex items-center gap-1">
                                        <i class="fab fa-jira text-blue-600"></i> @lang('Jira API Key / URL')
                                    </label>
                                    <input type="text" name="jira_config"
                                        value="{{ $notificationSettings['jira_config'] ?? '' }}"
                                        class="w-full px-3 py-2 text-xs rounded-lg border-slate-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        placeholder="https://your-domain.atlassian.net">
                                </div>
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-500 uppercase mb-1 flex items-center gap-1">
                                        <i class="fab fa-github text-slate-900"></i> @lang('GitHub Token / Repo')
                                    </label>
                                    <input type="text" name="github_config"
                                        value="{{ $notificationSettings['github_config'] ?? '' }}"
                                        class="w-full px-3 py-2 text-xs rounded-lg border-slate-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        placeholder="owner/repo">
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit" id="save-notifications-btn"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-2">
                                <i class="fa fa-save"></i> @lang('Update settings')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- Auditor/Viewer can see Audit Trail (Phase 2) -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                        <i class="fa fa-history text-blue-500"></i> @lang('System Activity Audit Trail')
                    </h3>
                    <span
                        class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] uppercase font-bold tracking-wider">@lang('Live Audit')</span>
                </div>
                <div class="overflow-x-auto max-h-[340px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase">@lang('Time')
                                </th>
                                <th class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase">@lang('User')
                                </th>
                                <th class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase">@lang('Action')
                                </th>
                                <th class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase text-right">
                                    @lang('IP')</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($auditLogs as $audit)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 text-[10px] text-slate-500 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($audit['timestamp'])->format('H:i:s m/d') }}
                                    </td>
                                    <td class="px-4 py-3 text-xs font-medium text-slate-700">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] uppercase font-bold text-slate-500">
                                                {{ substr($audit['user_id'] ?? 'G', 0, 1) }}
                                            </div>
                                            {{ Str::limit($audit['user_id'] ?? __('Guest'), 12) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <span
                                            class="px-2 py-0.5 rounded text-[10px] font-bold {{ Str::contains($audit['action'], 'delete') ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ strtoupper(str_replace('_', ' ', $audit['action'])) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-[10px] text-slate-400 font-mono text-right">
                                        {{ $audit['ip'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-12 text-center text-slate-400">
                                        <i class="fa fa-clipboard-list text-2xl mb-2 opacity-20"></i>
                                        <p class="text-[10px]">@lang('No system activity recorded yet.')</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-right">
                    <p class="text-[10px] text-slate-400"><i class="fa fa-info-circle mr-1"></i> @lang('Showing last 20 verified administrative actions.')</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($percents as $level => $item)
            <div
                class="relative overflow-hidden bg-white rounded-xl border border-slate-200 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-1 group">
                <div class="absolute top-0 bottom-0 left-0 w-1 bg-level-{{ $level }}"></div>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-4">
                        <span
                            class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $item['name'] }}</span>
                        <span
                            class="text-lg text-level-{{ $level }} opacity-50 group-hover:opacity-100 transition-opacity">
                            {!! log_styler()->icon($level) !!}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-slate-900">{{ $item['count'] }}</span>
                        @if ($item['count'] > 0)
                            <span class="text-sm font-medium text-slate-500">{{ $item['percent'] }}%</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('scripts')
    <script>
        ready(() => {
            // Trend Line Chart
            new Chart(document.getElementById("trend-line-chart"), {
                type: 'line',
                data: {!! $trendData !!},
                options: {
                    onClick: (e, elements) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const label = e.chart.data.labels[index];
                            window.location.href = "{{ route('log-viewer::logs.list') }}?query=date:" +
                                label;
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 10,
                                    weight: 'bold'
                                }
                            }
                        }
                    }
                }
            });

            // Level Distribution Bar Chart
            new Chart(document.getElementById("stats-doughnut-chart"), {
                type: 'bar',
                data: {!! $chartData !!},
                options: {
                    onClick: (e, elements) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const label = e.chart.data.labels[index].toLowerCase();
                            window.location.href =
                                "{{ route('log-viewer::logs.list') }}?query=level:" + label;
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Notification Settings Submission
            const notificationForm = document.getElementById('notification-settings-form');
            notificationForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('save-notifications-btn');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Updating...';

                try {
                    const response = await fetch('{{ route('log-viewer::notifications.save') }}', {
                        method: 'POST',
                        body: new FormData(notificationForm),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        alert('Notification settings updated successfully!');
                        location.reload();
                    } else {
                        alert('Failed to update settings. Please check the logs.');
                    }
                } catch (error) {
                    console.error('Error saving notifications:', error);
                    alert('Network error while saving notification settings.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        });

        async function runManualCleanup() {
            if (!confirm(
                    'Are you sure you want to run a manual cleanup cycle? This will identify files for removal based on current retention policies.'
                )) return;

            const btn = document.getElementById('manual-cleanup-btn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Cleaning...';

            try {
                const response = await fetch('{{ route('log-viewer::cleanup-logs') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Log cleanup failed. Check system permissions.');
                }
            } catch (error) {
                console.error('Cleanup error:', error);
                alert('Connection error during cleanup process.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        async function sendEmailReport() {
            const btn = document.getElementById('email-report-btn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Sending...';

            try {
                const response = await fetch('{{ route('log-viewer::reports.email') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                } else {
                    alert(data.message || 'Failed to send report. Please check configuration.');
                }
            } catch (error) {
                console.error('Email report error:', error);
                alert('Connection error while attempting to send email report.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    </script>
@endsection
