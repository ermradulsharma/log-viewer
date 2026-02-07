@extends('log-viewer::tailwind._master')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-slate-900">@lang('Live Tail') <span
                class="text-slate-400 text-base font-normal">({{ $date }})</span></h1>
        <div class="flex items-center gap-2">
            <span id="status-indicator"
                class="flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wide">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                @lang('Live')
            </span>
            <button id="pause-btn"
                class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">
                <i class="fa fa-pause"></i> @lang('Pause')
            </button>
        </div>
    </div>

    <div class="bg-slate-900 rounded-xl overflow-hidden shadow-lg border border-slate-700 font-mono text-sm leading-6">
        <div id="terminal" class="p-4 h-[600px] overflow-y-auto w-full whitespace-pre-wrap text-slate-300">
            @lang('Connecting to log stream...')</div>
    </div>
@endsection

@section('scripts')
    <script>
        ready(() => {
            const terminal = document.getElementById('terminal');
            const pauseBtn = document.getElementById('pause-btn');
            const statusIndicator = document.getElementById('status-indicator');
            let isPaused = false;
            let offset = null; // Start from server default (end of file)
            let pollInterval;

            const scrollToBottom = () => {
                terminal.scrollTop = terminal.scrollHeight;
            };

            const fetchLogs = () => {
                if (isPaused) return;

                fetch("{{ route('log-viewer::logs.tail', ['date' => $date]) }}?offset=" + (offset || ''))
                    .then(response => response.json())
                    .then(data => {
                        if (offset === null) {
                            terminal.innerHTML = ""; // Clear "Connecting..." message
                        }

                        if (data.content) {
                            // Append content
                            const span = document.createElement('span');
                            span.innerHTML = data.content;
                            terminal.appendChild(span);

                            // Auto scroll if close to bottom
                            const isAtBottom = terminal.scrollHeight - terminal.scrollTop <= terminal
                                .clientHeight + 100;
                            if (isAtBottom || offset === null) {
                                scrollToBottom();
                            }
                        }

                        offset = data.offset;
                    })
                    .catch(err => {
                        console.error("Live Tail Error:", err);
                    });
            };

            // Initial fetch
            fetchLogs();

            // Poll every 2 seconds
            pollInterval = setInterval(fetchLogs, 2000);

            pauseBtn.addEventListener('click', () => {
                isPaused = !isPaused;
                if (isPaused) {
                    pauseBtn.innerHTML = '<i class="fa fa-play"></i> @lang('Resume')';
                    statusIndicator.classList.replace('bg-green-100', 'bg-slate-100');
                    statusIndicator.classList.replace('text-green-700', 'text-slate-500');
                    statusIndicator.querySelector('.animate-ping').classList.add('hidden');
                    statusIndicator.querySelector('.bg-green-500').classList.replace('bg-green-500',
                        'bg-slate-400');
                } else {
                    pauseBtn.innerHTML = '<i class="fa fa-pause"></i> @lang('Pause')';
                    statusIndicator.classList.replace('bg-slate-100', 'bg-green-100');
                    statusIndicator.classList.replace('text-slate-500', 'text-green-700');
                    statusIndicator.querySelector('.animate-ping').classList.remove('hidden');
                    statusIndicator.querySelector('.bg-slate-400').classList.replace('bg-slate-400',
                        'bg-green-500');
                    // Resume immediately
                    fetchLogs();
                }
            });
        });
    </script>
@endsection
