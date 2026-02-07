@extends('log-viewer::bootstrap-5._master')

<?php /** @var  Illuminate\Pagination\LengthAwarePaginator  $rows */ ?>

@section('content')
    <div class="row justify-content-center py-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">@lang('Log Files')</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                @foreach ($headers as $key => $header)
                                    <th scope="col" class="{{ $key == 'date' ? 'text-start ps-4' : 'text-center' }}">
                                        @if ($key == 'date')
                                            {{ $header }}
                                        @else
                                            <span class="badge badge-level-{{ $key }}">
                                                {{ log_styler()->icon($key) }} {{ $header }}
                                            </span>
                                        @endif
                                    </th>
                                @endforeach
                                <th scope="col" class="text-center">@lang('Actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $date => $row)
                                <tr>
                                    @foreach ($row as $key => $value)
                                        <td class="{{ $key == 'date' ? 'text-start ps-4 font-monospace' : 'text-center' }}">
                                            @if ($key == 'date')
                                                <a href="{{ route('log-viewer::logs.show', [$date]) }}"
                                                    class="text-decoration-none fw-bold text-primary">
                                                    {{ $value }}
                                                </a>
                                            @elseif ($value == 0)
                                                <span class="text-muted opacity-25">-</span>
                                            @else
                                                <a href="{{ route('log-viewer::logs.filter', [$date, $key]) }}">
                                                    <span
                                                        class="badge badge-level-{{ $key }}">{{ $value }}</span>
                                                </a>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('log-viewer::logs.show', [$date]) }}"
                                                class="btn btn-light text-primary" title="@lang('Show')">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('log-viewer::logs.download', [$date]) }}"
                                                class="btn btn-light text-success" title="@lang('Download')">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <a href="#delete-log-modal" class="btn btn-light text-danger"
                                                data-log-date="{{ $date }}" title="@lang('Delete')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa fa-inbox fa-3x mb-3 opacity-25"></i>
                                            <span>@lang('The list of logs is empty!')</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $rows->render() }}
            </div>
        </div>
    </div>
@endsection

@section('modals')
    {{-- DELETE MODAL --}}
    <div class="modal fade" id="delete-log-modal" tabindex="-1" aria-labelledby="delete-log-modal-label"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="date" value="">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="fa fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <p class="mb-0 fs-5"></p>
                        <small class="text-muted">@lang('This action cannot be undone.')</small>
                    </div>
                    <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn-danger px-4"
                            data-loading-text="@lang('Loading')&hellip;">@lang('Delete')</button>
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
            let deleteLogModalElt = deleteLogModal._element
            let deleteLogForm = document.querySelector('form#delete-log-form')
            let submitBtn = new bootstrap.Button(deleteLogForm.querySelector('button[type=submit]'))
            document.querySelectorAll("a[href='#delete-log-modal']").forEach((elt) => {
                elt.addEventListener('click', (event) => {
                    event.preventDefault()
                    let date = event.currentTarget.getAttribute('data-log-date')
                    let message = "{{ __('Are you sure you want to delete ?') }}"
                    deleteLogForm.querySelector('input[name=date]').value = date
                    deleteLogModalElt.querySelector('.modal-body p').innerHTML = message
                    deleteLogModal.show()
                })
            })
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
                            deleteLogModal.hide()
                            location.reload()
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
            deleteLogModalElt.addEventListener('hidden.bs.modal', () => {
                deleteLogForm.querySelector('input[name=date]').value = ''
                deleteLogModalElt.querySelector('.modal-body p').innerHTML = ''
            })
        })
    </script>
@endsection
