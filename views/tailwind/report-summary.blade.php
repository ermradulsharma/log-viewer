<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogViewer Executive Summary</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }

            .print-break {
                page-break-after: always;
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 antialiased p-8">
    <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-2xl overflow-hidden border border-slate-200">
        <!-- Header -->
        <div class="bg-slate-900 px-10 py-12 text-white flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Executive System Report</h1>
                <p class="text-slate-400 mt-2">Generated on {{ now()->format('F j, Y \a\t H:i') }}</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-black text-primary-500 italic">LOG<span class="text-white">VIEWER</span></div>
                <p class="text-[10px] uppercase font-bold tracking-widest text-slate-500 mt-1">Enterprise Edition</p>
            </div>
        </div>

        <div class="p-10 space-y-12">
            <!-- System Health Section -->
            <section>
                <h2 class="text-xl font-bold border-b-2 border-slate-100 pb-2 mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-600 rounded"></span> System Health & Storage
                </h2>
                <div class="grid grid-cols-3 gap-6">
                    <div class="p-5 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Overall Health</p>
                        <p class="text-2xl font-bold text-green-600">{{ round($storage['health']) }}%</p>
                    </div>
                    <div class="p-5 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Storage Used</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $storage['total_size'] }}</p>
                    </div>
                    <div class="p-5 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Current Limit</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $storage['limit'] }}</p>
                    </div>
                </div>
            </section>

            <!-- Distribution Section -->
            <section>
                <h2 class="text-xl font-bold border-b-2 border-slate-100 pb-2 mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-purple-600 rounded"></span> Log Level Distribution
                </h2>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($percents as $level => $item)
                        @if ($item['count'] > 0)
                            <div class="flex items-center justify-between p-3 border border-slate-100 rounded-lg">
                                <span class="text-xs font-bold uppercase text-slate-500">{{ $level }}</span>
                                <span class="text-sm font-bold">{{ $item['count'] }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>

            <!-- Top Issues -->
            <section class="print-break">
                <h2 class="text-xl font-bold border-b-2 border-slate-100 pb-2 mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-red-600 rounded"></span> Top Recurring Critical Patterns
                </h2>
                <div class="space-y-4">
                    @forelse($topErrors as $error)
                        <div class="p-4 border border-slate-200 rounded-xl flex justify-between items-start">
                            <div>
                                <p class="text-sm font-bold text-slate-800 leading-tight mb-2">{{ $error['message'] }}
                                </p>
                                <div class="flex gap-4">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">Last Seen:
                                        {{ $error['last_seen']->format('Y-m-d H:i') }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">Level:
                                        {{ $error['level'] }}</span>
                                </div>
                            </div>
                            <span
                                class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-black">{{ $error['count'] }}
                                hits</span>
                        </div>
                    @empty
                        <p class="text-slate-400 text-sm italic">No recurring critical errors detected.</p>
                    @endforelse
                </div>
            </section>

            <!-- Audit Activity -->
            <section>
                <h2 class="text-xl font-bold border-b-2 border-slate-100 pb-2 mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-amber-500 rounded"></span> Recent Administrative Audit
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                                <th class="px-4 py-2">Time</th>
                                <th class="px-4 py-2">Administrator</th>
                                <th class="px-4 py-2">Action</th>
                                <th class="px-4 py-2">Result</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($auditLogs as $log)
                                <tr>
                                    <td class="px-4 py-3">{{ $log['timestamp'] }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $log['user_id'] }}</td>
                                    <td class="px-4 py-3"><span
                                            class="bg-slate-100 px-2 py-1 rounded text-[10px] uppercase font-bold">{{ $log['action'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">Success</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Footer -->
            <footer
                class="pt-8 border-t border-slate-200 text-center text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">
                System Analytics powered by Skywalker LogViewer v2.0
            </footer>
        </div>
    </div>

    <!-- Print Action -->
    <div class="fixed bottom-8 right-8 no-print flex gap-3">
        <button onclick="window.print()"
            class="bg-slate-900 hover:bg-black text-white px-6 py-3 rounded-full shadow-2xl transition-all flex items-center gap-2 font-bold">
            <i class="fa fa-print"></i> Print Report
        </button>
        <button onclick="window.close()"
            class="bg-white hover:bg-slate-50 text-slate-900 border border-slate-200 px-6 py-3 rounded-full shadow-2xl transition-all font-bold">
            Close
        </button>
    </div>
</body>

</html>
