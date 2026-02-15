<?php

/**
 * @var  Skywalker\LogViewer\Entities\Log            $log
 * @var  Illuminate\Pagination\LengthAwarePaginator  $entries
 * @var  string|null                                 $query
 */
?>

@extends('log-viewer::bootstrap-5._master')

@section('content')
    <div class="row justify-content-center py-3">
        <div class="row pb-2">
            <div class="col-8">
                <div class="card border-0 shadow-sm m-0">
                    <div class="card-body p-4">
                        <div class="row g-4 text-center">
                            <div class="col-sm-4 border-end">
                                <h2 class="fw-bold text-dark mb-1">{{ $entries->total() }}</h2>
                                <span class="text-muted text-uppercase small fw-bold tracking-wider">@lang('Entries')</span>
                            </div>
                            <div class="col-sm-4 border-end">
                                <h2 class="fw-bold text-dark mb-1">{{ $log->size() }}</h2>
                                <span
                                    class="text-muted text-uppercase small fw-bold tracking-wider">@lang('Size')</span>
                            </div>
                            <div class="col-sm-4">
                                <h6 class="fw-bold text-dark mb-1">{{ $log->updatedAt() }}</h6>
                                <span
                                    class="text-muted text-uppercase small fw-bold tracking-wider">@lang('Updated At')</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm flex-row align-items-center justify-content-center py-4 m-0">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">@lang('Log'): <span class="text-primary">{{ $log->date }}</span>
                        </h5>
                    </div>
                    <div class="card-body text-end">
                        <div class="btn-group shadow-sm rounded-3">
                            <a href="{{ route('log-viewer::logs.download', [$log->date]) }}"
                                class="btn btn-success text-white fw-medium">
                                <i class="fa fa-download me-1"></i> @lang('Download')
                            </a>
                            <button type="button" class="btn btn-danger text-white fw-medium" data-bs-toggle="modal"
                                data-bs-target="#delete-log-modal">
                                <i class="fa fa-trash me-1"></i> @lang('Delete')
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row pt-2">
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm sticky-top m-0" style="top: 2rem; z-index: 100;">
                    <div class="card-header bg-white border-bottom border-light py-3">
                        <h6 class="m-0 fw-bold text-dark"><i class="fa fa-layer-group me-2 text-primary opacity-50"></i>
                            @lang('Levels')</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($log->menu() as $levelKey => $item)
                            <a href="{{ $item['count'] === 0 ? '#' : $item['url'] }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $level === $levelKey ? 'bg-primary-subtle text-primary fw-bold border-start border-0 border-primary' : '' }} {{ $item['count'] === 0 ? 'text-muted opacity-50' : '' }}">
                                <span>{!! $item['icon'] !!} {{ $item['name'] }}</span>
                                @if ($item['count'] > 0)
                                    <span
                                        class="badge rounded-pill badge-level-{{ $levelKey }}">{{ $item['count'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm mb-4">
                    @if ($entries->hasPages())
                        <div
                            class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small">
                                {{ __('Page :current of :last', ['current' => $entries->currentPage(), 'last' => $entries->lastPage()]) }}
                            </span>
                            {{ $entries->links('pagination::bootstrap-5') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="entries">
                            <thead class="bg-light">
                                <tr>
                                    <th>@lang('Level')</th>
                                    <th>@lang('Time')</th>
                                    <th>@lang('Env')</th>
                                    <th>@lang('Header')</th>
                                    <th>@lang('Actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $key => $entry)
                                    <tr>
                                        <td>
                                            <span class="badge badge-level-{{ $entry->level }}">
                                                {!! $entry->level() !!}
                                            </span>
                                        </td>
                                        <td class="text-nowrap text-muted small font-monospace">
                                            {{ $entry->datetime->format('H:i:s') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $entry->env }}</span>
                                        </td>
                                        <td class="w-50 text-truncate" style="max-width: 0;">
                                            {{ $entry->header }}
                                        </td>
                                        <td>
                                            @if ($entry->hasStack() || $entry->hasContext())
                                                <button class="btn btn-sm btn-light text-primary fw-bold" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#log-stack-{{ $key }}" aria-expanded="false">
                                                    <i class="fa fa-search-plus me-1"></i> @lang('Details')
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @if ($entry->hasStack() || $entry->hasContext())
                                        <tr>
                                            <td colspan="5" class="p-0 border-0">
                                                <div class="collapse bg-light" id="log-stack-{{ $key }}">
                                                    <div class="p-4">
                                                        @if ($entry->hasStack())
                                                            <div class="mb-3">
                                                                <h6 class="text-uppercase text-xs fw-bold text-muted mb-2">
                                                                    Stack
                                                                    Trace</h6>
                                                                <div class="stack-content rounded p-3 bg-white border shadow-sm"
                                                                    style="max-height: 400px; overflow-y: auto;">
                                                                    {!! $entry->stack() !!}
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if ($entry->hasContext())
                                                            <div>
                                                                <h6 class="text-uppercase text-xs fw-bold text-muted mb-2">
                                                                    Context</h6>
                                                                <pre class="rounded p-3 bg-dark text-light mb-0" style="max-height: 200px;">{{ $entry->context() }}</pre>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center opacity-50">
                                                <i class="fa fa-inbox fa-3x mb-3 text-muted"></i>
                                                <h5 class="text-muted">@lang('No log entries found')</h5>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($entries->hasPages())
                        <div class="card-footer bg-white py-3 d-flex justify-content-center">
                            {{ $entries->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <div class="modal fade" id="delete-log-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="date" value="{{ $log->date }}">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-body text-center p-5">
                        <div class="mb-4">
                            <i class="fa fa-exclamation-circle fa-4x text-danger opacity-25"></i>
                        </div>
                        <h4 class="mb-3">@lang('Are you sure?')</h4>
                        <p class="text-muted mb-4">@lang('Do you really want to delete this log file (:date)? This process cannot be undone.', ['date' => $log->date])</p>

                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-light px-4 py-2"
                                data-bs-dismiss="modal">@lang('Cancel')</button>
                            <button type="submit" class="btn btn-danger px-4 py-2"
                                data-loading-text="@lang('Deleting logs...')">@lang('Yes, delete it')</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        ready(() => {
            let deleteLogModal = new bootstrap.Modal('div#delete-log-modal')
            let deleteLogForm = document.querySelector('form#delete-log-form')
            let submitBtn = new bootstrap.Button(deleteLogForm.querySelector('button[type=submit]'))

            deleteLogForm.addEventListener('submit', (event) => {
                event.preventDefault()
                submitBtn.toggle('loading')

                fetch(event.currentTarget.getAttribute('action'), {
                        method: 'DELETE',
                        headers: {
                            "X-Requested-With": "XMLHttpRequest",
                            'Content-type': 'application/json'
                        },
                        body: JSON.stringify({
                            date: event.currentTarget.querySelector("input[name='date']").value,
                        })
                    })
                    .then((resp) => resp.json())
                    .then((resp) => {
                        if (resp.result === 'success') {
                            deleteLogModal.hide();
                            location.replace("{{ route('log-viewer::logs.list') }}");
                        } else {
                            alert('AJAX ERROR ! Check the console !')
                            console.error(resp)
                        }
                    })
                    .catch((err) => {
                        alert('AJAX ERROR ! Check the console !')
                        console.error(err)
                    })

                return false
            })

            // Highlight stack trace
            @unless (empty(log_styler()->toHighlight()))
                @php
                    $htmlHighlight = join('|', log_styler()->toHighlight());
                @endphp

                document.querySelectorAll('.stack-content').forEach((elt) => {
                    elt.innerHTML = elt.innerHTML.trim()
                        .replace(/({!! $htmlHighlight !!})/gm,
                            '<strong class="text-danger bg-warning-subtle px-1 rounded">$1</strong>')
                })
            @endunless
        });
    </script>
@endsection
